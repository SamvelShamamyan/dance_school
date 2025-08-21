<?php

namespace App\Services;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupService
{
   public function getGroupData(Request $request){
    $draw   = $request->input('draw');
    $start  = $request->input('start');
    $length = $request->input('length');
    $search = $request->input('search.value');

    // $schoolId = Auth::user()->school_id;

    $query = Group::with('school');

    $selectedSchoolId = null;

    if (Auth::user()->hasRole('super-admin')) {
        $selectedSchoolId = $request->input('school_id') ?: null;

        if ($selectedSchoolId !== null && $selectedSchoolId !== '') {
            $query->where('school_id', $selectedSchoolId);
        } else {
            $query->whereNotNull('school_id');
            $selectedSchoolId = null; 
        }
    } else {
        $query->where('school_id', Auth::user()->school_id);
    }

    
    // $query = Group::with('school')->whereNotNull('school_id')->where('school_id', Auth::user()->school_id);

    $recordsTotal = $query->count();

    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
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
            $query->leftJoin('school_names', 'groups.school_id', '=', 'school_names.id')
                  ->orderBy('school_names.name', $orderDirection)
                  ->select('groups.*'); 
        } else {
            $query->orderBy($orderColumnName, $orderDirection);
        }
    }

    $data = $query->skip($start)->take($length)->get();

    // $data->transform(function ($item) {
    //     $item->school_name = $item->school->name ?? '';
    //     $item->action = '
    //         <a href="'.route('admin.group.studentsPage', $item->id).'" class="btn btn-success" id="getGroupStudent" data-group-id ="'.$item->id.'"><i class="fas fa-users"></i> Աշակերտներ</a>
    //         <button class="btn btn-success" id="studentGroupModalBtn" data-toggle="modal" data-target="#studentGroupModal" data-group-id ="'.$item->id.'"><i class="fas fa-user"></i> Ավելացնել աշակերտ</button>
    //         <a href="'.route('admin.group.staffPage', $item->id).'" class="btn btn-warning" id="getGroupStaff" data-group-id ="'.$item->id.'"><i class="fas fa-users"></i> Աշխատակազմ</a>
    //         <button class="btn btn-warning" id="staffGroupModalBtn" data-toggle="modal" data-target="#staffGroupModal" data-group-id ="'.$item->id.'"><i class="fas fa-users"></i> Ավելացնել աշխատակից</button>
    //         <button class="btn btn-info btn-edit-group" data-id="'.$item->id.'" title="Խմբագրել"><i class="fas fa-edit"></i></button>
    //         <button class="btn btn-danger btn-delete-group" data-id="'.$item->id.'" title="Հեռացնել"><i class="fas fa-trash-alt"></i></button>
    //     ';
    //     return $item;
    // });

    $data->transform(function ($item) {
        $effectiveSchoolId = (int) $item->school_id;

        $studentsParams = ['groupId' => $item->id, 'school_id' => $effectiveSchoolId];
        $staffParams    = ['groupId' => $item->id, 'school_id' => $effectiveSchoolId];

        $item->school_name = $item->school->name ?? '';

        $item->action = '
        <a href="'.route('admin.group.studentsPage', $studentsParams).'"
                class="btn btn-success"
                id="getGroupStudent"
                data-group-id="'.$item->id.'"
                data-school-id="'.$effectiveSchoolId.'">
                <i class="fas fa-user-graduate"></i> Աշակերտներ
            </a>

            <button class="btn btn-success"
                    id="studentGroupModalBtn"
                    data-toggle="modal"
                    data-target="#studentGroupModal"
                    data-group-id="'.$item->id.'"
                    data-school-id="'.$effectiveSchoolId.'">
                <i class="fas fa-user-graduate"></i> Ավելացնել աշակերտ
            </button>

            <a href="'.route('admin.group.staffPage', $staffParams).'"
            class="btn btn-warning"
            id="getGroupStaff"
            data-group-id="'.$item->id.'"
            data-school-id="'.$effectiveSchoolId.'">
                <i class="fas fa-briefcase"></i> Աշխատակազմ
            </a>

            <button class="btn btn-warning"
                    id="staffGroupModalBtn"
                    data-toggle="modal"
                    data-target="#staffGroupModal"
                    data-group-id="'.$item->id.'"
                    data-school-id="'.$effectiveSchoolId.'">
                <i class="fas fa-briefcase"></i> Ավելացնել աշխատակից
            </button>

            <button class="btn btn-info btn-edit-group" data-id="'.$item->id.'" title="Խմբագրել">
                <i class="fas fa-edit"></i>
            </button>

            <button class="btn btn-danger btn-delete-group" data-id="'.$item->id.'" title="Հեռացնել">
                <i class="fas fa-trash-alt"></i>
            </button>
        ';

        return $item;
    });

    return [
        'draw'              => intval($draw),
        'recordsTotal'      => $recordsTotal,
        'recordsFiltered'   => $recordsFiltered,
        'data'              => $data->values()->toArray()
    ];
}

}
