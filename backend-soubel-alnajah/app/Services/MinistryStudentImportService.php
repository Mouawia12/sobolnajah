<?php

namespace App\Services;

use App\Models\Inscription\StudentInfo;
use App\Models\School\Section;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Throwable;

/**
 * منسق استيراد الطلبة من ملفات الوزارة (Eleve.xls) بمزامنة كاملة:
 *  - حل المؤسسة من رأس الملف (بحث-أو-إنشاء).
 *  - مزامنة السنوات/الشعب/الأقسام تلقائياً.
 *  - upsert للطلبة بمفتاح رقم التعريف الوطني (إضافة/تحديث/نقل قسم).
 *  - تقرير بالطلبة الموجودين في النظام والغائبين عن الملف (بدون أي حذف).
 */
class MinistryStudentImportService
{
    private const PROGRESS_EVERY = 25;
    private const ABSENT_PREVIEW_LIMIT = 50;

    /** الحقول الوزارية القابلة للمقارنة/التحديث على سجل الطالب. */
    private const UPDATABLE_FIELDS = [
        'gender',
        'datenaissance',
        'lieunaissance',
        'registration_number',
        'enrolled_at',
        'schooling_system',
        'birth_certificate_type',
        'birth_record_year',
        'birth_certificate_number',
        'born_by_judgment',
    ];

    public function __construct(
        private MinistryStudentFileParser $parser,
        private SchoolStructureSyncService $structureSync,
        private StudentEnrollmentService $enrollmentService,
        private StudentImportProgressService $progress,
    ) {
    }

    /**
     * @return array ملخص الاستيراد (عدّادات + تقرير الغائبين)
     */
    public function import(string $absolutePath, string $token): array
    {
        $parsed = $this->parser->parse($absolutePath);
        $school = $this->structureSync->resolveSchool($parsed['school_name']);

        $rows = $parsed['rows'];
        $hasStreams = $parsed['has_streams'];
        $totalRows = count($rows);

        $counters = [
            'created_rows' => 0,
            'updated_rows' => 0,
            'moved_rows' => 0,
            'unchanged_rows' => 0,
            'failed_rows' => 0,
        ];
        $issues = [];
        $seenNationalIds = [];

        $this->progress->running($token, [
            'total_rows' => $totalRows,
            'processed_rows' => 0,
            'school_name' => $parsed['school_name'],
        ] + $counters);

        $processed = 0;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +1 لصف الرؤوس و+1 للفهرسة من 1

            try {
                $nationalId = preg_replace('/\D+/', '', (string) ($row['national_id'] ?? ''));

                if (strlen($nationalId) !== 16) {
                    $counters['failed_rows']++;
                    $issues[] = "السطر {$rowNumber}: رقم تعريف غير صالح (يجب أن يكون 16 رقماً).";
                    continue;
                }

                if (isset($seenNationalIds[$nationalId])) {
                    $counters['unchanged_rows']++;
                    $issues[] = "السطر {$rowNumber}: رقم التعريف {$nationalId} مكرر داخل الملف، تم تجاهله.";
                    continue;
                }
                $seenNationalIds[$nationalId] = true;

                $gradeName = (string) ($row['grade'] ?? '');
                $sectionName = (string) ($row['section'] ?? '');

                if ($gradeName === '' || $sectionName === '') {
                    $counters['failed_rows']++;
                    $issues[] = "السطر {$rowNumber}: السنة أو القسم غير محدد.";
                    continue;
                }

                $section = $this->structureSync->resolveSection(
                    $school,
                    $gradeName,
                    $hasStreams ? ($row['stream'] ?? null) : null,
                    $sectionName
                );

                $existing = StudentInfo::withTrashed()->where('national_id', $nationalId)->first();

                if ($existing) {
                    $status = $this->updateExistingStudent($existing, $row, $section, $nationalId);
                    $counters[$status . '_rows']++;
                } else {
                    $this->createStudent($row, $section, $nationalId);
                    $counters['created_rows']++;
                }
            } catch (Throwable $exception) {
                $counters['failed_rows']++;
                $issues[] = "السطر {$rowNumber}: " . $exception->getMessage();
            } finally {
                $processed++;

                if ($processed % self::PROGRESS_EVERY === 0 || $processed === $totalRows) {
                    $this->progress->running($token, [
                        'total_rows' => $totalRows,
                        'processed_rows' => $processed,
                        'school_name' => $parsed['school_name'],
                        'issues_preview' => array_slice($issues, -5),
                        'latest_issue' => !empty($issues) ? end($issues) : null,
                    ] + $counters);
                }
            }
        }

