<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StudentMonthlyDues;

class StudentsDebtHistoryService
{
    public function getDebtData(Request $request): array
    {
        $isSuper = Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant');

        $schoolId = Auth::user()->school_id;
        if ($isSuper) {
            $schoolId = $request->input('school_id');
            if ($schoolId === '') { $schoolId = null; } 
        }

        $year    = (int)($request->input('year') ?: now()->year);
        $groupId = $request->input('group_id');
        // $status  = $request->input('status'); 

        // DataTables
        $search  = trim((string) $request->input('search.value', ''));
        $draw    = (int)$request->input('draw', 1);
        $start   = (int)$request->input('start', 0);
        $length  = (int)$request->input('length', 10);

        // $students = Student::query()
        //     ->leftJoin('groups as g', 'g.id', '=', 'students.group_id')
        //     ->leftJoin('school_names as sn', 'sn.id', '=', 'students.school_id');

        $students = Student::withTrashed()
            ->leftJoin('groups as g', 'g.id', '=', 'students.group_id')
            ->leftJoin('school_names as sn', 'sn.id', '=', 'students.school_id');


        if (!$isSuper) {
            $students->where('students.school_id', Auth::user()->school_id);
        } elseif (!empty($schoolId)) {
            $students->where('students.school_id', $schoolId);
        }
        if (!empty($groupId)) $students->where('students.group_id', $groupId);

        if ($search !== '') {
            $needle = mb_strtolower($search,'UTF-8');
            $needle = str_replace(['\\','%','_'], ['\\\\','\%','\_'], $needle);
            $like   = '%'.$needle.'%'; $esc='\\\\';
            $students->where(function($q) use ($like,$esc){
                $q->whereRaw("LOWER(students.first_name)  LIKE ? ESCAPE '{$esc}'", [$like])
                  ->orWhereRaw("LOWER(students.last_name)   LIKE ? ESCAPE '{$esc}'", [$like])
                  ->orWhereRaw("LOWER(students.father_name) LIKE ? ESCAPE '{$esc}'", [$like])
                  ->orWhereRaw("LOWER(CONCAT(COALESCE(students.last_name,''),' ',COALESCE(students.first_name,''),' ',COALESCE(students.father_name,''))) LIKE ? ESCAPE '{$esc}'", [$like]);
            });
        }

        $dues = StudentMonthlyDues::query()
            ->selectRaw('student_id, month, SUM(amount_due) as due_sum')
            ->where('year', $year);

        if (!$isSuper) {
            $dues->where('school_id', Auth::user()->school_id);
        } elseif (!empty($schoolId)) {
            $dues->where('school_id', $schoolId);
        }
        if (!empty($groupId)) $dues->where('group_id', $groupId);

        $dues->groupBy('student_id', 'month');

        $pays = Payment::query()
            ->selectRaw('student_id, MONTH(paid_at) as month, SUM(amount) as paid_sum')
            ->whereYear('paid_at', $year);

        if (!$isSuper) {
            $pays->where('school_id', Auth::user()->school_id);
        } elseif (!empty($schoolId)) {
            $pays->where('school_id', $schoolId);
        }
        if (!empty($groupId)) $pays->where('group_id', $groupId);
        // if (!empty($status))  $pays->where('status', $status); 

        $pays->groupBy('student_id', 'month');

        $months = DB::table(DB::raw('
            (SELECT 1 AS m UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
             UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
             UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) as mo
        '));

        // $studentsBase = $students->select([
        //     'students.id as student_id',
        //     DB::raw("CONCAT(COALESCE(students.last_name,''),' ',COALESCE(students.first_name,''),' ',COALESCE(students.father_name,'')) as full_name"),
        //     'students.school_id'
        // ]);

        $studentsBase = $students->select([
            'students.id as student_id',
            DB::raw("CONCAT(COALESCE(students.last_name,''),' ',COALESCE(students.first_name,''),' ',COALESCE(students.father_name,'')) as full_name"),
            'students.school_id',
            'students.deleted_at',
        ]);


        $outer = DB::query()
            ->fromSub($studentsBase, 's')
            ->crossJoinSub($months, 'mo') 
            ->leftJoinSub($dues, 'd', function ($join) {
                $join->on('d.student_id', '=', 's.student_id')
                     ->on('d.month', '=', 'mo.m');
            })
            ->leftJoinSub($pays, 'p', function ($join) {
                $join->on('p.student_id', '=', 's.student_id')
                     ->on('p.month', '=', 'mo.m');
            });

        $selects = ['s.student_id', 's.full_name', 's.school_id'];

        for ($m = 1; $m <= 12; $m++) {
            $s = str_pad($m, 2, '0', STR_PAD_LEFT);

            $selects[] = DB::raw("
                SUM(CASE WHEN mo.m={$m} THEN IFNULL(d.due_sum,0) ELSE 0 END) as due_m{$s}
            ");
            $selects[] = DB::raw("
                SUM(CASE WHEN mo.m={$m} THEN IFNULL(p.paid_sum,0) ELSE 0 END) as paid_m{$s}
            ");
            $selects[] = DB::raw("
                GREATEST(
                    SUM(CASE WHEN mo.m={$m} THEN IFNULL(d.due_sum,0) ELSE 0 END) -
                    SUM(CASE WHEN mo.m={$m} THEN IFNULL(p.paid_sum,0) ELSE 0 END), 0
                ) as rem_m{$s}
            ");
        }

        $selects[] = DB::raw("
            SUM(IFNULL(d.due_sum,0)) as total_due
        ");
        $selects[] = DB::raw("
            SUM(IFNULL(p.paid_sum,0)) as total_paid
        ");
        $selects[] = DB::raw("
            GREATEST(SUM(IFNULL(d.due_sum,0)) - SUM(IFNULL(p.paid_sum,0)), 0) as total_rem
        ");

        $selects[] = DB::raw("MAX(CASE WHEN s.deleted_at IS NULL THEN 0 ELSE 1 END) as is_deleted");


        $rows = $outer
            ->select($selects)
            ->groupBy('s.student_id', 's.full_name', 's.school_id')
            ->orderBy('s.full_name')
            ->get();

        $recordsFiltered = $rows->count();
        $paged = $rows->slice($start, $length)->values();

        $summary = array_fill(1, 12, ['due' => 0.0, 'paid' => 0.0, 'rem' => 0.0]);

        $data = $paged->map(function ($r) use (&$summary) {
            $row = [
                'id'        => (int) $r->student_id,
                'full_name' => (string) $r->full_name,
                'school_id' => (int) $r->school_id,
                'deleted'   => (bool) ($r->is_deleted ?? 0),
            ];

            $total_due  = 0.0;
            $total_paid = 0.0;
            $total_rem  = 0.0;

            for ($m = 1; $m <= 12; $m++) {
                $dueKey  = 'due_m'  . str_pad($m, 2, '0', STR_PAD_LEFT);
                $paidKey = 'paid_m' . str_pad($m, 2, '0', STR_PAD_LEFT);
                $remKey  = 'rem_m'  . str_pad($m, 2, '0', STR_PAD_LEFT);

                $due  = (float)($r->$dueKey  ?? 0);
                $paid = (float)($r->$paidKey ?? 0);
                $rem  = (float)($r->$remKey  ?? 0);

                $row[$dueKey]  = $due;
                $row[$paidKey] = $paid;
                $row[$remKey]  = $rem;

                $total_due  += $due;
                $total_paid += $paid;
                $total_rem  += $rem;

                $summary[$m]['due']  += $due;
                $summary[$m]['paid'] += $paid;
                $summary[$m]['rem']  += $rem;
            }

            $row['total_due']  = (float) $total_due;
            $row['total_paid'] = (float) $total_paid;
            $row['total_rem']  = (float) $total_rem;

            return $row;
        })->toArray();

        $summaryFlat = [
            'due'  => [], 'paid' => [], 'rem' => []
        ];
        for ($m = 1; $m <= 12; $m++) {
            $summaryFlat['due'][]  = (float) $summary[$m]['due'];
            $summaryFlat['paid'][] = (float) $summary[$m]['paid'];
            $summaryFlat['rem'][]  = (float) $summary[$m]['rem'];
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsFiltered,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
            'summary'         => $summaryFlat, // {due: [..12], paid:[..12], rem:[..12]}
            'meta'            => [
                'year'      => $year,
                'group_id'  => $groupId,
                // 'status'    => $status,
                'school_id' => $schoolId,
            ],
        ];
    }
}
