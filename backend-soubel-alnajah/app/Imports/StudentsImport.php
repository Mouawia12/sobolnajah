<?php

namespace App\Imports;

use App\Models\User;
use App\Models\School\Section;
use App\Services\StudentEnrollmentService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        private StudentEnrollmentService $enrollmentService,
        private ?int $schoolId = null
    ) {
    }

    private int $importedRows = 0;
    private int $skippedRows = 0;
    private int $autoFilledFields = 0;
    private array $issues = [];

    public function collection(Collection $rows)
    {
        $rowNumber = 1;
        $usedEmails = [];

        foreach ($rows as $row) {
            $rowNumber++;

            try {
                if ($this->isRowEmpty($row)) {
                    $this->skippedRows++;
                    continue;
                }

                $row = $this->normalizeRow($row, $rowNumber, $usedEmails);
                $sectionId = $row['section_id'] ?? null;

                if (!$sectionId) {
                    $this->issues[] = "Row {$rowNumber}: section_id is missing.";
                    $this->skippedRows++;
                    continue;
                }

                $section = Section::query()
                    ->forSchool($this->schoolId)
                    ->findOrFail($sectionId);

                $studentPayload = [
                    'first_name' => [
                        'fr' => $row['prenom_fr'] ?? null,
                        'ar' => $row['prenom_ar'] ?? null,
                        'en' => $row['prenom_fr'] ?? null,
                    ],
                    'last_name' => [
                        'fr' => $row['nom_fr'] ?? null,
                        'ar' => $row['nom_ar'] ?? null,
                        'en' => $row['nom_fr'] ?? null,
                    ],
                    'email' => $row['email'] ?? null,
                    'gender' => isset($row['gender']) ? (int) $row['gender'] : null,
                    'phone' => $row['numtelephone'] ?? null,
                    'birth_date' => $this->formatDate($row['datenaissance'] ?? null),
                    'birth_place' => $row['lieunaissance'] ?? null,
                    'wilaya' => $row['wilaya'] ?? null,
                    'dayra' => $row['dayra'] ?? null,
                    'baladia' => $row['baladia'] ?? null,
                ];

                $guardianPayload = [
                    'first_name' => [
                        'fr' => $row['prenom_wali_fr'] ?? null,
                        'ar' => $row['prenom_wali_ar'] ?? null,
                        'en' => $row['prenom_wali_fr'] ?? null,
                    ],
                    'last_name' => [
                        'fr' => $row['nom_wali_fr'] ?? null,
                        'ar' => $row['nom_wali_ar'] ?? null,
                        'en' => $row['nom_wali_fr'] ?? null,
                    ],
                    'relation' => $row['relationetudiant'] ?? null,
                    'address' => $row['adressewali'] ?? null,
                    'wilaya' => $row['wilayawali'] ?? null,
                    'dayra' => $row['dayrawali'] ?? null,
                    'baladia' => $row['baladiawali'] ?? null,
                    'phone' => $row['numtelephonewali'] ?? null,
                    'email' => $row['emailwali'] ?? null,
                ];

                $this->enrollmentService->createStudent($studentPayload, $guardianPayload, $section);
                $this->importedRows++;
            } catch (Throwable $exception) {
                $this->issues[] = "Row {$rowNumber}: {$exception->getMessage()}";
                $this->skippedRows++;
            }
        }
    }

    private function formatDate($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception) {
                return null;
            }
        }

        return $value ?: null;
    }

    private function isRowEmpty($row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeRow($row, int $rowNumber, array &$usedEmails): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalized[$key] = is_string($value) ? trim($value) : $value;
        }

        $studentFirstFr = $this->valueOrDefault($normalized['prenom_fr'] ?? null, "Student{$rowNumber}");
        $studentLastFr = $this->valueOrDefault($normalized['nom_fr'] ?? null, "LN{$rowNumber}");
        $studentFirstAr = $this->valueOrDefault($normalized['prenom_ar'] ?? null, $studentFirstFr);
        $studentLastAr = $this->valueOrDefault($normalized['nom_ar'] ?? null, $studentLastFr);

        $normalized['prenom_fr'] = $studentFirstFr;
        $normalized['nom_fr'] = $studentLastFr;
        $normalized['prenom_ar'] = $studentFirstAr;
        $normalized['nom_ar'] = $studentLastAr;

        $normalized['email'] = $this->normalizeEmail(
            $normalized['email'] ?? null,
            "student{$rowNumber}",
            $usedEmails
        );

        $normalized['gender'] = $this->normalizeGender($normalized['gender'] ?? null);
        $normalized['numtelephone'] = $this->normalizePhone($normalized['numtelephone'] ?? null, 550000000 + $rowNumber);
        $normalized['datenaissance'] = $this->formatDate($normalized['datenaissance'] ?? null) ?? '2010-01-01';
        $normalized['lieunaissance'] = $this->valueOrDefault($normalized['lieunaissance'] ?? null, 'غير محدد');
        $normalized['wilaya'] = $this->valueOrDefault($normalized['wilaya'] ?? null, 'غير محدد');
        $normalized['dayra'] = $this->valueOrDefault($normalized['dayra'] ?? null, 'غير محدد');
        $normalized['baladia'] = $this->valueOrDefault($normalized['baladia'] ?? null, 'غير محدد');

        $guardianFirstFr = $this->valueOrDefault($normalized['prenom_wali_fr'] ?? null, "Guardian{$rowNumber}");
        $guardianLastFr = $this->valueOrDefault($normalized['nom_wali_fr'] ?? null, $studentLastFr);
        $guardianFirstAr = $this->valueOrDefault($normalized['prenom_wali_ar'] ?? null, $guardianFirstFr);
        $guardianLastAr = $this->valueOrDefault($normalized['nom_wali_ar'] ?? null, $guardianLastFr);

        $normalized['prenom_wali_fr'] = $guardianFirstFr;
        $normalized['nom_wali_fr'] = $guardianLastFr;
        $normalized['prenom_wali_ar'] = $guardianFirstAr;
        $normalized['nom_wali_ar'] = $guardianLastAr;

        $normalized['relationetudiant'] = $this->valueOrDefault($normalized['relationetudiant'] ?? null, 'ولي');
        $normalized['adressewali'] = $this->valueOrDefault($normalized['adressewali'] ?? null, 'غير محدد');
        $normalized['wilayawali'] = $this->valueOrDefault($normalized['wilayawali'] ?? null, $normalized['wilaya']);
        $normalized['dayrawali'] = $this->valueOrDefault($normalized['dayrawali'] ?? null, $normalized['dayra']);
        $normalized['baladiawali'] = $this->valueOrDefault($normalized['baladiawali'] ?? null, $normalized['baladia']);
        $normalized['numtelephonewali'] = $this->normalizePhone($normalized['numtelephonewali'] ?? null, 660000000 + $rowNumber);
        $normalized['emailwali'] = $this->normalizeEmail(
            $normalized['emailwali'] ?? null,
            "guardian{$rowNumber}",
            $usedEmails
        );

        return $normalized;
    }

    private function normalizeGender($value): int
    {
        if (is_numeric($value) && in_array((int) $value, [0, 1], true)) {
            return (int) $value;
        }

        $raw = Str::lower(trim((string) $value));
        if (in_array($raw, ['f', 'female', 'femme', 'انثى', 'أنثى'], true)) {
            $this->autoFilledFields++;
            return 0;
        }

        if (in_array($raw, ['m', 'male', 'homme', 'ذكر'], true)) {
            $this->autoFilledFields++;
            return 1;
        }

        $this->autoFilledFields++;
        return 1;
    }

    private function normalizePhone($value, int $fallback): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits !== '' && strlen($digits) >= 8) {
            return $digits;
        }

        $this->autoFilledFields++;
        return (string) $fallback;
    }

    private function normalizeEmail($value, string $prefix, array &$usedEmails): string
    {
        $email = trim((string) $value);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && !$this->emailTaken($email, $usedEmails)) {
            $usedEmails[$email] = true;
            return $email;
        }

        $counter = 1;
        do {
            $candidate = sprintf('%s_%d_%d@import.local', $prefix, time(), $counter);
            $counter++;
        } while ($this->emailTaken($candidate, $usedEmails));

        $usedEmails[$candidate] = true;
        $this->autoFilledFields++;

        return $candidate;
    }

    private function emailTaken(string $email, array $usedEmails): bool
    {
        if (isset($usedEmails[$email])) {
            return true;
        }

        return User::where('email', $email)->exists();
    }

    private function valueOrDefault($value, string $default): string
    {
        $text = trim((string) $value);
        if ($text !== '') {
            return $text;
        }

        $this->autoFilledFields++;
        return $default;
    }

    public function getImportedRows(): int
    {
        return $this->importedRows;
    }

    public function getSkippedRows(): int
    {
        return $this->skippedRows;
    }

    public function getAutoFilledFields(): int
    {
        return $this->autoFilledFields;
    }

    public function getIssues(): array
    {
        return $this->issues;
    }
}
