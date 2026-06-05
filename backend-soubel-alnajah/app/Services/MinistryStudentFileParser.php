<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use Illuminate\Validation\ValidationException;

/**
 * يحلل ملفات "Eleve.xls" الصادرة عن منظومة وزارة التربية الوطنية.
 *
 * هذه الملفات ليست Excel حقيقياً بل مستندات HTML (UTF-8) تحتوي:
 *  - ديباجة قبل وسم <html> فيها اسم المؤسسة (مثل: ثانوية سبل النجاح (الوادي)).
 *  - جدول <table> واحد رأسه <th> وصفوفه <td> بوسوم <tr> غير مغلقة.
 */
class MinistryStudentFileParser
{
    /** خريطة الرؤوس العربية إلى المفاتيح القياسية. */
    public const HEADER_MAP = [
        'رقم التعريف' => 'national_id',
        'اللقب' => 'last_name_ar',
        'الاسم' => 'first_name_ar',
        'الجنس' => 'gender',
        'تاريخ الازدياد' => 'birth_date',
        'مولود بحكم' => 'born_by_judgment',
        'عقد الميلاد' => 'birth_certificate_type',
        'سنة التسجيل في سجل الولادات' => 'birth_record_year',
        'رقم عقد الميلاد' => 'birth_certificate_number',
        'مكان الازدياد' => 'birth_place',
        'السنة' => 'grade',
        'الشعبة' => 'stream',
        'القسم' => 'section',
        'نظام التمدرس' => 'schooling_system',
        'رقم القيد' => 'registration_number',
        'تاريخ التسجيل' => 'enrolled_at',
    ];

    /** الرؤوس الإلزامية (الشعبة اختيارية: موجودة في الثانوي فقط). */
    public const REQUIRED_HEADERS = [
        'national_id',
        'last_name_ar',
        'first_name_ar',
        'gender',
        'birth_date',
        'birth_place',
        'grade',
        'section',
        'schooling_system',
        'registration_number',
        'enrolled_at',
    ];

    /**
     * فحص سريع: هل الملف مستند HTML من منظومة الوزارة؟
     */
    public function isMinistryHtmlFile(string $absolutePath): bool
    {
        $head = (string) @file_get_contents($absolutePath, false, null, 0, 4096);

        return stripos($head, '<table') !== false || stripos($head, '<html') !== false;
    }

    /**
     * @return array{school_name: ?string, headers: array<int, string>, rows: array<int, array<string, string>>, has_streams: bool}
     */
    public function parse(string $absolutePath): array
    {
        $raw = $this->readUtf8($absolutePath);

        if (trim($raw) === '') {
            throw ValidationException::withMessages([
                'file' => 'ملف فارغ أو غير قابل للقراءة.',
            ]);
        }

        $schoolName = $this->extractSchoolName($raw);
        $dom = $this->loadDom($raw);
        [$headers, $rows] = $this->extractTable($dom);

        $missing = array_diff(self::REQUIRED_HEADERS, $headers);
        if (!empty($missing)) {
            $missingArabic = array_keys(array_intersect(self::HEADER_MAP, $missing));
            throw ValidationException::withMessages([
                'file' => 'الملف لا يطابق صيغة ملفات الوزارة — أعمدة مفقودة: ' . implode('، ', $missingArabic),
            ]);
        }

        if (!$schoolName) {
            throw ValidationException::withMessages([
                'file' => 'تعذر استخراج اسم المؤسسة من رأس الملف.',
            ]);
        }

        return [
            'school_name' => $schoolName,
            'headers' => $headers,
            'rows' => $rows,
            'has_streams' => in_array('stream', $headers, true),
        ];
    }

    private function readUtf8(string $absolutePath): string
    {
        $content = (string) @file_get_contents($absolutePath);

        // إزالة BOM إن وُجد
        return (string) preg_replace('/^\xEF\xBB\xBF/', '', $content);
    }

    /**
     * اسم المؤسسة هو آخر سطر نصي في الديباجة قبل وسم <html>.
     */
    private function extractSchoolName(string $raw): ?string
    {
        $htmlPos = stripos($raw, '<html');
        $preamble = $htmlPos !== false ? substr($raw, 0, $htmlPos) : '';

        if (trim($preamble) === '') {
            return null;
        }

        $text = str_ireplace(['<br/>', '<br />', '<br>'], "\n", $preamble);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $lines = array_values(array_filter(array_map(
            fn ($line) => $this->normalizeText($line),
            preg_split('/\R/u', $text) ?: []
        )));

        $name = end($lines);

        return $name !== false && $name !== '' ? $name : null;
    }

    private function loadDom(string $html): DOMDocument
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        // بادئة <?xml لضمان قراءة UTF-8 بشكل صحيح، وDOMDocument يتسامح مع وسوم <tr> غير المغلقة.
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        return $dom;
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<string, string>>}
     */
    private function extractTable(DOMDocument $dom): array
    {
        $table = $dom->getElementsByTagName('table')->item(0);

        if (!$table instanceof DOMElement) {
            throw ValidationException::withMessages([
                'file' => 'الملف لا يحتوي على جدول بيانات الطلبة.',
            ]);
        }

        $headers = [];
        $rows = [];

        foreach ($table->getElementsByTagName('tr') as $tr) {
            $thCells = $tr->getElementsByTagName('th');

            if ($thCells->length > 0 && empty($headers)) {
                foreach ($thCells as $th) {
                    $arabic = $this->normalizeText($th->textContent);
                    $headers[] = self::HEADER_MAP[$arabic] ?? $arabic;
                }
                continue;
            }

            $tdCells = $tr->getElementsByTagName('td');
            if ($tdCells->length === 0) {
                continue;
            }

            $cells = [];
            foreach ($tdCells as $td) {
                $cells[] = $this->normalizeText($td->textContent);
            }

            if (trim(implode('', $cells)) === '') {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $key) {
                $row[$key] = $cells[$index] ?? '';
            }
            $rows[] = $row;
        }

        if (empty($headers)) {
            throw ValidationException::withMessages([
                'file' => 'تعذر العثور على صف الرؤوس في جدول الملف.',
            ]);
        }

        return [$headers, $rows];
    }

    /**
     * تطبيع نص الخلية: فك الكيانات، إزالة المسافات الزائدة (بما فيها NBSP) ودمج المسافات الداخلية.
     */
    private function normalizeText(string $value): string
    {
        $text = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = (string) preg_replace('/[\x{00A0}\s]+/u', ' ', $text);

        return trim($text);
    }
}