        $absentReport = $this->buildAbsentReport($school->id, $seenNationalIds);

        return [
            'school_name' => $parsed['school_name'],
            'total_rows' => $totalRows,
            'processed_rows' => $processed,
            'structure_created' => $this->structureSync->created,
            'issues' => $issues,
        ] + $counters + $absentReport;
    }

    /**
     * تحديث طالب موجود (مطابق برقم التعريف): تحديث الحقول ونقل القسم عند تغيّره.
     * لا يمس الولي إطلاقاً.
     *
     * @return string created|updated|moved|unchanged
     */
    private function updateExistingStudent(StudentInfo $student, array $row, Section $section, string $nationalId): string
    {
        $changes = [];

        $payload = $this->buildMinistryFields($row, $nationalId);
        unset($payload['national_id']);

        $payload['gender'] = $this->normalizeGender($row['gender'] ?? null);
        $payload['datenaissance'] = $this->normalizeDate($row['birth_date'] ?? null) ?? (string) $student->datenaissance;
        $payload['lieunaissance'] = trim((string) ($row['birth_place'] ?? '')) ?: $student->lieunaissance;

        foreach (self::UPDATABLE_FIELDS as $field) {
            if (!array_key_exists($field, $payload)) {
                continue;
            }

            $current = $student->{$field};
            $incoming = $payload[$field];

            if ($field === 'datenaissance' || $field === 'enrolled_at') {
                $current = $current ? Carbon::parse((string) $current)->format('Y-m-d') : null;
            }

            if ($field === 'born_by_judgment') {
                $current = (bool) $current;
            }

            if ($incoming !== null && $incoming !== '' && $incoming != $current) {
                $changes[$field] = $incoming;
            }
        }

        // تحديث الاسم العربي إذا تغيّر
        $incomingFirst = trim((string) ($row['first_name_ar'] ?? ''));
        $incomingLast = trim((string) ($row['last_name_ar'] ?? ''));

        if ($incomingFirst !== '' && $incomingFirst !== $student->getTranslation('prenom', 'ar', false)) {
            $student->setTranslation('prenom', 'ar', $incomingFirst);
        }
        if ($incomingLast !== '' && $incomingLast !== $student->getTranslation('nom', 'ar', false)) {
            $student->setTranslation('nom', 'ar', $incomingLast);
        }

        $moved = (int) $student->section_id !== (int) $section->id;
        if ($moved) {
            $changes['section_id'] = $section->id;
        }

        $wasTrashed = $student->trashed();
        $hasFieldChanges = !empty($changes) || $student->isDirty();

        if ($wasTrashed) {
            $student->restore();
        }

        if ($hasFieldChanges) {
            $student->fill($changes);
            $student->save();
        }

        if ($moved) {
            return 'moved';
        }

        return ($hasFieldChanges || $wasTrashed) ? 'updated' : 'unchanged';
    }

    private function createStudent(array $row, Section $section, string $nationalId): StudentInfo
    {
        $firstName = trim((string) ($row['first_name_ar'] ?? '')) ?: 'غير محدد';
        $lastName = trim((string) ($row['last_name_ar'] ?? '')) ?: 'غير محدد';
        $birthPlace = trim((string) ($row['birth_place'] ?? '')) ?: 'غير محدد';

        $studentPayload = [
            'first_name' => ['ar' => $firstName, 'fr' => $firstName, 'en' => $firstName],
            'last_name' => ['ar' => $lastName, 'fr' => $lastName, 'en' => $lastName],
            'email' => $this->syntheticEmail('student', $nationalId),
            'gender' => $this->normalizeGender($row['gender'] ?? null),
            'phone' => $nationalId, // هاتف مؤقت فريد وثابت حتى يُستكمل لاحقاً
            'birth_date' => $this->normalizeDate($row['birth_date'] ?? null) ?? '2010-01-01',
            'birth_place' => $birthPlace,
            'wilaya' => $birthPlace,
            'dayra' => $birthPlace,
            'baladia' => $birthPlace,
        ];

        $guardianName = 'ولي ' . trim($firstName . ' ' . $lastName);
        $guardianPayload = [
            'first_name' => ['ar' => $guardianName, 'fr' => $guardianName, 'en' => $guardianName],
            'last_name' => ['ar' => $lastName, 'fr' => $lastName, 'en' => $lastName],
            'relation' => 'ولي',
            'address' => 'غير محدد',
            'wilaya' => $birthPlace,
            'dayra' => $birthPlace,
            'baladia' => $birthPlace,
            'phone' => '9' . substr($nationalId, 1), // هاتف مؤقت فريد مشتق من رقم التعريف
            'email' => $this->syntheticEmail('guardian', $nationalId),
        ];

        return $this->enrollmentService->createStudentFromImport(
            $studentPayload,
            $guardianPayload,
            $section,
            $this->buildMinistryFields($row, $nationalId)
        );
    }

    private function buildMinistryFields(array $row, string $nationalId): array
    {
        return [
            'national_id' => $nationalId,
            'registration_number' => trim((string) ($row['registration_number'] ?? '')) ?: null,
            'enrolled_at' => $this->normalizeDate($row['enrolled_at'] ?? null),
            'schooling_system' => $this->normalizeSchoolingSystem($row['schooling_system'] ?? null),
            'birth_certificate_type' => trim((string) ($row['birth_certificate_type'] ?? '')) ?: null,
            'birth_record_year' => trim((string) ($row['birth_record_year'] ?? '')) ?: null,
            'birth_certificate_number' => trim((string) ($row['birth_certificate_number'] ?? '')) ?: null,
            'born_by_judgment' => trim((string) ($row['born_by_judgment'] ?? '')) === 'نعم',
        ];
    }

    /**
     * @return array{absent_count: int, absent_preview: array, legacy_count: int}
     */
    private function buildAbsentReport(int $schoolId, array $seenNationalIds): array
    {
        $absentQuery = StudentInfo::query()
            ->forSchool($schoolId)
            ->whereNotNull('national_id')
            ->whereNotIn('national_id', array_keys($seenNationalIds));

        $absentCount = (clone $absentQuery)->count();

        $absentPreview = $absentQuery
            ->with('section:id,name_section')
            ->limit(self::ABSENT_PREVIEW_LIMIT)
            ->get(['id', 'national_id', 'prenom', 'nom', 'section_id'])
            ->map(fn (StudentInfo $student) => [
                'id' => $student->id,
                'national_id' => $student->national_id,
                'name' => trim($student->getTranslation('prenom', 'ar', false) . ' ' . $student->getTranslation('nom', 'ar', false))
                    ?: trim((string) $student->prenom . ' ' . (string) $student->nom),
                'section' => optional($student->section)->getTranslation('name_section', 'ar', false),
            ])
            ->values()
            ->all();

        $legacyCount = StudentInfo::query()
            ->forSchool($schoolId)
            ->whereNull('national_id')
            ->count();

        return [
            'absent_count' => $absentCount,
            'absent_preview' => $absentPreview,
            'legacy_count' => $legacyCount,
        ];
    }

    private function normalizeGender($value): int
    {
        $raw = trim((string) $value);

        return in_array($raw, ['أنثى', 'انثى'], true) ? 0 : 1;
    }

    private function normalizeSchoolingSystem($value): ?string
    {
        $raw = trim((string) $value);

        return in_array($raw, ['خارجي', 'نصف داخلي', 'داخلي'], true) ? $raw : null;
    }

    private function normalizeDate($value): ?string
    {
        $date = trim((string) $value);
        if ($date === '') {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private function syntheticEmail(string $prefix, string $nationalId): string
    {
        $email = sprintf('%s_%s@import.local', $prefix, $nationalId);

        if (\App\Models\User::where('email', $email)->exists()) {
            $email = sprintf('%s_%s_%s@import.local', $prefix, $nationalId, Str::lower(Str::random(6)));
        }

        return $email;
    }
}
