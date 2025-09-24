<?php

namespace App\Services;

use App\Models\ScheduleGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentAttendancesService
{
    public $weekdayHy = [
        1 => 'Երկուշաբթի',
        2 => 'Երեքշաբթի',
        3 => 'Չորեքշաբթի',
        4 => 'Հինգշաբթի',
        5 => 'Ուրբաթ',
        6 => 'Շաբաթ',
        7 => 'Կիրակի', 
    ];
    public function getStudentAttendancesData(Request $request){
        
        $draw   = $request->input('draw');
        $start  = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search.value');

        // $schoolId = Auth::user()->school_id;

        $query = ScheduleGroup::with(['school','group','room']);

        $selectedSchoolId = null;

        if (Auth::user()->hasRole('super-admin')) {
            $selectedSchoolId = $request->input('school_id') ?: null;
            $selectedGroupId  = $request->input('group_id') ?: null;

            $query->when($selectedSchoolId !== null && $selectedSchoolId !== '', function ($q) use ($selectedSchoolId) {
                $q->where('school_id', $selectedSchoolId);
            });

            $query->when($selectedGroupId !== null && $selectedGroupId !== '', function ($q) use ($selectedGroupId) {
                $q->where('group_id', $selectedGroupId);
            });

            if (($selectedSchoolId === null || $selectedSchoolId === '') &&
                ($selectedGroupId === null  || $selectedGroupId === '')) {
                $query->whereNotNull('school_id');
            }
            
            } else {
                $query->where('school_id', Auth::user()->school_id);
        }


        $recordsTotal = $query->count();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('school', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%"); 
                })
                ->orWhereHas('group', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%"); 
                })
                ->orWhereHas('room', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%"); 
                });
            });
        }

        $recordsFiltered = $query->count();

        $orderColumnIndex = $request->input('order.0.column');
        $orderColumnName  = $request->input("columns.$orderColumnIndex.data");
        $orderDirection   = $request->input('order.0.dir');

        if ($orderColumnName && $orderDirection) {
            if (in_array($orderColumnName, ['school_name','group_name','room_name'], true)) {
                $query->leftJoin('schools', 'schedule_groups.school_id', '=', 'schools.id')
                    ->leftJoin('rooms',   'schedule_groups.room_id',   '=', 'rooms.id')
                    ->leftJoin('groups',  'schedule_groups.group_id',  '=', 'groups.id')
                    ->select('schedule_groups.*',
                            'schools.name as school_name',
                            'groups.name as group_name',
                            'rooms.name  as room_name',
                        );

                $orderMap = [
                    'school_name' => 'schools.name',
                    'group_name'  => 'groups.name',
                    'room_name'   => 'rooms.name',
                ];

                $query->orderBy($orderMap[$orderColumnName], $orderDirection);
            } else {
                $query->orderBy($orderColumnName, $orderDirection);
            }
        }
        
        $data = $query->skip($start)->take($length)->get();

        $weekdayHy = $this->weekdayHy;

        $data->transform(function ($item) use ($weekdayHy) {

            $item->school_name = $item->school_name ?? ($item->school->name ?? '');
            $item->group_name  = $item->group_name  ?? ($item->group->name  ?? '');
            $item->room_name   = $item->room_name   ?? ($item->room->name   ?? '');
            $item->week_day    = $weekdayHy[(int)$item->week_day] ?? '';
            $item->action = '
                <button class="btn btn-info btn-check-attendances" data-id="'.$item->id.'"
                     data-school-id="'.$item->school_id.'" data-group-id="'.$item->group_id.'" 
                     data-room-id="'.$item->room_id.'"  data-start-time="'.$item->start_time.'"
                     data-end-time="'.$item->end_time.'" title="Անցկացնել ստուգում"><i class="fas fa-clipboard-check"></i></button>
            ';
            return $item;
        });

        return [
            'draw'            => intval($draw),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data->values()->toArray()
        ];
    }
}
