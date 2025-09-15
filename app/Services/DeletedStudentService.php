<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;


class DeletedStudentService
{
   public function getSudentData(Request $request){
    $draw = $request->input('draw');
    $start = $request->input('start');
    $length = $request->input('length');
    $search = $request->input('search.value');


    $schoolId = Auth::user()->school_id;

    // $query = Student::with('school');
    $query = Student::onlyTrashed()->with('school');

    if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant') || Auth::user()->hasRole('school-admin') || Auth::user()->hasRole('school-accountant')) {
        $schoolId = $request->input('school_id');
        if ($schoolId !== null && $schoolId !== '') {
            $query->where('school_id', $schoolId);
        } else {
            $query->whereNotNull('school_id'); 
        }
    } else {
        $query->where('school_id', $schoolId);
    }
    
    // $query = Student::with('school')->whereNotNull('school_id')->where('school_id', Auth::user()->school_id);


    $recordsTotal = $query->count();

    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('father_name', 'like', "%{$search}%")
              ->orWhere('soc_number', 'like', "%{$search}%")
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
        } else {
            $query->orderBy($orderColumnName, $orderDirection);
        }
    }

    $data = $query->skip($start)->take($length)->get();

    $data->transform(function ($item) {
        $item->full_name = $item->last_name . ' ' . $item->first_name . ' ' . $item->father_name;
        $item->school_name = $item->school->name ?? '';
        $item->action = '
            <button class="btn btn-info btn-edit-student" data-id="'.$item->id.'" title="Խմբագրել"><i class="fas fa-edit"></i></button>
            <button class="btn btn-danger btn-delete-student" data-id="'.$item->id.'" title="Հեռացնել"><i class="fas fa-trash-alt"></i></button>
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
