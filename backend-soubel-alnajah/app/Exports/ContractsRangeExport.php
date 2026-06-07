<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ContractsRangeExport implements FromArray, WithHeadings, WithTitle, WithEvents
{
    public function __construct(
        private Collection $contracts,
        private array $totals,
        private Carbon $fromDate,
        private Carbon $toDate,
    ) {
    }

    public function title(): string
    {
        return 'العقود ' . $this->fromDate->format('Y-m-d') . ' إلى ' . $this->toDate->format('Y-m-d');
    }

    public function headings(): array
    {
        return [
            '#',
            'رقم العقد',
            'الطالب',
            'السنة',
            'الخطة',
            'الحالة',
            'الإجمالي',
            'المدفوع',
            'المتبقي',
            'تاريخ الإنشاء',
        ];
    }

    public function array(): array
    {
        // نفس تسميات تقرير الطباعة print-range.blade.php
        $planLabels = ['yearly' => 'كاش (دفعة واحدة)', 'monthly' => 'شهري (سبتمبر-أفريل)', 'installments' => 'أقساط (3 دفعات)'];
        $statusLabels = ['draft' => 'مسودة', 'active' => 'نشط', 'partial' => 'جزئي', 'paid' => 'مدفوع', 'overdue' => 'متأخر', 'pending' => 'قيد الانتظار'];

        $rows = [];
        foreach ($this->contracts as $index => $contract) {
            $paid = (float) ($contract->paid_total ?? 0);
            $remaining = max(((float) $contract->total_amount - $paid), 0);

            $rows[] = [
                $index + 1,
                $contract->id,
                $contract->student->user->name ?? ('Student #' . $contract->student_id),
                $contract->academic_year,
                $planLabels[$contract->plan_type] ?? $contract->plan_type,
                $statusLabels[$contract->status] ?? $contract->status,
                round((float) $contract->total_amount, 2),
                round($paid, 2),
                round($remaining, 2),
                optional($contract->created_at)->format('Y-m-d'),
            ];
        }

        // صف المجموع
        $rows[] = [
            '',
            '',
            '',
            '',
            '',
            'المجموع',
            round((float) $this->totals['total_amount'], 2),
            round((float) $this->totals['paid_total'], 2),
            round((float) $this->totals['remaining'], 2),
            '',
        ];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A1:J1')->getFont()->setBold(true);
                $sheet->getStyle('A' . $lastRow . ':J' . $lastRow)->getFont()->setBold(true);

                foreach (range('A', 'J') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
