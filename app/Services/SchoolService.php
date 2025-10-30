<?php

namespace App\Services;

use App\Models\SchoolName;
use Illuminate\Http\Request;

class SchoolService
{
    public function getSchoolData(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start'); 
        $length = $request->input('length'); 
        $search = $request->input('search.value'); 

        $query = SchoolName::query();
        $recordsTotal = $query->count();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();

        $orderColumnIndex = $request->input('order.0.column'); 
        $orderColumnName = $request->input("columns.$orderColumnIndex.data"); 
        $orderDirection = $request->input('order.0.dir'); 

        if ($orderColumnName && $orderDirection) {
            $query->orderBy($orderColumnName, $orderDirection);  
        }

        $data = $query->skip($start)->take($length)->orderBy('id', 'DESC')->get();

        $data->transform(function ($item) {
            $item->action = '
                <button class="btn btn-info btn-edit-school" data-id="'.$item->id.'"><i class="fas fa-edit"></i> Խմբագրել</button>
                <button class="btn btn-danger btn-delete-school" data-id="'.$item->id.'"><i class="fas fa-trash-alt"></i> Հեռացնել</button>
            ';
            return $item;
        });

        return [
            'draw' => intval($draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }
}
