<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomService
{
   public function getRoomData(Request $request){
    $draw   = $request->input('draw');
    $start  = $request->input('start');
    $length = $request->input('length');
    $search = $request->input('search.value');

    // $schoolId = Auth::user()->school_id;

    $query = Room::with('school');

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
            $query->leftJoin('school_names', 'rooms.school_id', '=', 'school_names.id')
                  ->orderBy('rooms.name', $orderDirection)
                  ->select('rooms.*, school'); 
        } else {
            $query->orderBy($orderColumnName, $orderDirection);
        }
    }

    $data = $query->skip($start)->take($length)->get();

    $data->transform(function ($item) {
        $item->school_name = $item->school->name ?? '';
        $item->action = '
            <button class="btn btn-info btn-edit-room" data-id="'.$item->id.'" title="Խմբագրել"><i class="fas fa-edit"></i></button>
            <button class="btn btn-danger btn-delete-room" data-id="'.$item->id.'" title="Հեռացնել"><i class="fas fa-trash-alt"></i></button>
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
