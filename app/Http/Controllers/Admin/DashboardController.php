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

        if ($isSuper) {
            $schoolId = $request->query('school_id') ?: null; 
        }

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

        $paymentsToday = Payment::query()
            ->when(true, $scopeSchool)
            ->whereDate('paid_at', $today)
            ->sum('amount');

        $paymentsMonth = Payment::query()
            ->when(true, $scopeSchool)
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->sum('amount');

        $pendingCount = Payment::query()
            ->when(true, $scopeSchool)
            ->where('status', 'pending')
            ->count();

        $activeStudents = Student::query()
            ->when(true, $scopeSchool)
            ->whereNull('deleted_at')
            ->count();

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
