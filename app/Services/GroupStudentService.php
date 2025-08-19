<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GroupStudentService
{

   public function getStudenetsList(Request $request, $groupId)
{
    $draw   = $request->input('draw');
    $start  = $request->input('start');
    $length = $request->input('length');
    $search = $request->input('search.value');

    $schoolId = Auth::user()->school_id;

    if (Auth::user()->hasRole('super-admin')) {
        $schoolId = $request->input('school_id');
    }

    
    $query = Student::with('school')
        ->select('students.*', 'groups.id as group_id')
        ->whereNotNull('students.school_id')
        ->join('groups', 'groups.id', '=', 'students.group_id')
        ->where('students.school_id', $schoolId)
        ->where('groups.id', $groupId); 


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

    $orderColumnIndex   = $request->input('order.0.column');
    $orderColumnName    = $request->input("columns.$orderColumnIndex.data");
    $orderDirection     = $request->input('order.0.dir');

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
            <button class="btn btn-danger btn-delete-group-student" data-id-student="'.$item->id.'" title="Հեռացնել աշակերտին տվյալ խմբից"><i class="fas fa-trash-alt"></i></button>
            <button class="btn btn-warning btn-change-group-student" id="studentGroupRepeatModalBtn" data-toggle="modal" data-target="#studentGroupRepeatModal" data-id-student="'.$item->id.'" title="Տեղափոխել մեկ այլ խումբ"><i class="fas fa-sync" style="color: white;"></i></button>
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

    public function getStudentData($request){

        $schoolId = Auth::user()->school_id;

        if (Auth::user()->hasRole('super-admin')) {
            $schoolId = $request->input('school_id');
        }

        $students = Student::select('id',
        DB::raw("CONCAT(first_name, ' ', last_name, ' ', father_name) as full_name")
        )->where('school_id', $schoolId)->whereNull('group_id')->get();
        return $students;
    }
    
    public function addStudents(array $validated){

        $groupId = (int) $validated['group_id'];
        $ids     = array_map('intval', $validated['add_student'] ?? []);

        $result = Student::where('school_id', Auth::user()->school_id)
            ->whereIn('id', $ids)
            ->update([
                'group_id'   => $groupId,
                'group_date' => Carbon::now()->format('Y-m-d'),
            ]);
        return $result;
    }
}
