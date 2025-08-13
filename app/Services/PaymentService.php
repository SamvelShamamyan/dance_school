<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class PaymentService
{
    public function getPaymentData(Request $request): array
    {
        $schoolId = Auth::user()->school_id;

        $year    = (int)($request->input('year') ?: now()->year);
        $groupId = $request->input('group_id');
        $status  = $request->input('status');

        // DataTables search
        $search  = (string) $request->input('search.value', '');
        $search  = trim($search);

        $draw    = (int)$request->input('draw', 1);
        $start   = (int)$request->input('start', 0);
        $length  = (int)$request->input('length', 10);

        // ========= DB (Eloquent) SEARCH |NO| ========= HA eli yanm te
        $base = Payment::query()
            ->join('students as s', 's.id', '=', 'payments.student_id')
            ->leftJoin('groups as g', 'g.id', '=', 'payments.group_id')
            ->leftJoin('school_names as sn', 'sn.id', '=', 'payments.school_id')
            ->whereYear('payments.paid_at', $year);

        if (!empty($schoolId)) $base->where('payments.school_id', $schoolId);
        if (!empty($groupId))  $base->where('payments.group_id', $groupId);
        if (!empty($status))   $base->where('payments.status',   $status);

        // ========= MOUNTS (NO SEARCH) ========= PFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
        $summaryRow = (clone $base)->selectRaw("
            SUM(CASE WHEN MONTH(payments.paid_at)=1  THEN payments.amount ELSE 0 END) as m01,
            SUM(CASE WHEN MONTH(payments.paid_at)=2  THEN payments.amount ELSE 0 END) as m02,
            SUM(CASE WHEN MONTH(payments.paid_at)=3  THEN payments.amount ELSE 0 END) as m03,
            SUM(CASE WHEN MONTH(payments.paid_at)=4  THEN payments.amount ELSE 0 END) as m04,
            SUM(CASE WHEN MONTH(payments.paid_at)=5  THEN payments.amount ELSE 0 END) as m05,
            SUM(CASE WHEN MONTH(payments.paid_at)=6  THEN payments.amount ELSE 0 END) as m06,
            SUM(CASE WHEN MONTH(payments.paid_at)=7  THEN payments.amount ELSE 0 END) as m07,
            SUM(CASE WHEN MONTH(payments.paid_at)=8  THEN payments.amount ELSE 0 END) as m08,
            SUM(CASE WHEN MONTH(payments.paid_at)=9  THEN payments.amount ELSE 0 END) as m09,
            SUM(CASE WHEN MONTH(payments.paid_at)=10 THEN payments.amount ELSE 0 END) as m10,
            SUM(CASE WHEN MONTH(payments.paid_at)=11 THEN payments.amount ELSE 0 END) as m11,
            SUM(CASE WHEN MONTH(payments.paid_at)=12 THEN payments.amount ELSE 0 END) as m12
        ")->first();

        $summary = [
            (int)($summaryRow->m01 ?? 0),(int)($summaryRow->m02 ?? 0),(int)($summaryRow->m03 ?? 0),
            (int)($summaryRow->m04 ?? 0),(int)($summaryRow->m05 ?? 0),(int)($summaryRow->m06 ?? 0),
            (int)($summaryRow->m07 ?? 0),(int)($summaryRow->m08 ?? 0),(int)($summaryRow->m09 ?? 0),
            (int)($summaryRow->m10 ?? 0),(int)($summaryRow->m11 ?? 0),(int)($summaryRow->m12 ?? 0),
        ];

        // ========= SEARCH (USE join Eloquent) ========= BOOOOOOM
        $baseSearch = (clone $base);
        if ($search !== '') {
            $needle = mb_strtolower($search, 'UTF-8');
            $needle = str_replace(['\\','%','_'], ['\\\\','\%','\_'], $needle);
            $like   = '%'.$needle.'%';
            $esc    = '\\\\';

            $baseSearch->where(function($q) use ($like, $esc) {
                $q->whereRaw("LOWER(s.first_name)  LIKE ? ESCAPE '{$esc}'", [$like])
                  ->orWhereRaw("LOWER(s.last_name)   LIKE ? ESCAPE '{$esc}'", [$like])
                  ->orWhereRaw("LOWER(s.father_name) LIKE ? ESCAPE '{$esc}'", [$like])
                  ->orWhereRaw("
                        LOWER(CONCAT(
                          COALESCE(s.last_name,''),' ',
                          COALESCE(s.first_name,''),' ',
                          COALESCE(s.father_name,'')
                        )) LIKE ? ESCAPE '{$esc}'
                  ", [$like])
                  ->orWhereRaw("LOWER(payments.comment) LIKE ? ESCAPE '{$esc}'", [$like])
                  ->orWhereRaw("LOWER(g.name)           LIKE ? ESCAPE '{$esc}'", [$like])
                  ->orWhereRaw("LOWER(sn.name)          LIKE ? ESCAPE '{$esc}'", [$like])
                  ->orWhereRaw("LOWER(CAST(payments.amount AS CHAR)) LIKE ? ESCAPE '{$esc}'", [$like]); 
            });
        }

        // ========= XXXXXXXX =========
        $monthSums = "
            SUM(CASE WHEN MONTH(payments.paid_at)=1  THEN payments.amount ELSE 0 END) as m01,
            SUM(CASE WHEN MONTH(payments.paid_at)=2  THEN payments.amount ELSE 0 END) as m02,
            SUM(CASE WHEN MONTH(payments.paid_at)=3  THEN payments.amount ELSE 0 END) as m03,
            SUM(CASE WHEN MONTH(payments.paid_at)=4  THEN payments.amount ELSE 0 END) as m04,
            SUM(CASE WHEN MONTH(payments.paid_at)=5  THEN payments.amount ELSE 0 END) as m05,
            SUM(CASE WHEN MONTH(payments.paid_at)=6  THEN payments.amount ELSE 0 END) as m06,
            SUM(CASE WHEN MONTH(payments.paid_at)=7  THEN payments.amount ELSE 0 END) as m07,
            SUM(CASE WHEN MONTH(payments.paid_at)=8  THEN payments.amount ELSE 0 END) as m08,
            SUM(CASE WHEN MONTH(payments.paid_at)=9  THEN payments.amount ELSE 0 END) as m09,
            SUM(CASE WHEN MONTH(payments.paid_at)=10 THEN payments.amount ELSE 0 END) as m10,
            SUM(CASE WHEN MONTH(payments.paid_at)=11 THEN payments.amount ELSE 0 END) as m11,
            SUM(CASE WHEN MONTH(payments.paid_at)=12 THEN payments.amount ELSE 0 END) as m12
        ";

        $groupedWithSearch = (clone $baseSearch)
            ->selectRaw("
                payments.student_id,
                {$monthSums},
                SUM(payments.amount) as total,
                CONCAT(
                    COALESCE(s.last_name,''),' ',
                    COALESCE(s.first_name,''),' ',
                    COALESCE(s.father_name,'')
                ) as full_name
            ")
            ->groupBy('payments.student_id','s.last_name','s.first_name','s.father_name');

        $groupedNoSearch = (clone $base)
            ->select('payments.student_id')
            ->groupBy('payments.student_id');

        $recordsTotal    = DB::query()->fromSub($groupedNoSearch, 't')->count();
        $recordsFiltered = DB::query()->fromSub($groupedWithSearch, 't')->count();

        $orderColumnIndex = $request->input('order.0.column');
        $orderColumnName  = $request->input("columns.$orderColumnIndex.data");
        $orderDir         = $request->input('order.0.dir', 'asc');
        $allowedOrder     = ['full_name','m01','m02','m03','m04','m05','m06','m07','m08','m09','m10','m11','m12','total'];

        $outer = DB::query()->fromSub($groupedWithSearch, 't');
        if (in_array($orderColumnName, $allowedOrder, true)) {
            $outer->orderBy($orderColumnName, $orderDir === 'desc' ? 'desc' : 'asc');
        } else {
            $outer->orderBy('full_name', 'asc');
        }

        $rows = $outer->offset($start)->limit($length)->get();

        $data = $rows->map(function($r){
            $months = []; $total = 0;
            for ($i=1; $i<=12; $i++) {
                $key = 'm'.str_pad($i,2,'0',STR_PAD_LEFT);
                $val = (int)($r->$key ?? 0);
                $months[$key] = $val; $total += $val;
            }
            return array_merge([
                'id'        => (int)$r->student_id,
                'full_name' => (string)$r->full_name,
            ], $months, ['total'=>$total]);
        })->values()->toArray();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
            'summary'         => $summary,
            'meta'            => [
                'year'=>$year, 'group_id'=>$groupId, 'status'=>$status
            ],
        ];
    }

    public function getFilterOptions(Request $request): array
    {
        $schoolId = optional(Auth::user())->school_id;

        $years = Payment::query()
            ->where('school_id', $schoolId)
            ->selectRaw('DISTINCT YEAR(paid_at) as y')
            ->orderByDesc('y')
            ->pluck('y')
            ->map(fn($y)=>(int)$y)
            ->values();

       
        $groups = Group::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get(['id','name']);

        $statuses = Payment::query()
            ->where('school_id', $schoolId)
            ->select('status')
            ->distinct()
            ->pluck('status')
            ->values();

        return [
            'years'    => $years,      // [2025, 2024, ...]
            'groups'   => $groups,     // [{id,name}]
            'statuses' => $statuses,   // ['paid','pending',...]
        ];
    }


    public function getGroupsData(){
        $groups = Group::select('id', 'name')->where('school_id', Auth::user()->school_id)->get();
        return $groups;
    }

    public function getStudentsData(int $groupId){
        $students = Student::select('id',
        DB::raw("CONCAT(first_name, ' ', last_name, ' ', father_name) as full_name")
        )->where('school_id', Auth::user()->school_id)->where('group_id', $groupId)->get();
        return $students;
    }


    public function getStudentHistory(int $studentId, $year = null, $groupId = null, $status = null, $limit = 5){
        
        $schoolId = Auth::user()->school_id;

        $q = Payment::query()
            ->where('payments.school_id', $schoolId)
            ->where('payments.student_id', $studentId);

        if ($year)    $q->whereYear('payments.paid_at', (int)$year);
        if ($groupId) $q->where('payments.group_id', $groupId);
        if ($status)  $q->where('payments.status', $status);


        if (!empty($limit)) {$q->limit($limit);}

        return $q->orderByDesc('payments.paid_at')
            ->get([
                'payments.id',
                'payments.paid_at',
                'payments.amount',
                'payments.method',
                'payments.status',
                'payments.comment',
            ])
            ->map(function($p){
                return [
                    'id'      => (int) $p->id,
                    'paid_at' => Carbon::parse($p->paid_at)->toDateString(), 
                    'amount'  => (int)$p->amount,
                    'method'  => (string)$p->method,
                    'status'  => (string)$p->status,
                    'comment' => (string)($p->comment ?? ''),
                ];
            })
            ->toArray();
    }


public function getStudentFilterOptions(int $studentId): array
{
    $schoolId = optional(Auth::user())->school_id;

    // годы, в которые у ЭТОГО ученика были платежи
    $years = Payment::query()
        ->where('school_id', $schoolId)
        ->where('student_id', $studentId)
        ->selectRaw('DISTINCT YEAR(paid_at) as y')
        ->orderByDesc('y')
        ->pluck('y')
        ->map(fn($y)=>(int)$y)
        ->values();

    // статусы, которые встречались у ЭТОГО ученика
    $statuses = Payment::query()
        ->where('school_id', $schoolId)
        ->where('student_id', $studentId)
        ->select('status')
        ->distinct()
        ->pluck('status')
        ->values();

    // (опционально) группы, в которых у ЭТОГО ученика есть платежи
    // если на странице ученика группы не нужны — можно удалить этот блок
    $groups = Payment::query()
        ->where('payments.school_id', $schoolId)
        ->where('payments.student_id', $studentId)
        ->whereNotNull('payments.group_id')
        ->join('groups as g', 'g.id', '=', 'payments.group_id')
        ->select('g.id', 'g.name')
        ->distinct()
        ->orderBy('g.name')
        ->get();

    return [
        'years'    => $years,     // [2025, 2024, ...] именно для этого ученика
        'statuses' => $statuses,  // ['paid','pending',...]
        'groups'   => $groups,    // [{id,name}] — если не используете, просто игнорируйте в JS
    ];
}

public function getStudentPaymentsTable(Request $request, int $studentId): array
{
    $schoolId = Auth::user()->school_id;

    $year   = (int)($request->input('year') ?: now()->year);
    $status = $request->input('status');

    $draw   = (int)$request->input('draw', 1);
    $start  = (int)$request->input('start', 0);
    $length = (int)$request->input('length', 10);
    $search = trim((string)$request->input('search.value', ''));

    $base = Payment::query()
        ->where('school_id', $schoolId)
        ->where('student_id', $studentId);

    if (!empty($year))   $base->whereYear('paid_at', $year);
    if (!empty($status)) $base->where('status', $status);

    $summaryRow = (clone $base)->selectRaw("
        SUM(CASE WHEN MONTH(paid_at)=1  THEN amount ELSE 0 END) as m01,
        SUM(CASE WHEN MONTH(paid_at)=2  THEN amount ELSE 0 END) as m02,
        SUM(CASE WHEN MONTH(paid_at)=3  THEN amount ELSE 0 END) as m03,
        SUM(CASE WHEN MONTH(paid_at)=4  THEN amount ELSE 0 END) as m04,
        SUM(CASE WHEN MONTH(paid_at)=5  THEN amount ELSE 0 END) as m05,
        SUM(CASE WHEN MONTH(paid_at)=6  THEN amount ELSE 0 END) as m06,
        SUM(CASE WHEN MONTH(paid_at)=7  THEN amount ELSE 0 END) as m07,
        SUM(CASE WHEN MONTH(paid_at)=8  THEN amount ELSE 0 END) as m08,
        SUM(CASE WHEN MONTH(paid_at)=9  THEN amount ELSE 0 END) as m09,
        SUM(CASE WHEN MONTH(paid_at)=10 THEN amount ELSE 0 END) as m10,
        SUM(CASE WHEN MONTH(paid_at)=11 THEN amount ELSE 0 END) as m11,
        SUM(CASE WHEN MONTH(paid_at)=12 THEN amount ELSE 0 END) as m12
    ")->first();

    $summary = [
        (int)($summaryRow->m01 ?? 0),(int)($summaryRow->m02 ?? 0),(int)($summaryRow->m03 ?? 0),
        (int)($summaryRow->m04 ?? 0),(int)($summaryRow->m05 ?? 0),(int)($summaryRow->m06 ?? 0),
        (int)($summaryRow->m07 ?? 0),(int)($summaryRow->m08 ?? 0),(int)($summaryRow->m09 ?? 0),
        (int)($summaryRow->m10 ?? 0),(int)($summaryRow->m11 ?? 0),(int)($summaryRow->m12 ?? 0),
    ];

    $q = (clone $base);
    if ($search !== '') {
        $needle = mb_strtolower($search, 'UTF-8');
        $needle = str_replace(['\\','%','_'], ['\\\\','\%','\_'], $needle);
        $like   = '%'.$needle.'%'; $esc='\\\\';
        $q->where(function($w) use ($like, $esc){
            $w->orWhereRaw("LOWER(method)  LIKE ? ESCAPE '{$esc}'", [$like])
              ->orWhereRaw("LOWER(status)  LIKE ? ESCAPE '{$esc}'", [$like])
              ->orWhereRaw("LOWER(comment) LIKE ? ESCAPE '{$esc}'", [$like])
              ->orWhereRaw("LOWER(CAST(amount AS CHAR)) LIKE ? ESCAPE '{$esc}'", [$like]);
        });
    }

    $orderIdx = $request->input('order.0.column');
    $orderCol = $request->input("columns.$orderIdx.data");
    $orderDir = $request->input('order.0.dir', 'asc');
    $allowed  = ['paid_at','amount','method','status','comment','id'];
    if (!in_array($orderCol, $allowed, true)) $orderCol = 'paid_at';
    $q->orderBy($orderCol, $orderDir === 'desc' ? 'desc' : 'asc');

    $recordsTotal    = (clone $base)->count();
    $recordsFiltered = (clone $q)->count();

    $rows = $q->offset($start)->limit($length)->get([
        'id','paid_at','amount','method','status','comment'
    ])->map(function($p){
        return [
            'id'      => (int)$p->id,
            'paid_at' => Carbon::parse($p->paid_at)->toDateString(),
            'amount'  => (int)$p->amount,
            'method'  => (string)$p->method,
            'status'  => (string)$p->status,
            'comment' => (string)($p->comment ?? ''),
        ];
    })->toArray();

    return [
        'draw'            => $draw,
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $rows,
        'summary'         => $summary,
        'meta'            => ['year'=>$year, 'status'=>$status, 'student_id'=>$studentId],
    ];
}



}
