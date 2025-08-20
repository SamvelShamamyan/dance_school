<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GroupStaffService
{

public function getStaffListData(Request $request, $groupId)
{
    $draw = $request->input('draw');
    $start = $request->input('start');
    $length = $request->input('length');
    $search = $request->input('search.value');

    $schoolId = Auth::user()->school_id;

    if (Auth::user()->hasRole('super-admin')) {
        $schoolId = $request->input('school_id');
    }
    
    $query = Staff::query()
        ->with('school')
        ->select('staff.*', 'groups.id as group_id')
        ->join('group_staff', 'group_staff.staff_id', '=', 'staff.id')
        ->join('groups', 'groups.id', '=', 'group_staff.group_id')
        ->where('group_staff.group_id', $groupId)
        ->whereNotNull('staff.school_id')
        ->where('staff.school_id', $schoolId)
        ->distinct('staff.id');


    $recordsTotal = $query->count();

    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('father_name', 'like', "%{$search}%")
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
                  ->select('staff.*'); 
        } else {
            $query->orderBy($orderColumnName, $orderDirection);
        }
    }

    $data = $query->skip($start)->take($length)->get();

    $data->transform(function ($item) {
        $item->full_name = $item->last_name . ' ' . $item->first_name . ' ' . $item->father_name;
        $item->school_name = $item->school->name ?? '';
        $item->action = '
            <button class="btn btn-info btn-edit-staff" data-id="'.$item->id.'" title="Խմբագրել"><i class="fas fa-edit"></i></button>
            <button class="btn btn-danger btn-delete-group-staff" data-id-staff="'.$item->id.'" data-id-group="'.$item->group_id.'"title="Հեռացնել աշխատակցին տվյալ խմբից"><i class="fas fa-trash-alt"></i></button>
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

    public function getStaffData($request){
        $schoolId = Auth::user()->school_id;
        if (Auth::user()->hasRole('super-admin')) {
            $schoolId = $request->input('school_id');
        }
        $staff = Staff::select('id',
        DB::raw("CONCAT(first_name, ' ', last_name, ' ', father_name) as full_name")
        )->where('school_id', $schoolId)->get();    
        return $staff;
    }


    public function addStaff(array $validated){
        
        $schoolId = Auth::user()->school_id;
        $groupId  = (int) $validated['group_id'];
        $ids      = array_map('intval', $validated['add_staff'] ?? []);

        if ($groupId <= 0 || empty($ids)) {
            return 0;
        }

        $group = Group::where('id', $groupId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $staffIds = Staff::where('school_id', $schoolId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        if (empty($staffIds)) {
            return 0;
        }

        return DB::transaction(function () use ($group, $staffIds, $schoolId) {
            $alreadyInGroup = $group->staff()->pluck('staff.id')->all();

            $alreadyInRequest = array_values(array_intersect($staffIds, $alreadyInGroup));
            $toAttach         = array_values(array_diff($staffIds, $alreadyInGroup));

            if (!empty($toAttach)) {
                $group->staff()->attach($toAttach);
            }

            $alreadyNames = [];
            if (!empty($alreadyInRequest)) {
                $alreadyNames = Staff::where('school_id', $schoolId)
                    ->whereIn('id', $alreadyInRequest)
                    ->pluck(DB::raw("CONCAT(last_name, ' ', first_name)"))
                    ->all();
            }

            return [
                'added_count'   => count($toAttach),
                'already_count' => count($alreadyInRequest),
                'already_ids'   => $alreadyInRequest,
                'already_names' => $alreadyNames,
            ];
        });
    }


}
