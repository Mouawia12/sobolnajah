<?php

namespace App\Actions\Accounting;

use App\Models\Accounting\ContractInstallment;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentReceipt;
use App\Models\Accounting\StudentContract;
use App\Models\Inscription\StudentInfo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class ImportAccountingWorkbookAction
{
    /**
     * @return array{
     *   contracts_created:int,
     *   contracts_updated:int,
     *   payments_created:int,
     *   rows_skipped:int,
      *   skipped_rows:array<int,array{sheet:string,row:int,reason:string,contract_no:string,student_name:string}>,
      *   preview_contracts:array<int,array{contract_no:string,student_name:string,academic_year:string,total_amount:float,is_new:bool}>,
     *   preview_payments:array<int,array{receipt_number:string,contract_no:string,type:string,amount:float,paid_on:string}>,
     *   validation_warnings:array<int,array{sheet:string,row:int,type:string,message:string,contract_no:string}>
     * }
     */
    public function execute(UploadedFile $file, int $schoolId, int $actorId, bool $dryRun = false): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());

        $contractsSheet = $spreadsheet->getSheetByName('عقود التلاميذ');
        $moneySheet = $spreadsheet->getSheetByName('دراهم');

        if (!$contractsSheet || !$moneySheet) {
            throw new RuntimeException('ملف Excel لا يحتوي الأوراق المطلوبة: عقود التلاميذ + دراهم');
        }

        $studentMap = $this->buildStudentMap($schoolId);

        $summary = [
            'contracts_created' => 0,
            'contracts_updated' => 0,
            'payments_created' => 0,
            'rows_skipped' => 0,
            'skipped_rows' => [],
            'preview_contracts' => [],
            'preview_payments' => [],
            'validation_warnings' => [],
        ];

        $runner = function () use ($contractsSheet, $moneySheet, $studentMap, $schoolId, $actorId, &$summary, $dryRun): void {
            $contractHeader = $this->resolveHeaderMap($contractsSheet->toArray(null, false, false, false), ['رقم العقد', 'اسم ولقب التلاميذ']);
            $moneyHeader = $this->resolveHeaderMap($moneySheet->toArray(null, false, false, false), ['رقم العقد', 'اسم ولقب التلاميذ']);

            $contractsByExternalNo = $this->importContracts(
                $contractsSheet->toArray(null, false, false, false),
                $contractHeader,
                $studentMap,
                $schoolId,
                $actorId,
                $summary,
                'عقود التلاميذ',
                $dryRun
            );

            $this->importPayments(
                $moneySheet->toArray(null, false, false, false),
                $moneyHeader,
                $contractsByExternalNo,
                $schoolId,
                $actorId,
                $summary,
                'دراهم',
                $dryRun
            );
        };

        if ($dryRun) {
            DB::beginTransaction();
            try {
                $runner();
            } finally {
                DB::rollBack();
            }
        } else {
            DB::transaction($runner);
        }

        return $summary;
    }

    private function importContracts(
        array $rows,
        array $header,
        array $studentMap,
        int $schoolId,
        int $actorId,
        array &$summary,
        string $sheetName,
        bool $collectPreview
    ): array {
        $contractsByExternalNo = [];

        foreach ($rows as $index => $row) {
            if ($index <= $header['__header_row']) {
                continue;
            }

            $externalNo = $this->stringCell($row, $header, 'رقم العقد');
            $studentName = $this->stringCell($row, $header, 'اسم ولقب التلاميذ');

            if ($externalNo === '' || $studentName === '') {
                continue;
            }

            $studentId = $studentMap[$this->normalizeName($studentName)] ?? null;
            if (!$studentId) {
                $this->registerSkip($summary, $sheetName, $index + 1, 'student_not_found_in_school', $externalNo, $studentName);
                continue;
            }

            $academicYear = $this->normalizeAcademicYear($this->stringCell($row, $header, 'السنة الدراسية'));
            $guardianName = $this->stringCell($row, $header, 'اسم والي');
            $totalAmount = $this->numericCell($row, $header, 'مجموع');

            if ($academicYear === '') {
                $academicYear = now()->format('Y') . '-' . (now()->year + 1);
            }

            if ($totalAmount <= 0) {
                $totalAmount = $this->sumMonthlyFromContractSheet($row, $header);
            }

            $monthlyTotal = $this->sumMonthlyFromContractSheet($row, $header);
            if ($totalAmount > 0 && $monthlyTotal > 0 && abs($totalAmount - $monthlyTotal) > 0.01) {
                $this->registerWarning(
                    $summary,
                    $sheetName,
                    $index + 1,
                    'contracts_total_mismatch',
                    sprintf('Contract total (%.2f) does not match months sum (%.2f).', $totalAmount, $monthlyTotal),
                    $externalNo
                );
            }

            $contract = StudentContract::query()->firstOrNew([
                'school_id' => $schoolId,
                'student_id' => $studentId,
                'academic_year' => $academicYear,
            ]);

            $isNew = !$contract->exists;

            $contract->fill([
                'external_contract_no' => $externalNo,
                'guardian_name' => $guardianName !== '' ? $guardianName : null,
                'total_amount' => max($totalAmount, 0),
                'plan_type' => 'monthly',
                'installments_count' => 9,
                'starts_on' => $this->dateCell($row, $header, 'تاريخ امضاء العقد') ?? now()->toDateString(),
                'status' => 'active',
                'created_by' => $isNew ? $actorId : $contract->created_by,
                'updated_by' => $actorId,
                'metadata' => [
                    'student_birth_date' => $this->dateCell($row, $header, 'تاريخ ميلاد'),
                    'guardian_phone' => $this->stringCell($row, $header, 'رقم الهاتف'),
                    'sibling_group' => $this->stringCell($row, $header, 'الاخوة'),
                ],
            ]);
            $contract->save();

            $this->syncInstallmentsFromContractRow($contract, $row, $header);

            if ($isNew) {
                $summary['contracts_created']++;
            } else {
                $summary['contracts_updated']++;
            }

            if ($collectPreview && count($summary['preview_contracts']) < 200) {
                $summary['preview_contracts'][] = [
                    'contract_no' => $externalNo,
                    'student_name' => $studentName,
                    'academic_year' => $academicYear,
                    'total_amount' => (float) max($totalAmount, 0),
                    'is_new' => $isNew,
                ];
            }

            $contractsByExternalNo[$this->contractKey($externalNo, $academicYear)] = $contract;
            $contractsByExternalNo[$this->contractKey($externalNo, '')] = $contract;
        }

        return $contractsByExternalNo;
    }

    private function importPayments(
        array $rows,
        array $header,
        array $contractsByExternalNo,
        int $schoolId,
        int $actorId,
        array &$summary,
        string $sheetName,
        bool $collectPreview
    ): void {
        foreach ($rows as $index => $row) {
            if ($index <= $header['__header_row']) {
                continue;
            }

            $externalNo = $this->stringCell($row, $header, 'رقم العقد');
            if ($externalNo === '') {
                continue;
            }

            $academicYear = $this->normalizeAcademicYear($this->stringCell($row, $header, 'السنة الدراسية'));
            $contract = $contractsByExternalNo[$this->contractKey($externalNo, $academicYear)]
                ?? $contractsByExternalNo[$this->contractKey($externalNo, '')]
                ?? null;

            if (!$contract) {
                $this->registerSkip(
                    $summary,
                    $sheetName,
                    $index + 1,
                    'contract_not_found_for_payment_row',
                    $externalNo,
                    $this->stringCell($row, $header, 'اسم ولقب التلاميذ')
                );
                continue;
            }

            $registrationReceipt = $this->stringCell($row, $header, 'رقم وصل اشتراك');
            $registrationAmount = $this->numericCell($row, $header, 'حقوق الاشتراك');
            $monthlyPaidSum = 0.0;
            if ($registrationReceipt !== '' && $registrationAmount > 0) {
                $created = $this->createPaymentRecord(
                    $contract,
                    'SUB-' . $registrationReceipt,
                    $registrationAmount,
                    $this->derivePaidOn($academicYear, 9),
                    'registration',
                    $schoolId,
                    $actorId,
                    null,
                    $collectPreview,
                    $externalNo,
                    $summary
                );
                if ($created) {
                    $summary['payments_created']++;
                }
            }

            foreach ($this->monthlyReceiptAmountColumns() as $pair) {
                $receipt = $this->stringCell($row, $header, $pair['receipt']);
                $amount = $this->numericCell($row, $header, $pair['amount']);
                $monthlyPaidSum += max($amount, 0);

                if ($receipt === '' || $amount <= 0) {
                    continue;
                }

                $installment = $contract->installments()->where('label', $pair['label'])->first();
                $created = $this->createPaymentRecord(
                    $contract,
                    (string) $receipt,
                    $amount,
                    $this->derivePaidOn($academicYear, $pair['month']),
                    'monthly',
                    $schoolId,
                    $actorId,
                    $installment?->id,
                    $collectPreview,
                    $externalNo,
                    $summary
                );
                if ($created) {
                    $summary['payments_created']++;
                }
            }

            $expectedTotal = $this->numericCell($row, $header, 'المجموع الإجمالي');
            $expectedRegistration = $this->numericCell($row, $header, 'مجموع حقوق الاشتراك');
            $expectedMonthly = $this->numericCell($row, $header, 'مجموع دفعات');

            if ($expectedTotal > 0 && abs(($registrationAmount + $monthlyPaidSum) - $expectedTotal) > 0.01) {
                $this->registerWarning(
                    $summary,
                    $sheetName,
                    $index + 1,
                    'money_total_mismatch',
                    sprintf('Grand total mismatch: expected %.2f, computed %.2f.', $expectedTotal, ($registrationAmount + $monthlyPaidSum)),
                    $externalNo
                );
            }

            if ($expectedRegistration > 0 && abs($registrationAmount - $expectedRegistration) > 0.01) {
                $this->registerWarning(
                    $summary,
                    $sheetName,
                    $index + 1,
                    'money_registration_mismatch',
                    sprintf('Registration sum mismatch: expected %.2f, computed %.2f.', $expectedRegistration, $registrationAmount),
                    $externalNo
                );
            }

            if ($expectedMonthly > 0 && abs($monthlyPaidSum - $expectedMonthly) > 0.01) {
                $this->registerWarning(
                    $summary,
                    $sheetName,
                    $index + 1,
                    'money_monthly_mismatch',
                    sprintf('Monthly sum mismatch: expected %.2f, computed %.2f.', $expectedMonthly, $monthlyPaidSum),
                    $externalNo
                );
            }

            $this->refreshContractStatus($contract->fresh(['payments', 'installments']), $actorId);
        }
    }

    private function createPaymentRecord(
        StudentContract $contract,
        string $receiptNumber,
        float $amount,
        string $paidOn,
        string $type,
        int $schoolId,
        int $actorId,
        ?int $installmentId,
        bool $collectPreview,
        string $contractExternalNo,
        array &$summary
    ): bool {
        $existing = Payment::query()
            ->where('school_id', $schoolId)
            ->where('receipt_number', $receiptNumber)
            ->first();

        if ($existing) {
            return false;
        }

        $payment = Payment::query()->create([
            'school_id' => $schoolId,
            'contract_id' => $contract->id,
            'installment_id' => $installmentId,
            'receipt_number' => $receiptNumber,
            'paid_on' => $paidOn,
            'amount' => $amount,
            'payment_method' => 'cash',
            'notes' => $type,
            'received_by' => $actorId,
            'created_by' => $actorId,
        ]);

        PaymentReceipt::query()->create([
            'school_id' => $schoolId,
            'payment_id' => $payment->id,
            'receipt_code' => 'RCPT-' . Str::upper(Str::slug($receiptNumber, '-')),
            'issued_at' => now(),
            'payload' => [
                'type' => $type,
                'amount' => $amount,
                'paid_on' => $paidOn,
                'contract_id' => $contract->id,
            ],
        ]);

        if ($installmentId) {
            $installment = ContractInstallment::query()->find($installmentId);
            if ($installment) {
                $newPaid = (float) $installment->paid_amount + $amount;
                $installment->update([
                    'paid_amount' => $newPaid,
                    'status' => $newPaid >= (float) $installment->amount ? 'paid' : 'partial',
                ]);
            }
        }

        if ($collectPreview && count($summary['preview_payments']) < 400) {
            $summary['preview_payments'][] = [
                'receipt_number' => $receiptNumber,
                'contract_no' => $contractExternalNo,
                'type' => $type,
                'amount' => $amount,
                'paid_on' => $paidOn,
            ];
        }

        return true;
    }

    private function syncInstallmentsFromContractRow(StudentContract $contract, array $row, array $header): void
    {
        $contract->installments()->delete();

        $installmentNo = 1;
        foreach ($this->monthlyReceiptAmountColumns() as $month) {
            $amount = $this->numericCell($row, $header, $month['amount']);
            if ($amount <= 0) {
                continue;
            }

            ContractInstallment::query()->create([
                'contract_id' => $contract->id,
                'installment_no' => $installmentNo,
                'due_date' => $this->derivePaidOn($contract->academic_year, $month['month']),
                'amount' => $amount,
                'paid_amount' => 0,
                'status' => 'pending',
                'label' => $month['label'],
            ]);

            $installmentNo++;
        }

        if ($installmentNo === 1) {
            ContractInstallment::query()->create([
                'contract_id' => $contract->id,
                'installment_no' => 1,
                'due_date' => $contract->starts_on ?? now()->toDateString(),
                'amount' => (float) $contract->total_amount,
                'paid_amount' => 0,
                'status' => 'pending',
                'label' => 'Yearly Payment',
            ]);
        }
    }

    private function refreshContractStatus(StudentContract $contract, int $actorId): void
    {
        $paidTotal = (float) $contract->payments()->sum('amount');
        $remaining = (float) $contract->total_amount - $paidTotal;
        $hasOverdue = $contract->installments()
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->exists();

        $status = 'active';
        if ($remaining <= 0) {
            $status = 'paid';
        } elseif ($paidTotal > 0) {
            $status = 'partial';
        }
        if ($remaining > 0 && $hasOverdue) {
            $status = 'overdue';
        }

        $contract->update([
            'status' => $status,
            'updated_by' => $actorId,
        ]);
    }

    private function resolveHeaderMap(array $rows, array $requiredHeaders): array
    {
        foreach ($rows as $rowIndex => $row) {
            $map = [];
            foreach ($row as $col => $value) {
                $label = $this->normalizeHeader((string) $value);
                if ($label !== '') {
                    $map[$label] = $col;
                }
            }

            $ok = true;
            foreach ($requiredHeaders as $required) {
                if (!array_key_exists($this->normalizeHeader($required), $map)) {
                    $ok = false;
                    break;
                }
            }

            if ($ok) {
                $map['__header_row'] = $rowIndex;
                return $map;
            }
        }

        throw new RuntimeException('تعذر العثور على صف headers المطلوب داخل ملف Excel.');
    }

    private function buildStudentMap(int $schoolId): array
    {
        $students = StudentInfo::query()
            ->forSchool($schoolId)
            ->with('user:id,name')
            ->get();

        $map = [];

        foreach ($students as $student) {
            $user = $student->user;
            if (!$user) {
                continue;
            }

            foreach ($this->nameCandidates($user) as $candidate) {
                $normalized = $this->normalizeName($candidate);
                if ($normalized !== '') {
                    $map[$normalized] = $student->id;
                }
            }
        }

        return $map;
    }

    private function nameCandidates(User $user): array
    {
        $candidates = [];

        if (method_exists($user, 'getTranslations')) {
            $translations = $user->getTranslations('name');
            foreach ($translations as $translation) {
                if (is_string($translation) && trim($translation) !== '') {
                    $candidates[] = $translation;
                }
            }
        }

        $raw = $user->getAttribute('name');
        if (is_string($raw) && trim($raw) !== '') {
            $candidates[] = $raw;
        }

        return array_values(array_unique($candidates));
    }

    private function monthlyReceiptAmountColumns(): array
    {
        return [
            ['receipt' => 'رقم الوصل 09', 'amount' => 'دفعة سبتمبر', 'month' => 9, 'label' => 'September'],
            ['receipt' => 'رقم الوصل 10', 'amount' => 'دفعة أكتوبر', 'month' => 10, 'label' => 'October'],
            ['receipt' => 'رقم الوصل 11', 'amount' => 'دفعة نوفمبر', 'month' => 11, 'label' => 'November'],
            ['receipt' => 'رقم الوصل 12', 'amount' => 'دفعة ديسمبر', 'month' => 12, 'label' => 'December'],
            ['receipt' => 'رقم الوصل 01', 'amount' => 'دفعة جانفي', 'month' => 1, 'label' => 'January'],
            ['receipt' => 'رقم الوصل 02', 'amount' => 'دفعة فيفري', 'month' => 2, 'label' => 'February'],
            ['receipt' => 'رقم الوصل 03', 'amount' => 'دفعة مارس', 'month' => 3, 'label' => 'March'],
            ['receipt' => 'رقم الوصل 04', 'amount' => 'دفعة أفريل', 'month' => 4, 'label' => 'April'],
            ['receipt' => 'رقم الوصل 05', 'amount' => 'دفعة ماي', 'month' => 5, 'label' => 'May'],
        ];
    }

    private function derivePaidOn(string $academicYear, int $month): string
    {
        $startYear = (int) Str::before($academicYear, '-');
        if ($startYear <= 0) {
            $startYear = now()->year;
        }

        $year = $month >= 9 ? $startYear : ($startYear + 1);

        return sprintf('%04d-%02d-01', $year, $month);
    }

    private function contractKey(string $externalNo, string $academicYear): string
    {
        return trim($externalNo) . '|' . trim($academicYear);
    }

    private function sumMonthlyFromContractSheet(array $row, array $header): float
    {
        $sum = 0.0;
        foreach (['سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر', 'جانفي', 'فيفري', 'مارس', 'افريل', 'ماي'] as $month) {
            $sum += $this->numericCell($row, $header, $month);
        }

        return round($sum, 2);
    }

    private function dateCell(array $row, array $header, string $label): ?string
    {
        $raw = $this->stringCell($row, $header, $label);
        if ($raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            $timestamp = ((int) $raw - 25569) * 86400;
            return gmdate('Y-m-d', $timestamp);
        }

        $raw = str_replace('/', '-', $raw);
        $parts = explode('-', $raw);
        if (count($parts) === 3) {
            $d = (int) $parts[0];
            $m = (int) $parts[1];
            $y = (int) $parts[2];
            if ($y > 1900 && $m >= 1 && $m <= 12 && $d >= 1 && $d <= 31) {
                return sprintf('%04d-%02d-%02d', $y, $m, $d);
            }
        }

        return null;
    }

    private function numericCell(array $row, array $header, string $label): float
    {
        $value = $this->stringCell($row, $header, $label);
        if ($value === '') {
            return 0.0;
        }

        $normalized = str_replace([',', ' '], ['', ''], $value);
        $normalized = str_replace(['٫', '٬'], ['.', ''], $normalized);

        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }

    private function stringCell(array $row, array $header, string $label): string
    {
        $index = $header[$this->normalizeHeader($label)] ?? null;
        if ($index === null) {
            return '';
        }

        $value = $row[$index] ?? '';

        return trim((string) $value);
    }

    private function normalizeAcademicYear(string $value): string
    {
        $clean = trim(str_replace(['/', '\\', '–', '—'], '-', $value));
        $clean = preg_replace('/\s+/', '', $clean) ?: '';

        return $clean;
    }

    private function normalizeHeader(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?: '';

        return $value;
    }

    private function normalizeName(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^\p{Arabic}\p{L}\p{N}]+/u', '', $value) ?: '';

        return $value;
    }

    private function registerSkip(
        array &$summary,
        string $sheet,
        int $row,
        string $reason,
        string $contractNo,
        string $studentName
    ): void {
        $summary['rows_skipped']++;
        $summary['skipped_rows'][] = [
            'sheet' => $sheet,
            'row' => $row,
            'reason' => $reason,
            'contract_no' => $contractNo,
            'student_name' => $studentName,
        ];
    }

    private function registerWarning(
        array &$summary,
        string $sheet,
        int $row,
        string $type,
        string $message,
        string $contractNo
    ): void {
        if (count($summary['validation_warnings']) >= 300) {
            return;
        }

        $summary['validation_warnings'][] = [
            'sheet' => $sheet,
            'row' => $row,
            'type' => $type,
            'message' => $message,
            'contract_no' => $contractNo,
        ];
    }
}
