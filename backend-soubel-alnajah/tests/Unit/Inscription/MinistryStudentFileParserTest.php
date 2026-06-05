<?php

namespace Tests\Unit\Inscription;

use App\Services\MinistryStudentFileParser;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MinistryStudentFileParserTest extends TestCase
{
    private MinistryStudentFileParser $parser;

    /** @var array<int, string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new MinistryStudentFileParser();
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    public function test_it_detects_ministry_html_file(): void
    {
        $path = $this->tempFile('<br/>مدرسة<html dir="RTL"><table><tr><th>x</th></tr></table></html>');

        $this->assertTrue($this->parser->isMinistryHtmlFile($path));
    }

    public function test_it_rejects_non_html_content(): void
    {
        $path = $this->tempFile("col1,col2\n1,2\n");

        $this->assertFalse($this->parser->isMinistryHtmlFile($path));
    }

    public function test_it_parses_high_school_file_with_stream_column(): void
    {
        $path = $this->tempFile($this->ministryHtml(
            'ثانوية سبل النجاح (الوادي)',
            true,
            [['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30']]
        ));

        $result = $this->parser->parse($path);

        $this->assertSame('ثانوية سبل النجاح (الوادي)', $result['school_name']);
        $this->assertTrue($result['has_streams']);
        $this->assertCount(1, $result['rows']);

        $row = $result['rows'][0];
        $this->assertSame('1000000000000001', $row['national_id']);
        $this->assertSame('درويش', $row['last_name_ar']);
        $this->assertSame('رائد', $row['first_name_ar']);
        $this->assertSame('جدع مشترك آداب', $row['stream']);
        $this->assertSame('1', $row['section']);
        $this->assertSame('2024-09-30', $row['enrolled_at']);
    }

    public function test_it_parses_middle_school_file_without_stream_column(): void
    {
        $path = $this->tempFile($this->ministryHtml(
            'متوسطة سبل النجاح (الوادي)',
            false,
            [['2000000000000001', 'غدير', 'أويس', 'ذكر', '2014-06-09', 'لا', 'عادي', '2014', '04272', 'الوادي', 'أولى', '1', 'خارجي', '17', '2025-09-15']]
        ));

        $result = $this->parser->parse($path);

        $this->assertSame('متوسطة سبل النجاح (الوادي)', $result['school_name']);
        $this->assertFalse($result['has_streams']);
        $this->assertArrayNotHasKey('stream', $result['rows'][0]);
        $this->assertSame('1', $result['rows'][0]['section']);
    }

    public function test_it_normalizes_whitespace_and_nbsp_in_cells(): void
    {
        // قيم بمسافات زائدة وNBSP كما تأتي في الملفات الحقيقية ("أولى " و"الوادي ")
        $path = $this->tempFile($this->ministryHtml(
            'ثانوية التجربة',
            true,
            [['1000000000000001', '  درويش  ', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', "الوادي\u{00A0}", "أولى \u{00A0}", 'جدع  مشترك   آداب', ' 1 ', 'خارجي', '443', '2024-09-30']]
        ));

        $row = $this->parser->parse($path)['rows'][0];

        $this->assertSame('درويش', $row['last_name_ar']);
        $this->assertSame('الوادي', $row['birth_place']);
        $this->assertSame('أولى', $row['grade']);
        $this->assertSame('جدع مشترك آداب', $row['stream']);
        $this->assertSame('1', $row['section']);
    }

    public function test_it_handles_unclosed_tr_tags(): void
    {
        // وسوم <tr> غير مغلقة عمداً كما في ملفات المنظومة
        $html = '<br/>ثانوية التجربة<html dir="RTL"><body><table border="1">'
            . '<tr>' . $this->headerCells(true) . '</tr>'
            . '<tr><td>1000000000000001</td><td>أ</td><td>ب</td><td>ذكر</td><td>2009-01-01</td><td>لا</td><td>عادي</td><td>2009</td><td>1</td><td>الوادي</td><td>أولى</td><td>آداب</td><td>1</td><td>خارجي</td><td>1</td><td>2024-09-30</td>'
            . '<tr><td>1000000000000002</td><td>ج</td><td>د</td><td>أنثى</td><td>2010-01-01</td><td>لا</td><td>عادي</td><td>2010</td><td>2</td><td>الوادي</td><td>أولى</td><td>آداب</td><td>2</td><td>خارجي</td><td>2</td><td>2024-09-30</td>'
            . '</table></body></html>';

        $result = $this->parser->parse($this->tempFile($html));

        $this->assertCount(2, $result['rows']);
        $this->assertSame('1000000000000002', $result['rows'][1]['national_id']);
        $this->assertSame('2', $result['rows'][1]['section']);
    }

    public function test_it_strips_utf8_bom(): void
    {
        $html = "\xEF\xBB\xBF" . $this->ministryHtml('متوسطة التجربة', false, [
            ['2000000000000001', 'أ', 'ب', 'ذكر', '2014-01-01', 'لا', 'عادي', '2014', '1', 'الوادي', 'أولى', '1', 'خارجي', '1', '2025-09-15'],
        ]);

        $result = $this->parser->parse($this->tempFile($html));

        $this->assertSame('متوسطة التجربة', $result['school_name']);
        $this->assertCount(1, $result['rows']);
    }

    public function test_it_skips_empty_rows(): void
    {
        $html = '<br/>ثانوية التجربة<html dir="RTL"><body><table>'
            . '<tr>' . $this->headerCells(false) . '</tr>'
            . '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>'
            . '<tr><td>2000000000000001</td><td>أ</td><td>ب</td><td>ذكر</td><td>2014-01-01</td><td>لا</td><td>عادي</td><td>2014</td><td>1</td><td>الوادي</td><td>أولى</td><td>1</td><td>خارجي</td><td>1</td><td>2025-09-15</td>'
            . '</table></body></html>';

        $result = $this->parser->parse($this->tempFile($html));

        $this->assertCount(1, $result['rows']);
    }

    public function test_it_fails_when_required_headers_are_missing(): void
    {
        $html = '<br/>ثانوية التجربة<html><body><table>'
            . '<tr><th>رقم التعريف</th><th>اللقب</th><th>الاسم</th></tr>'
            . '<tr><td>1</td><td>أ</td><td>ب</td>'
            . '</table></body></html>';

        $this->expectException(ValidationException::class);

        $this->parser->parse($this->tempFile($html));
    }

    public function test_it_fails_when_no_table_exists(): void
    {
        $this->expectException(ValidationException::class);

        $this->parser->parse($this->tempFile('<br/>مدرسة<html><body><p>لا جدول هنا</p></body></html>'));
    }

    public function test_it_fails_when_school_name_is_missing(): void
    {
        // لا ديباجة قبل <html> → لا اسم مؤسسة
        $html = '<html><body><table><tr>' . $this->headerCells(false) . '</tr>'
            . '<tr><td>2000000000000001</td><td>أ</td><td>ب</td><td>ذكر</td><td>2014-01-01</td><td>لا</td><td>عادي</td><td>2014</td><td>1</td><td>الوادي</td><td>أولى</td><td>1</td><td>خارجي</td><td>1</td><td>2025-09-15</td>'
            . '</table></body></html>';

        $this->expectException(ValidationException::class);

        $this->parser->parse($this->tempFile($html));
    }

    public function test_it_fails_on_empty_file(): void
    {
        $this->expectException(ValidationException::class);

        $this->parser->parse($this->tempFile(''));
    }

    public function test_it_extracts_school_name_as_last_preamble_line(): void
    {
        // الديباجة الحقيقية تحتوي سطور الجمهورية والوزارة قبل اسم المؤسسة
        $html = '<br> <p style="text-align:center"><strong><span>  الجمهورية الجزائرية الديمقراطية الشعبية </span></strong></p>'
            . '<p style="text-align:center"><strong><span> وزارة التربية الوطنية </span></strong></p><br/>'
            . 'متوسطة سبل النجاح (الوادي)'
            . '<html dir="RTL"><body><table><tr>' . $this->headerCells(false) . '</tr>'
            . '<tr><td>2000000000000001</td><td>أ</td><td>ب</td><td>ذكر</td><td>2014-01-01</td><td>لا</td><td>عادي</td><td>2014</td><td>1</td><td>الوادي</td><td>أولى</td><td>1</td><td>خارجي</td><td>1</td><td>2025-09-15</td>'
            . '</table></body></html>';

        $result = $this->parser->parse($this->tempFile($html));

        $this->assertSame('متوسطة سبل النجاح (الوادي)', $result['school_name']);
    }

    private function headerCells(bool $withStream): string
    {
        $headers = $withStream
            ? ['رقم التعريف', 'اللقب', 'الاسم', 'الجنس', 'تاريخ الازدياد', 'مولود بحكم', 'عقد الميلاد', 'سنة التسجيل في سجل الولادات', 'رقم عقد الميلاد', 'مكان الازدياد', 'السنة', 'الشعبة', 'القسم', 'نظام التمدرس', 'رقم القيد', 'تاريخ التسجيل']
            : ['رقم التعريف', 'اللقب', 'الاسم', 'الجنس', 'تاريخ الازدياد', 'مولود بحكم', 'عقد الميلاد', 'سنة التسجيل في سجل الولادات', 'رقم عقد الميلاد', 'مكان الازدياد', 'السنة', 'القسم', 'نظام التمدرس', 'رقم القيد', 'تاريخ التسجيل'];

        return implode('', array_map(fn ($h) => "<th style=\"background:#f9f9f9;\">{$h}</th>", $headers));
    }

    private function ministryHtml(string $schoolName, bool $withStream, array $rows): string
    {
        $html = '<br/>' . $schoolName . '<html dir="RTL"><head><meta charset="UTF-8"></head><body><table border="1">'
            . '<tr>' . $this->headerCells($withStream) . '</tr>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . $cell . '</td>';
            }
        }

        return $html . '</table></body></html>';
    }

    private function tempFile(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'parser-test-') . '.xls';
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;

        return $path;
    }
}
