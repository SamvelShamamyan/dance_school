<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ScheduleGroup\ScheduleGroupStoreRequest;
use App\Http\Requests\ScheduleGroup\ScheduleGroupUpdateRequest;

use App\Models\ScheduleGroup;
use App\Models\SchoolName;
use App\Models\Group;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Throwable;

class ScheduleGroupController extends Controller
{
    public function index(){
        $schools = [];
        $groups = [];
        $rooms = [];
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant')) {
            $schools = SchoolName::get();
        }
        if(Auth::user()->hasRole('school-admin')){
            $schoolId = Auth::user()->school_id;
            $groups = Group::select('id', 'name')
                ->where('school_id', $schoolId)
                ->orderBy('name')
                ->get();

            $rooms = Room::select('id', 'name')
                ->where('school_id', $schoolId)
                ->orderBy('name')
                ->get();

        }
        return View('admin.scheduleGroup.index', compact('schools', 'groups', 'rooms'));
    }


    public function getEvents(Request $request){
      
        $schoolId = $request->query('school_id');
        $groupId  = $request->query('group_id');

        $request->validate([
            'school_id' => ['nullable', 'integer', 'exists:school_names,id'],
            'group_id'  => ['nullable', 'integer', 'exists:groups,id'],
        ]);

        $schoolId = Auth::user()->school_id;

        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant')) {
            $schoolId = $request->input('school_id');   
        }

        $eventsQuery = ScheduleGroup::with(['school:id,name', 'group:id,name', 'room:id,name'])
            ->orderBy('week_day')
            ->orderBy('start_time');

        $eventsQuery->when($schoolId, fn($q, $val) => $q->where('school_id', $val))
                    ->when($groupId,  fn($q, $val) => $q->where('group_id',  $val));

        $events = $eventsQuery->get([
            'id','week_day','start_time','end_time',
            'school_id','group_id','room_id','title','note','color'
        ]);

        return response()->json($events);
    }

    public function add(ScheduleGroupStoreRequest $request){     
        try{
            $data = $request->validated();

            if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant')) {
                $schoolId =  $data['school_id'];
            }else{
                $schoolId = $schoolId = Auth::user()->school_id;
            }

            $start = Carbon::createFromFormat('H:i', $data['start'])->format('H:i:s');
            $end   = Carbon::createFromFormat('H:i', $data['end'])->format('H:i:s');

            if ($start >= $end) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Սկիզբը պետք է փոքր լինի ավարտից։'
                ], 422);
            }

            $day    = $data['day'];
            $roomId = $data['room_id'];

            $check = DB::table('schedule_groups')
                ->where('room_id', $roomId)
                ->where('week_day', $day)
                ->where('start_time', '<', $end) 
                ->where('end_time',   '>', $start)
                ->exists();

            if ($check) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Այս ժամին տվյալ դահլիճում արդեն դասաժամ կա։'
                ], 409);
            }

            $ev = ScheduleGroup::create([
                'week_day'   => $data['day'], 
                'start_time' => $data['start'] . ':00',
                'end_time'   => $data['end']   . ':00',
                'title'      => $data['title'] ?? '',
                'school_id'  => $schoolId,
                'group_id'   => $data['group_id'],
                'room_id'    => $data['room_id'],
                'note'       => $data['note']  ?? null,
                'color'      => $data['color'] ?? 'blue',
            ]);

            return response()->json(['id' => $ev->id], 201);
            
        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։'
            ], 500);
        }  
    }



    public function edit($id, ScheduleGroupUpdateRequest $request){
        $data = $request->validated();
        $ev = ScheduleGroup::findOrFail($id);

        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant')) {
            $schoolId =  $data['school_id'];
        }else{
            $schoolId = $schoolId = Auth::user()->school_id;
        }

        $startInput = $data['start'] ?? substr($ev->start_time, 0, 5); 
        $endInput   = $data['end']   ?? substr($ev->end_time,   0, 5); 

        $start = Carbon::createFromFormat('H:i', $startInput)->format('H:i:s');
        $end   = Carbon::createFromFormat('H:i', $endInput)->format('H:i:s');

        if ($start >= $end) {
            return response()->json([
                'status' => 0,
                'message' => 'Սկիզբը պետք է փոքր լինի ավարտից։'
            ], 422);
        }

        $day    = $data['day']     ?? $ev->week_day;
        $roomId = $data['room_id'] ?? $ev->room_id;

        $check = DB::table('schedule_groups')
            ->where('room_id', $roomId)
            ->where('week_day', $day)
            ->where('id', '!=', $id)                  
            ->where('start_time', '<', $end)       
            ->where('end_time',   '>', $start)
            ->exists();

        if ($check) {
            return response()->json([
                'status'  => 0,
                'message' => 'Այս ժամին տվյալ դահլիճում արդեն դասաժամ կա։'
            ], 409);
        }

        $ev->update([
            'week_day'   => $day,
            'start_time' => $start,
            'end_time'   => $end,
            'title'      => $data['title'] ?? $ev->title,
            'school_id'  => $schoolId,
            'group_id'   => $data['group_id'] ?? $ev->group_id,
            'room_id'    => $roomId,
            'note'       => $data['note']  ?? $ev->note,
            'color'      => $data['color'] ?? $ev->color,
        ]);

        return response()->noContent();
    }



     public function delete($id)
    {
        $ev = ScheduleGroup::findOrFail($id);
        $ev->delete();
        return response()->noContent();
    }


    public function getGroupsBySchool(int $schoolId = null){
        try {

            $groups = Group::select('id', 'name')
                ->where('school_id', $schoolId)
                ->orderBy('name')
                ->get();

            $rooms = Room::select('id', 'name')
                ->where('school_id', $schoolId)
                ->orderBy('name')
                ->get();

            return response()->json(['groups' => $groups, 'rooms' => $rooms]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
