<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $schoolIds  = DB::table('school_names')->pluck('id')->toArray();
        $groupIds   = DB::table('groups')->pluck('id')->toArray();
        $studentIds = DB::table('students')->pluck('id')->toArray();
        $userIds    = DB::table('users')->pluck('id')->toArray();

        $methods = ['cash','card','online'];
        $statuses = ['paid','pending','failed','refunded'];

        $rows = [];
        for ($i = 0; $i < 30; $i++) {
            $paidAt = Carbon::create(2025, rand(1, 12), rand(1, 28), 12, 0, 0);

            $rows[] = [
                'school_id'  => $schoolIds ? $schoolIds[array_rand($schoolIds)] : null,
                'group_id'   => $groupIds ? $groupIds[array_rand($groupIds)] : null,
                'student_id' => $studentIds[array_rand($studentIds)],
                'created_by' => $userIds ? $userIds[array_rand($userIds)] : null,

                'amount'     => rand(40, 60) * 1000, 
                'currency'   => 'AMD',
                'paid_at'    => $paidAt,

                'method'     => $methods[array_rand($methods)],
                'status'     => $statuses[array_rand($statuses)],
                'comment'    => 'վճարում ' . $paidAt->translatedFormat('F'),

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('payments')->insert($rows);
    }
}
