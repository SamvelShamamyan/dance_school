<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;


class StaffService
{
   public function getStaffData(Request $request)
{
    $draw = $request->input('draw');
    $start = $request->input('start');
    $length = $request->input('length');
    $search = $request->input('search.value');
    
    $query = Staff::with('school')->whereNotNull('school_id')->where('school_id', Auth::user()->school_id);


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
            <button class="btn btn-danger btn-delete-staff" data-id="'.$item->id.'" title="Հեռացնել"><i class="fas fa-trash-alt"></i></button>
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
