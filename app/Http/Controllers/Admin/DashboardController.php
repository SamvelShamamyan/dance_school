<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $isSuper  = Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant');
        $schoolId = Auth::user()->school_id;

        // super-* может смотреть либо все школы (null), либо конкретную school_id из query
        if ($isSuper) {
            $schoolId = $request->query('school_id') ?: null; // null = все школы
        }

        // helper для фильтра по школе
        $scopeSchool = function ($q) use ($isSuper, $schoolId) {
            if (!$isSuper) {
                $q->where('school_id', Auth::user()->school_id);
            } elseif (!empty($schoolId)) {
                $q->where('school_id', $schoolId);
            }
            return $q;
        };

        $today = Carbon::today();
        $year  = $today->year;
        $month = $today->month;

        // 1) Сумма платежей сегодня
        $paymentsToday = Payment::query()
            ->when(true, $scopeSchool)
            ->whereDate('paid_at', $today)
            ->sum('amount');

        // 2) Сумма платежей за текущий месяц
        $paymentsMonth = Payment::query()
            ->when(true, $scopeSchool)
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->sum('amount');

        // 3) Кол-во платежей в статусе pending
        $pendingCount = Payment::query()
            ->when(true, $scopeSchool)
            ->where('status', 'pending')
            ->count();

        // 4) Активные (не удалённые) учащиеся
        $activeStudents = Student::query()
            ->when(true, $scopeSchool)
            ->whereNull('deleted_at')
            ->count();

        // 5) Общая сумма долгов (если поле есть)
        $totalDebts = Student::query()
            ->when(true, $scopeSchool)
            ->sum('student_debts');

        return view('admin.dashboard', [
            'paymentsToday'  => (int)$paymentsToday,
            'paymentsMonth'  => (int)$paymentsMonth,
            'pendingCount'   => (int)$pendingCount,
            'activeStudents' => (int)$activeStudents,
            'totalDebts'     => (float)$totalDebts,
            'isSuper'        => $isSuper,
            'schoolId'       => $schoolId,
        ]);
    }
}
