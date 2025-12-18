<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Throwable;


class StudentService
{
   public function getSudentData(Request $request){
    $draw = $request->input('draw');
    $start = $request->input('start');
    $length = $request->input('length');
    $search = $request->input('search.value');

    $schoolId = Auth::user()->school_id;
    $query = Student::with('school');

    $user    = Auth::user();
    $schoolId = $request->input('school_id');
    $groupId  = $request->input('group_id');

    if ($user->hasRole(['super-admin', 'super-accountant', 'school-accountant'])) {

        if (!empty($schoolId)) {
            $query->where('school_id', $schoolId);

            if (!empty($groupId)) {
                $query->where('group_id', $groupId);
            }
        }

    } elseif ($user->hasRole('school-admin')) {

        $query->where('school_id', $user->school_id);

        if (!empty($groupId)) {
            $query->where('group_id', $groupId);
        }

    } else {

        $query->where('school_id', $user->school_id);
    }


    $recordsTotal = $query->count();

    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('father_name', 'like', "%{$search}%")
              ->orWhere('soc_number', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhereHas('school', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%"); 
              });
        });
    }

    $recordsFiltered = $query->count();

    $orderColumnIndex = $request->input('order.0.column');
    $orderColumnName = $request->input("columns.$orderColumnIndex.data");
    $orderDirection = $request->input('order.0.dir');

    if ($orderColumnName && $orderDirection) {
        if ($orderColumnName === 'school_name') {
            $query->leftJoin('school_names', 'staff.school_id', '=', 'school_names.id')
                  ->orderBy('school_names.name', $orderDirection)
                  ->select('students.*'); 
        } 
        elseif ($orderColumnName === 'full_name') {
            $query->select('students.*')
                ->orderByRaw("LOWER(CONCAT(COALESCE(students.first_name,''), ' ',COALESCE(students.last_name,''),  ' ',COALESCE(students.father_name,''))) 
                    {$orderDirection}");
        }
        else {
            $query->orderBy($orderColumnName, $orderDirection);
        }
    }

    $data = $query->skip($start)->take($length)->orderBy('id', 'DESC')->get();

    $data->transform(function ($item, $schoolId) {

        $viewHistory = ''; 

        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant') || Auth::user()->hasRole('school-accountant')) {
         $viewHistory = '<button class="btn btn-sm btn-light view-history" data-id="'.$item->id.'" data-school-id="'.$item->school_id.'" title="Պատմություն">&#8942;</button>'; 
        }
        $item->full_name = $item->first_name . ' ' . $item->last_name . ' ' . $item->father_name;
        $item->school_name = $item->school->name ?? '';
        $item->action = '
            <button class="btn btn-info btn-edit-student" data-id="'.$item->id.'" title="Խմբագրել"><i class="fas fa-edit"></i></button>
            <button class="btn btn-danger btn-delete-student" data-id="'.$item->id.'" title="Հեռացնել"><i class="fas fa-trash-alt"></i></button>
            '.$viewHistory.'
        ';
        return $item;
    });

    return [
        'draw' => intval($draw),
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data->values()->toArray()
    ];
}

}
