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
    /**
     * Վերադարձնում է վճարումների ըստ ամիսների համառոտ աղյուսակը (DataTables)։
     * Դպրոցների տրամաբանություն.
     *  - super-admin | super-accountant՝
     *      school_id = '' կամ null => ԲՈԼՈՐ դպրոցները
     *      school_id = {id}        => միայն ընտրված դպրոցը
     *  - սովորական դերեր՝
     *      միշտ սահմանափակում ենք ըստ օգտագործողի Auth::user()->school_id-ի
     */

    public function getPaymentData(Request $request): array{
        $isSuper = Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant');

        $schoolId = Auth::user()->school_id;
        if ($isSuper) {
            $schoolId = $request->input('school_id');
            if ($schoolId === '') { $schoolId = null; } 
        }

        $now       = now();
        $yearStart = $request->filled('year')
            ? (int)$request->input('year')
            : ($now->month >= 9 ? $now->year : $now->year - 1);

        $from = Carbon::create($yearStart, 9, 1)->startOfDay();
        $to   = Carbon::create($yearStart + 1, 5, 31)->endOfDay();

        $rangeFrom = $request->input('range_from'); 
        $rangeTo   = $request->input('range_to');   
        if ($rangeFrom && $rangeTo) {
            try {
                $rf = Carbon::createFromFormat('Y-m-d', $rangeFrom)->startOfDay();
                $rt = Carbon::createFromFormat('Y-m-d', $rangeTo)->endOfDay();
                if ($rf->gt($rt)) { [$rf, $rt] = [$rt, $rf]; }
                $from = $rf;
                $to   = $rt;
            } catch (\Throwable $e) {
            
            }
        }

        $periodLabel = ($from->month === 9 && $from->day === 1 && $to->month === 5 && $to->day === 31 && $to->year === $from->year + 1)
            ? sprintf('%d-%d', $from->year, $to->year)
            : 'custom';

        $groupId = $request->input('group_id');
        $method  = $request->input('method');
        $status  = $request->input('status');

        $search  = trim((string) $request->input('search.value', ''));
        $draw    = (int)$request->input('draw', 1);
        $start   = (int)$request->input('start', 0);
        $length  = (int)$request->input('length', 10);

        $base = Payment::query()
            ->join('students as s', 's.id', '=', 'payments.student_id')
            ->leftJoin('groups as g', 'g.id', '=', 'payments.group_id')
            ->leftJoin('school_names as sn', 'sn.id', '=', 'payments.school_id')
            ->whereBetween('payments.paid_at', [$from, $to]);

        if (!$isSuper) {
            $base->where('payments.school_id', Auth::user()->school_id);
        } elseif (!empty($schoolId)) {
            $base->where('payments.school_id', $schoolId);
        }

        if (!empty($groupId)) $base->where('payments.group_id', $groupId);
        if (!empty($method))  $base->where('payments.method',   $method);
        if (!empty($status))  $base->where('payments.status',   $status);

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

        // ====== Search ======
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
                ->orWhereRaw("LOWER(CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.first_name,''),' ',COALESCE(s.father_name,''))) LIKE ? ESCAPE '{$esc}'", [$like])
                ->orWhereRaw("LOWER(payments.comment) LIKE ? ESCAPE '{$esc}'", [$like])
                ->orWhereRaw("LOWER(g.name)           LIKE ? ESCAPE '{$esc}'", [$like])
                ->orWhereRaw("LOWER(sn.name)          LIKE ? ESCAPE '{$esc}'", [$like])
                ->orWhereRaw("LOWER(CAST(payments.amount AS CHAR)) LIKE ? ESCAPE '{$esc}'", [$like]);
            });
        }

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
                MAX(COALESCE(payments.school_id, s.school_id)) as school_id,
                MAX(CASE WHEN s.deleted_at IS NULL THEN 0 ELSE 1 END) as is_deleted,
                {$monthSums},
                SUM(payments.amount) as total,
                CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.first_name,''),' ',COALESCE(s.father_name,'')) as full_name
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
                $months[$key] = $val;
                $total += $val;
            }
            return array_merge([
                'id'         => (int)$r->student_id,
                'full_name'  => (string)$r->full_name,
                'school_id'  => (int)($r->school_id ?? 0),
                'is_deleted' => (int)$r->is_deleted === 1,
            ], $months, ['total'=>$total]);
        })->values()->toArray();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
            'summary'         => $summary,
            'meta'            => [
                'period' => [
                    'label' => $periodLabel,
                    'from'  => $from->toDateString(),
                    'to'    => $to->toDateString(),
                ],
                'group_id'  => $groupId,
                'method'    => $method,
                'status'    => $status,
                'school_id' => $schoolId,
            ],
        ];
    }


    /**
     * Վճարումների գլխավոր էջի ֆիլտրերը։
     * super + դատարկ school_id => կառուցում ենք ըստ բոլոր դպրոցների։
     * հակառակ դեպքում՝ ըստ կոնկրետ դպրոցի կամ ըստ ընթացիկ օգտագործողի դպրոցի։
     */
    public function getFilterOptions(Request $request): array
    {
        $isSuper = Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant');

        $schoolId = Auth::user()->school_id;
        if ($isSuper) {
            $schoolId = $request->input('school_id');
            if ($schoolId === '') { $schoolId = null; } // '' => Բոլորը
        }

        // Years
        $yearsQuery = Payment::query();
        if (!$isSuper) {
            $yearsQuery->where('school_id', $schoolId);
        } else if (!empty($schoolId)) {
            $yearsQuery->where('school_id', $schoolId);
        }
        
        $years = $yearsQuery->selectRaw('DISTINCT YEAR(paid_at) as y')
            ->orderByDesc('y')
            ->pluck('y')
            ->map(fn($y) => (int)$y)
            ->values();

        // Groups
        $groupsQuery = Group::query();
        if (!$isSuper) {
            $groupsQuery->where('school_id', $schoolId);
        } else if (!empty($schoolId)) {
            $groupsQuery->where('school_id', $schoolId);
        }
        $groups = $groupsQuery->orderBy('name')->get(['id','name']);

        // Method
        $methodQuery = Payment::query();
        if (!$isSuper) {
            $methodQuery->where('school_id', $schoolId);
        } else if (!empty($schoolId)) {
            $methodQuery->where('school_id', $schoolId);
        }
        $methods = $methodQuery->select('method')->distinct()->pluck('method')->values();

        // Statuses
        $statusesQuery = Payment::query();
        if (!$isSuper) {
            $statusesQuery->where('school_id', $schoolId);
        } else if (!empty($schoolId)) {
            $statusesQuery->where('school_id', $schoolId);
        }
        $statuses = $statusesQuery->select('status')->distinct()->pluck('status')->values();

        return [
            'years'    => $years,    // [2025, 2024, ...]
            'groups'   => $groups,   // [{id,name}]
            'methods'   => $methods,
            'statuses' => $statuses, // ['paid','pending',...]
        ];
    }

    /**
     * «Վճարում ավելացնել» մոդալի համար խմբեր։
     * super + դատարկ school_id => բոլոր դպրոցներով (այսինքն՝ չենք սահմանափակում)։
     * super + կոնկրետ դպրոց => միայն այդ դպրոցը։
     * ոչ-super => միայն օգտագործողի դպրոցը։
     */
    public function getGroupsData($request)
    {
        $isSuper = Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant');

        $schoolId = Auth::user()->school_id;
        if ($isSuper) {
            $schoolId = $request->input('school_id');
            if ($schoolId === '') { $schoolId = null; }
        }

        $q = Group::select('id','name');
        if (!$isSuper) {
            $q->where('school_id', $schoolId);
        } else if (!empty($schoolId)) {
            $q->where('school_id', $schoolId);
        }

        return $q->orderBy('name')->get();
    }

    /**
     * Ընտրված խմբի սովորողները (մոդալում)։
     */
    public function getStudentsData(int $groupId, $request)
    {
        $isSuper = Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant');

        $schoolId = Auth::user()->school_id;
        if ($isSuper) {
            $schoolId = $request->input('school_id');
            if ($schoolId === '') { $schoolId = null; }
        }

        $q = Student::select('id',
            DB::raw("CONCAT(first_name, ' ', last_name, ' ', father_name) as full_name")
        )->where('group_id', $groupId);

        if (!$isSuper) {
            $q->where('school_id', $schoolId);
        } else if (!empty($schoolId)) {
            $q->where('school_id', $schoolId);
        }

        return $q->orderBy('last_name')->get();
    }

    /**
     * Ուսանողի վճարումների կարճ պատմությունը (մոդալի համար)։
     */
    public function getStudentHistory(int $studentId, $year = null, $groupId = null, $status = null, $limit = 5, $request)
    {
        $isSuper = Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant');

        $schoolId = Auth::user()->school_id;
        if ($isSuper) {
            $schoolId = $request->input('school_id');
            if ($schoolId === '') { $schoolId = null; }
        }

        // Եթե դերը ոչ-super է — միշտ սեփական դպրոցը։ Super դերի դեպքում առանց school_id-ի — բոլոր դպրոցները։
        $q = Payment::query();
        if (!$isSuper) {
            $q->where('payments.school_id', $schoolId);
        } else if (!empty($schoolId)) {
            $q->where('payments.school_id', $schoolId);
        }

        $q->where('payments.student_id', $studentId);

        if ($year)    $q->whereYear('payments.paid_at', (int)$year);
        if ($groupId) $q->where('payments.group_id', $groupId);
        if ($status)  $q->where('payments.status', $status);

        if (!empty($limit)) {
            $q->limit($limit);
        }

        return $q->orderByDesc('payments.paid_at')
            ->get([
                'payments.id',
                'payments.paid_at',
                'payments.amount',
                'payments.status',
                'payments.comment',
            ])
            ->map(function($p){
                return [
                    'id'      => (int) $p->id,
                    'paid_at' => Carbon::parse($p->paid_at)->toDateString(),
                    'amount'  => (int)$p->amount,
                    'status'  => (string)$p->status,
                    'comment' => (string)($p->comment ?? ''),
                ];
            })
            ->toArray();
    }

    /**
     * Կոնկրետ ուսանողի էջի ֆիլտրերը։
     */
    public function getStudentFilterOptions(int $studentId, $request): array
    {
        $isSuper = Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant');

        $schoolId = Auth::user()->school_id;
        if ($isSuper) {
            $schoolId = $request->input('school_id');
            if ($schoolId === '') { $schoolId = null; }
        }

        $yearsQuery = Payment::query()->where('student_id', $studentId);
        $methodsQuery = Payment::query()->where('student_id', $studentId);
        $statusesQuery = Payment::query()->where('student_id', $studentId);
        $groupsQuery = Payment::query()
            ->where('payments.student_id', $studentId)
            ->whereNotNull('payments.group_id')
            ->join('groups as g', 'g.id', '=', 'payments.group_id');

        if (!$isSuper) {
            $yearsQuery->where('school_id', $schoolId);
            $methodsQuery->where('school_id', $schoolId);
            $statusesQuery->where('school_id', $schoolId);
            $groupsQuery->where('payments.school_id', $schoolId);
        } else if (!empty($schoolId)) {
            $yearsQuery->where('school_id', $schoolId);
            $methodsQuery->where('school_id', $schoolId);
            $statusesQuery->where('school_id', $schoolId);
            $groupsQuery->where('payments.school_id', $schoolId);
        }

        $years = $yearsQuery->selectRaw('DISTINCT YEAR(paid_at) as y')
            ->orderByDesc('y')
            ->pluck('y')
            ->map(fn($y)=>(int)$y)
            ->values();

        $methods = $methodsQuery->select('method')->distinct()->pluck('method')->values();
        $statuses = $statusesQuery->select('status')->distinct()->pluck('status')->values();

        $groups = $groupsQuery->select('g.id', 'g.name')->distinct()->orderBy('g.name')->get();

        return [
            'years'    => $years,
            'methods'  => $methods,
            'statuses' => $statuses,
            'groups'   => $groups,
        ];
    }

    /**
     * Կոնկրետ ուսանողի վճարումների աղյուսակը (DataTables)։
     */
    public function getStudentPaymentsTable(Request $request, int $studentId): array
    {
        $isSuper = Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant');

        $schoolId = Auth::user()->school_id;
        if ($isSuper) {
            $schoolId = $request->query('school_id');
            if ($schoolId === '') { $schoolId = null; }
        }

        $year   = (int)($request->input('year') ?: now()->year);
        $method = $request->input('method');
        $status = $request->input('status');

        $draw   = (int)$request->input('draw', 1);
        $start  = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 10);
        $search = trim((string)$request->input('search.value', ''));

        $base = Payment::query()->where('student_id', $studentId);

        if (!$isSuper) {
            $base->where('school_id', $schoolId);
        } else if (!empty($schoolId)) {
            $base->where('school_id', $schoolId);
        }

        if (!empty($year))   $base->whereYear('paid_at', $year);
        if (!empty($method)) $base->where('method', $method);
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
            'meta'            => ['year'=>$year, 'method' =>$method, 'status'=>$status, 'student_id'=>$studentId],
        ];
    }
}
