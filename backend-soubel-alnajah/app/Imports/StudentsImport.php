<?php

namespace App\Imports;

use App\Models\User;
use App\Models\School\Section;
use App\Services\StudentEnrollmentService;
use App\Services\StudentImportProgressService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        private StudentEnrollmentService $enrollmentService,
        private ?int $schoolId = null,
        private ?StudentImportProgressService $progressService = null,
        private ?string $progressToken = null
    ) {
    }

    private int $totalRows = 0;
    private int $processedRows = 0;
    private int $importedRows = 0;
    private int $sectionUpdatedRows = 0;
    private int $duplicateRows = 0;
    private int $skippedRows = 0;
    private int $autoFilledFields = 0;
    private array $issues = [];
    private array $seenFingerprints = [];

    public function collection(Collection $rows)
    {
        $rowNumber = 1;
        $usedEmails = [];
        $this->totalRows = $rows->count();
        $this->reportProgress();

        foreach ($rows as $row) {
            $rowNumber++;

            try {
                if ($this->isRowEmpty($row)) {
                    $this->skippedRows++;
                    $this->issues[] = "Row {$rowNumber}: empty row skipped.";
                    continue;
                }

                $row = $this->normalizeRow($row, $rowNumber, $usedEmails);
                $fingerprint = $this->rowFingerprint($row);
                if ($fingerprint !== null && isset($this->seenFingerprints[$fingerprint])) {
                    $this->duplicateRows++;
                    $this->issues[] = "Row {$rowNumber}: duplicate row in the same file (already processed).";
                    continue;
                }
                if ($fingerprint !== null) {
                    $this->seenFingerprints[$fingerprint] = true;
                }

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

                $result = $this->enrollmentService->importStudent($studentPayload, $guardianPayload, $section);
                $status = (string) ($result['status'] ?? '');

                if ($status === 'created') {
                    $this->importedRows++;
                } elseif ($status === 'section_updated') {
                    $this->sectionUpdatedRows++;
                    $this->issues[] = "Row {$rowNumber}: duplicate detected and section updated.";
                } elseif ($status === 'duplicate') {
                    $this->duplicateRows++;
                    $this->issues[] = "Row {$rowNumber}: duplicate detected (same section), skipped.";
                } else {
                    $this->skippedRows++;
                    $this->issues[] = "Row {$rowNumber}: unexpected import status.";
                }
            } catch (ValidationException $exception) {
                if ($this->isDuplicateValidationError($exception)) {
                    $this->duplicateRows++;
                } else {
                    $this->skippedRows++;
                }
                $this->issues[] = "Row {$rowNumber}: " . $this->flattenValidationError($exception);
            } catch (Throwable $exception) {
                $this->issues[] = "Row {$rowNumber}: {$exception->getMessage()}";
                $this->skippedRows++;
            } finally {
                $this->processedRows++;
                $this->reportProgress();
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

    private function rowFingerprint(array $row): ?string
    {
        $first = $this->normalizeNameToken((string) ($row['prenom_fr'] ?? ''))
            ?: $this->normalizeNameToken((string) ($row['prenom_ar'] ?? ''));
        $last = $this->normalizeNameToken((string) ($row['nom_fr'] ?? ''))
            ?: $this->normalizeNameToken((string) ($row['nom_ar'] ?? ''));
        $birthDate = trim((string) ($row['datenaissance'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string) ($row['numtelephone'] ?? ''));
        $guardianPhone = preg_replace('/\D+/', '', (string) ($row['numtelephonewali'] ?? ''));
        $sectionId = trim((string) ($row['section_id'] ?? ''));

        if ($first === '' && $last === '' && $phone === '' && $guardianPhone === '') {
            return null;
        }

        return implode('|', [
            $sectionId,
            $first,
            $last,
            $birthDate,
            $phone,
            $guardianPhone,
        ]);
    }

    private function normalizeNameToken(string $value): string
    {
        $text = Str::lower(trim($value));
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $text);
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '', (string) $text);

        return trim((string) $text);
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

    public function getDuplicateRows(): int
    {
        return $this->duplicateRows;
    }

    public function getSectionUpdatedRows(): int
    {
        return $this->sectionUpdatedRows;
    }

    public function getSkippedRows(): int
    {
        return $this->skippedRows;
    }

    public function getNotImportedRows(): int
    {
        return $this->duplicateRows + $this->skippedRows;
    }

    public function getTotalRows(): int
    {
        return $this->totalRows;
    }

    public function getProcessedRows(): int
    {
        return $this->processedRows;
    }

    public function getAutoFilledFields(): int
    {
        return $this->autoFilledFields;
    }

    public function getIssues(): array
    {
        return $this->issues;
    }

    private function isDuplicateValidationError(ValidationException $exception): bool
    {
        $errors = $exception->errors();
        $duplicateFields = ['student', 'student_email'];

        foreach ($duplicateFields as $field) {
            if (!empty($errors[$field])) {
                $text = Str::lower(implode(' ', (array) $errors[$field]));
                if (Str::contains($text, ['already exists', 'similar identity'])) {
                    return true;
                }
            }
        }

        return false;
    }

    private function flattenValidationError(ValidationException $exception): string
    {
        $parts = [];
        foreach ($exception->errors() as $field => $messages) {
            foreach ((array) $messages as $message) {
                $parts[] = "{$field}: {$message}";
            }
        }

        return !empty($parts) ? implode(' | ', $parts) : $exception->getMessage();
    }

    private function reportProgress(): void
    {
        if (!$this->progressService || !$this->progressToken) {
            return;
        }

        $this->progressService->running($this->progressToken, [
            'total_rows' => $this->totalRows,
            'processed_rows' => $this->processedRows,
            'imported_rows' => $this->importedRows,
            'section_updated_rows' => $this->sectionUpdatedRows,
            'duplicate_rows' => $this->duplicateRows,
            'skipped_rows' => $this->skippedRows,
            'auto_filled_fields' => $this->autoFilledFields,
            'issues_preview' => array_slice($this->issues, -5),
            'latest_issue' => !empty($this->issues) ? end($this->issues) : null,
        ]);
    }
}
