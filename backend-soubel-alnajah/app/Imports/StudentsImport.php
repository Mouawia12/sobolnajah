<?php

namespace App\Imports;

use App\Models\School\Section;
use App\Services\StudentEnrollmentService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
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

    public function collection(Collection $rows)
    {
        $errors = [];
        $rowNumber = 1;

        foreach ($rows as $row) {
            $rowNumber++;

            try {
                $sectionId = $row['section_id'] ?? null;

                if (!$sectionId) {
                    throw ValidationException::withMessages([
                        'section_id' => 'Section identifier is required for each row.',
                    ]);
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
            } catch (ValidationException $exception) {
                foreach ($exception->errors() as $messages) {
                    foreach ((array) $messages as $message) {
                        $errors[] = "Row {$rowNumber}: {$message}";
                    }
                }
            } catch (Throwable $exception) {
                $errors[] = "Row {$rowNumber}: {$exception->getMessage()}";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages(['file' => $errors]);
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
}
