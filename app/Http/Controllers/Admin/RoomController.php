<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RoomRequest\RoomStoreRequest;
use App\Http\Requests\RoomRequest\RoomUpdateRequest; 
use Illuminate\Support\Facades\Auth;
use App\Services\RoomService;
use App\Models\Room;
use App\Models\SchoolName;
use Throwable;

class RoomController extends Controller
{
    protected $roomService;
    public function __construct(RoomService $roomService){
        $this->roomService = $roomService;
    }

    public function index(){
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        return view('admin.room.index', compact('schools'));
    }

    public function getRoomData(Request $request){
        $result = $this->roomService->getRoomData($request);
        return response()->json($result);
    }

    public function create(){
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        return view('admin.room.form',compact('schools'));
    }

    public function add(RoomStoreRequest $request){
        try{
            $validated = $request->validated();
            $schoolId = Auth::user()->school_id;
            if (Auth::user()->hasRole('super-admin')) {
                $schoolId = $request->school_id;
            }
            Room::create([
                "name" => $validated['name'],
                "school_id" => $schoolId,
            ]);    
            return response()->json([
                'status' => 1, 
                'message' => 'Գործողությունը կատարված է',
                'redirect'=> route('admin.room.index'),
            ]);  

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։'
            ], 500);
        }  
    }

    public function edit($id) {
        $room = Room::findOrFail($id);
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        return view('admin.room.form', compact('room', 'schools')); 
    }

    public function update(RoomUpdateRequest $request, $id) {            
        try{

            $validated = $request->validated();
            $room = Room::findOrFail($id);   

            // dd($id);exit;

            $schoolId = Auth::user()->school_id;
            if (Auth::user()->hasRole('super-admin')) {
                $schoolId = $request->school_id;
            }

            $room->update([
                'name' => $validated['name'],
                'school_id' => $schoolId,
            ]);

            return response()->json([
                'status' => 1, 
                'message' => 'Գործողությունը կատարված է',
                'redirect' => route('admin.room.edit', ['id' => $room->id]),
            ]);

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }

    public function delete($id){
        try {

            $room = Room::find($id);

            if (!$room) {
                return response()->json([
                    'status' => -2,
                    'message' => 'Տվյալներ չեն գտնվել։'
                ], 404);
            }

            $room->delete();

            return response()->json([
                'status' => 1,
                'message' => 'Գործողությունը կատարված է։'
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }
    }
}
