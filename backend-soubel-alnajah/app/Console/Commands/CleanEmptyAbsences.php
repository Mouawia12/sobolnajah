<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AgendaScolaire\Absence;


class CleanEmptyAbsences extends Command
{
    protected $signature = 'absence:clean';
    protected $description = 'حذف سجلات الغياب التي جميع الساعات فيها = 1 (غياب كامل)';

    public function handle()
    {
        // نحذف أي سجل جميع ساعات اليوم فيه = 1
        $query = Absence::query();

        for ($i = 1; $i <= 9; $i++) {
            $query->where("hour_$i", 1);
        }

        $deleted = $query->delete();

        $this->info("تم حذف {$deleted} سجل غياب كامل.");
        return 0;
    }
}
