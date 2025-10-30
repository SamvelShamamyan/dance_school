<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class UserService
{
   public function getUserData(Request $request)
{
    $draw = $request->input('draw');
    $start = $request->input('start');
    $length = $request->input('length');
    $search = $request->input('search.value');
    
    $query = User::with('school')->whereNotNull('school_id');

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
            $query->leftJoin('school_names', 'users.school_id', '=', 'school_names.id')
                ->select('users.*') 
                ->orderBy('school_names.name', $orderDirection);
        }
        elseif ($orderColumnName === 'full_name') {
            $query->select('users.*')
                ->orderByRaw("LOWER(CONCAT(COALESCE(users.first_name,''), ' ',COALESCE(users.last_name,''),  ' ',COALESCE(users.father_name,''))) 
                    {$orderDirection}");
        }
        else {
            $query->orderBy($orderColumnName, $orderDirection);
        }
    }


    $data = $query->skip($start)->take($length)->orderBy('id', 'DESC')->get();

    $data->transform(function ($item) {
        $item->full_name = $item->first_name . ' ' . $item->last_name . ' ' . $item->father_name;
        $item->school_name = $item->school->name ?? '';
        $item->action = '
            <button class="btn btn-info btn-edit-user" data-id="'.$item->id.'"><i class="fas fa-edit"></i> Խմբագրել</button>
            <button class="btn btn-danger btn-delete-user" data-id="'.$item->id.'"><i class="fas fa-trash-alt"></i> Հեռացնել</button>
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
