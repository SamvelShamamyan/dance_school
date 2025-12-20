<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;

class StudentService
{
    public function getSudentData(Request $request)
    {
        $draw   = (int) $request->input('draw');
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value');

        $user = Auth::user();

        $schoolId = $request->input('school_id'); 
        $groupId  = $request->input('group_id');

        $from = $request->input('range_from');
        $to   = $request->input('range_to');

        // if (!empty($from) && !empty($to)) {
        //     $fromDt = Carbon::createFromFormat('m-d', $from)->startOfDay();
        //     $toDt   = Carbon::createFromFormat('m-d', $to)->endOfDay();
        // } else {
        //     $fromDt = now()->startOfMonth()->startOfDay();
        //     $toDt   = now()->endOfMonth()->endOfDay();
        // }

        $query = Student::query()->with('school');

        if ($user->hasRole(['super-admin', 'super-accountant', 'school-accountant'])) {

            if (!empty($schoolId)) {
                $query->where('students.school_id', $schoolId);
            }

            if (!empty($groupId)) {
                $query->where('students.group_id', $groupId);
            }

        } elseif ($user->hasRole('school-admin')) {

            $query->where('students.school_id', $user->school_id);

            if (!empty($groupId)) {
                $query->where('students.group_id', $groupId);
            }

        } else {
            $query->where('students.school_id', $user->school_id);
        }

        if (!empty($from) && !empty($to)) {
            $query->whereRaw("DATE_FORMAT(students.birth_date, '%m-%d') BETWEEN ? AND ?", [$from, $to]);
        }

        $recordsTotal = (clone $query)->count();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('students.first_name', 'like', "%{$search}%")
                    ->orWhere('students.last_name', 'like', "%{$search}%")
                    ->orWhere('students.father_name', 'like', "%{$search}%")
                    ->orWhere('students.soc_number', 'like', "%{$search}%")
                    ->orWhere('students.email', 'like', "%{$search}%")
                    ->orWhereHas('school', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $recordsFiltered = (clone $query)->count();

        $orderColumnIndex = $request->input('order.0.column');
        $orderColumnName  = $request->input("columns.$orderColumnIndex.data");
        $orderDirection   = $request->input('order.0.dir', 'desc');

        if ($orderColumnName && $orderDirection) {
            if ($orderColumnName === 'school_name') {
                $query->leftJoin('school_names', 'students.school_id', '=', 'school_names.id')
                    ->orderBy('school_names.name', $orderDirection)
                    ->select('students.*');
            } elseif ($orderColumnName === 'full_name') {
                $query->orderByRaw(
                    "LOWER(CONCAT(COALESCE(students.first_name,''),' ',COALESCE(students.last_name,''),' ',COALESCE(students.father_name,''))) {$orderDirection}"
                );
            } else {
                $allowed = [
                    'id', 'email', 'birth_date', 'created_at',
                    'student_expected_payments', 'student_prepayment', 'student_debts',
                ];
                if (in_array($orderColumnName, $allowed, true)) {
                    $query->orderBy("students.$orderColumnName", $orderDirection);
                } else {
                    $query->orderBy('students.id', 'desc');
                }
            }
        } else {
            $query->orderBy('students.id', 'desc');
        }

        $data = $query->skip($start)->take($length)->get();

        $data->transform(function ($item) use ($user) {
            $viewHistory = '';

            if ($user->hasRole(['super-admin', 'super-accountant', 'school-accountant'])) {
                $viewHistory = '<button class="btn btn-sm btn-light view-history" data-id="'.$item->id.'" data-school-id="'.$item->school_id.'" title="Պատմություն">&#8942;</button>';
            }

            $item->full_name   = trim($item->first_name.' '.$item->last_name.' '.$item->father_name);
            $item->school_name = $item->school->name ?? '';

            $item->action = '
                <button class="btn btn-info btn-edit-student" data-id="'.$item->id.'" title="Խմբագրել"><i class="fas fa-edit"></i></button>
                <button class="btn btn-danger btn-delete-student" data-id="'.$item->id.'" title="Հեռացնել"><i class="fas fa-trash-alt"></i></button>
                '.$viewHistory.'
            ';

            $item->created_date = optional($item->created_at)->toDateString();

            return $item;
        });

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data->values()->toArray(),
        ];
    }
}
