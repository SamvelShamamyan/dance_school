<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Student;

class RolloverStudentBalances extends Command
{
    protected $signature = 'balances:rollover {--month=}';
    protected $description = 'Добавляет норму месяца в долг и покрывает её из депозита, обнуляет транзакции на новый месяц';

    public function handle()
    {
        if ($m = $this->option('month')) {
            [$y, $mm] = explode('-', $m);
            $y  = (int) $y;
            $mm = (int) $mm;
        } else {
            $y  = (int) now()->subMonthNoOverflow()->format('Y');
            $mm = (int) now()->subMonthNoOverflow()->format('m');
        }

        $updated = 0;

        Student::query()
            ->select(['id','student_expected_payments','student_prepayment','student_debts'])
            ->orderBy('id')
            ->chunkById(500, function ($chunk) use (&$updated) {

                DB::transaction(function () use ($chunk, &$updated) {
                    foreach ($chunk as $row) {
                        $student = Student::lockForUpdate()->find($row->id);
                        if (!$student) continue;

                        $E = max(0.0, (float) ($student->student_expected_payments ?? 0.0)); 
                        $R = max(0.0, (float) ($student->student_prepayment ?? 0.0));      
                        $T = max(0.0, (float) ($student->student_debts ?? 0.0));          

                        $T_plus_E = $T + $E;

                        $cover   = min($R, $T_plus_E);
                        $T_new   = $T_plus_E - $cover;   
                        $R_new   = $R - $cover;         

                        Student::whereKey($student->id)->update([
                            'student_transactions' => 0.0,
                            'student_prepayment'   => $R_new,
                            'student_debts'        => $T_new,
                        ]);

                        $updated++;
                    }
                });
            });

        $this->info("Готово. Обновлено студентов: {$updated} (ролловер {$y}-{$mm})");
        return self::SUCCESS;
    }
}
