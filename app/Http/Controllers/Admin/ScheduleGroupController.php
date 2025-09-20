<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScheduleGroup;
use App\Models\SchoolName;
use App\Models\Group;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Throwable;
use View;

class ScheduleGroupController extends Controller
{
    public function index(){
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        return View('admin.scheduleGroup.index', compact('schools'));
    }


    // public function add(Request $request){
    //     // dd($request->all());exit;

    //     ScheduleGroup::create([
    //         'week_day' => $request->day,
    //         'start_time' => $request->start,
    //         'end_time' => $request->end,
    //         'title' => $request->title,
    //         'node' => $request->node,
    //         'color' => $request->color,
    //     ]);

    // }

     public function add(Request $request)
    {

        // dd($request->all());exit;

        $data = $request->validate([
            'week_start' => ['required','date','date_format:Y-m-d'],
            'day'        => ['required','integer','between:1,7'],
            'start'      => ['required','date_format:H:i'],
            'end'        => ['required','date_format:H:i','after:start'],
            'school_id'  => ['required'],
            'group_id'   => ['required'],
            'room_id'    => ['required'],
            'title'      => ['nullable','string','max:255'],
            'note'       => ['nullable','string','max:255'],
            // 'color'      => ['nullable','string','max:32', Rule::in(['blue','green','purple','orange'])],
            'color'      => ['nullable','string','max:32'],
        ]);

        $ev = ScheduleGroup::create([
            'week_start' => $data['week_start'],
            'week_day'   => $data['day'],
            'start_time' => $data['start'] . ':00',
            'end_time'   => $data['end']   . ':00',
            'title'      => $data['title'] ?? '',
            'school_id'  => $data['school_id'],
            'group_id'   => $data['group_id'],
            'room_id'    => $data['room_id'],
            'note'       => $data['note']  ?? null,
            'color'      => $data['color'] ?? 'blue',
        ]);

        return response()->json(['id' => $ev->id], 201);
    }



     public function edit($id, Request $request)
    {
        $data = $request->validate([
            'week_start' => ['required','date','date_format:Y-m-d'],
            'day'        => ['required','integer','between:1,7'],
            'start'      => ['required','date_format:H:i'],
            'end'        => ['required','date_format:H:i','after:start'],
            'school_id'  => ['required'],
            'group_id'   => ['required'],
            'room_id'    => ['required'],
            'title'      => ['nullable','string','max:255'],
            'note'       => ['nullable','string','max:255'],
            // 'color'      => ['nullable','string','max:32', Rule::in(['blue','green','purple','orange'])],
            'color'      => ['nullable','string','max:32'],
        ]);

        $ev = ScheduleGroup::findOrFail($id);

        $ev->update([
            'week_start' => $data['week_start'],
            'week_day'   => $data['day'],
            'start_time' => $data['start'] . ':00',
            'end_time'   => $data['end']   . ':00',
            'title'      => $data['title'] ?? '',
            'school_id'  => $data['school_id'],
            'group_id'   => $data['group_id'],
            'room_id'    => $data['room_id'],
            'note'       => $data['note']  ?? null,
            'color'      => $data['color'] ?? 'blue',
        ]);

        return response()->noContent();
    }


     public function delete($id)
    {
        $ev = ScheduleGroup::findOrFail($id);
        $ev->delete();
        return response()->noContent();
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

        $eventsQuery = ScheduleGroup::with(['school:id,name', 'group:id,name'])
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
