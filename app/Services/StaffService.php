<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class StaffService
{
    public function getStaffData(Request $request){
   
        $draw    = (int) $request->input('draw');
        $start   = (int) $request->input('start', 0);
        $length  = (int) $request->input('length', 10);
        $search  = trim((string) $request->input('search.value', ''));
        $orderIx = $request->input('order.0.column');
        $orderBy = $request->input("columns.$orderIx.data");
        $dir     = $request->input('order.0.dir', 'asc');

        $user   = Auth::user();

        $userSchoolId   = (int) ($user->school_id ?? 0);
        $filterSchoolId = $user->hasRole('super-admin')
            ? (int) $request->input('school_id', 0)
            : $userSchoolId;

        $query = Staff::query()
            ->with(['schools:id,name'])
            ->select('staff.*')->orderBy('id','DESC');

        if ($user->hasRole('super-admin')) {
            if ($filterSchoolId > 0) {
                $query->whereHas('schools', fn($q) =>
                    $q->where('school_names.id', $filterSchoolId)
                );
            }
        } else {
            $query->whereHas('schools', fn($q) =>
                $q->where('school_names.id', $filterSchoolId)
            );
        }

        $recordsTotal = (clone $query)->count('staff.id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name',  'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('father_name','like', "%{$search}%")
                ->orWhereHas('schools', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }

        $recordsFiltered = (clone $query)->count('staff.id');

        if ($orderBy && in_array(strtolower($dir), ['asc','desc'], true)) {
            if ($orderBy === 'school_name') {
                $query->leftJoin('school_staff', 'school_staff.staff_id', '=', 'staff.id')
                    ->leftJoin('school_names', 'school_names.id', '=', 'school_staff.school_id')
                    ->groupBy('staff.id')
                    ->orderBy('school_names.name', $dir)
                    ->select('staff.*'); 
            } else {
                $query->orderBy($orderBy, $dir);
            }
        }

        $rows = $query->skip($start)->take($length)->get();

        $rows->transform(function ($item) {
            $item->full_name   = trim($item->first_name.' '.$item->last_name.' '.$item->father_name);
            $item->school_name = $item->schools->pluck('name')->join(', ');
            $item->action = '
                <button class="btn btn-info btn-edit-staff" data-id="'.$item->id.'" title="Խմբագրել"><i class="fas fa-edit"></i></button>
                <button class="btn btn-danger btn-delete-staff" data-id="'.$item->id.'" title="Հեռացնել"><i class="fas fa-trash-alt"></i></button>
            ';
            return $item;
        });

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows->values()->toArray(),
        ];
    }

}
