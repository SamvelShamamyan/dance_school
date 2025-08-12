<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GroupRequest\GroupStoreRequest; 
use App\Http\Requests\GroupRequest\GroupUpdateRequest; 
use App\Http\Requests\GroupRequest\GroupStudentStoreRequest; 
use App\Http\Requests\GroupRequest\GroupStaffStoreRequest; 
use App\Http\Requests\GroupRequest\StudentRepeatStoreRequest; 
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\Student;

use App\Services\GroupService;
use App\Services\GroupStudentService;
use App\Services\GroupStaffService;

use Throwable;

class GroupController extends Controller
{
    protected $groupService;
    protected $groupStudentService;
    protected $groupStaffService;
    public function __construct(GroupService $groupService, GroupStudentService $groupStudentService, GroupStaffService $groupStaffService){
        $this->groupService = $groupService;
        $this->groupStudentService = $groupStudentService;
        $this->groupStaffService = $groupStaffService;
    }

    public function index(){
        return view('admin.group.index');
    }

    public function create(){
        return view('admin.group.form');
    }

    public function getGroupData(Request $request){
        $result = $this->groupService->getGroupData($request);
        return response()->json($result);
    }

    public function add(GroupStoreRequest $request){
        try{

            $validated = $request->validated();
            $formattedDate = Carbon::createFromFormat('d.m.Y', $validated['group_date'])->format('Y-m-d');
            Group::create([
                'name'          => $validated['group_name'],
                'created_date'  => $formattedDate,
                'school_id'     => Auth::user()->school_id,
            ]);   

            return response()->json([
                'status' => 1, 
                'message' => 'Գործողությունը կատարված է'
            ]); 

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }

    public function edit($id) {
        $group = Group::findOrFail($id);
        return view('admin.group.form', compact('group')); 
    }

    public function update(GroupUpdateRequest $request, $id) {            
        try{

            $validated = $request->validated();
            $group = Group::findOrFail($id);   
            $formattedDate = Carbon::createFromFormat('d.m.Y', $validated['group_date'])->format('Y-m-d');
            $group->update([
                'name'          => $validated['group_name'],
                'created_date'  => $formattedDate,      
            ]);

            return response()->json([
                'status' => 1, 
                'message' => 'Գործողությունը կատարված է'
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
        
            $group = Group::findOrFail($id); 
            $group->delete();

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

    public function getStudents(){
        try{

            $result = $this->groupStudentService->getStudentData();
            return response()->json($result);

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }   

    public function addStudenets(GroupStudentStoreRequest $request){
        try{    

            $validated = $request->validated();
            $this->groupStudentService->addStudents($validated);

            return response()->json(['status' => 1, 'message' => 'Գործողությունը կատարված է']); 
            
        }catch(Throwable $e){

            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }

    public function studentsPage($groupId){

        $groupsData = Group::where('school_id',Auth::user()->school_id)->get();
        $group = Group::where('school_id', Auth::user()->school_id)
            ->where('id',$groupId)->first();
        $groupName = $group->name;

        return view('admin.group.student', compact('groupId','groupsData', 'groupName'));
    }

    public function getStudenetsList(Request $request, $groupId){
        $result = $this->groupStudentService->getStudenetsList($request, $groupId);
        return response()->json($result);
    }

    public function studentRepeat(StudentRepeatStoreRequest $request){
        try{

            $validated = $request->validated();            
            $student = Student::findOrFail($validated['student_id']); 
            $student->update(['group_id'=> $validated['group_id']]);

            return response()->json([
                'status' => 1,
                'message' => 'Գործողությունը կատարված է։'
            ]);

        }catch (Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }
    }


    public function deleteGroupStudent(int $studentId){
        try {   

            $student = Student::findOrFail($studentId); 
            $student->update(['group_id'=> null]);

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


    public function getStaff(){
        try{

            $result = $this->groupStaffService->getStaffData();
            return response()->json($result);

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }  

    public function addStaff(GroupStaffStoreRequest $request){
        try {
            $validated = $request->validated();
            $result = $this->groupStaffService->addStaff($validated);

            $added   = $result['added_count'] ?? 0;
            $already = $result['already_count'] ?? 0;

            $status = 1; 
            $message = 'Գործողությունը կատարված է';

            if ($added > 0 && $already > 0) {
                $status  = 2;
                $message = 'Մասամբ պահպանվեց. Որոշ աշխատակիցներ արդեն կային խմբում';
            } elseif ($added === 0 && $already > 0) {
                $status  = 3;
                $message = 'Ընտրված աշխատակիցները արդեն կային խմբում';
            } elseif ($added === 0 && $already === 0) {
                $status  = 3;
                $message = 'Չկա նոր տվյալ պահպանելու համար';
            }

            return response()->json([
                'status'         => $status,
                'message'        => $message,
                'added_count'    => $added,
                'already_count'  => $already,
                'already_ids'    => $result['already_ids'] ?? [],
                'already_names'  => $result['already_names'] ?? [],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function staffPage($groupId){
         $group = Group::where('school_id', Auth::user()->school_id)
            ->where('id',$groupId)->first();
        $groupName = $group->name;
        return view('admin.group.staff', compact('groupId', 'groupName'));
    }

    public function getStaffList(Request $request, $groupId){
        $result = $this->groupStaffService->getStaffListData($request, $groupId);
        return response()->json($result);
    }



    public function deleteGroupStaff(int $staffId, int $groupId){
        try {    
            $group = Group::findOrFail($groupId); 
            $group->staff()->detach($staffId);

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
