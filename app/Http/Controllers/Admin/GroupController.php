<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\GroupRequest\GroupStoreRequest; 
use App\Http\Requests\GroupRequest\GroupUpdateRequest; 
use App\Http\Requests\GroupRequest\GroupStudentStoreRequest; 
use App\Http\Requests\GroupRequest\GroupStaffStoreRequest; 
use App\Http\Requests\GroupRequest\StudentRepeatStoreRequest; 
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\Student;
use App\Models\SchoolName;
use App\Models\StudentGroupChangeHistory;
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
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        return view('admin.group.index', compact('schools'));
    }

    public function create(){
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        return view('admin.group.form', compact('schools'));
    }

    public function getGroupData(Request $request){
        $result = $this->groupService->getGroupData($request);
        return response()->json($result);
    }

    public function add(GroupStoreRequest $request){
        try{

            $schoolId = Auth::user()->school_id;

            if (Auth::user()->hasRole('super-admin')) {
                $schoolId = $request->school_id;
            }

            $validated = $request->validated();
            $formattedDate = Carbon::createFromFormat('d.m.Y', $validated['group_date'])->format('Y-m-d');
            Group::create([
                'name'          => $validated['group_name'],
                'created_date'  => $formattedDate,
                'school_id'     => $schoolId,
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
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        return view('admin.group.form', compact('group','schools')); 
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

    public function getStudents(Request $request){
        try{

            $result = $this->groupStudentService->getStudentData($request);
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
            $this->groupStudentService->addStudents($validated, $request);

            return response()->json(['status' => 1, 'message' => 'Գործողությունը կատարված է']); 
            
        }catch(Throwable $e){

            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }

    public function studentsPage(Request $request, $groupId){

        $schoolId = Auth::user()->school_id;
        if (Auth::user()->hasRole('super-admin')) {  
            $schoolId = $request->input('school_id');        
        }

        $groupsData = Group::where('school_id',$schoolId)->get();
        $group = Group::where('school_id', $schoolId)
            ->where('id',$groupId)->first();
        $groupName = $group->name;

        return view('admin.group.student', compact('groupId','groupsData', 'groupName', 'schoolId'));
    }

    public function getStudenetsList(Request $request, $groupId){
        $result = $this->groupStudentService->getStudenetsList($request, $groupId);
        return response()->json($result);
    }


    public function studentRepeat(StudentRepeatStoreRequest $request){
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $student = Student::select('id','school_id','group_id','group_date')
                ->lockForUpdate()
                ->findOrFail($validated['student_id']);

            StudentGroupChangeHistory::where('student_id', $student->id)
                ->where('is_last', true)
                ->update(['is_last' => false]);

            StudentGroupChangeHistory::create([
                'student_id' => $student->id,
                'data' => [
                    'old_data' => $student->only(['id','school_id','group_id','group_date']),
                    'new_data' => [
                        'id' => $student->id,
                        'school_id'  => $student->school_id,
                        'group_id'   => $validated['group_id'],
                        'group_date' => Carbon::now()->toDateString(),
                    ],
                ],
                'is_last' => true,
            ]);

            $student->update([
                'group_id'   => $validated['group_id'],
                'group_date' => Carbon::now()->toDateString(),
            ]);

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Գործողությունը կատարված է։'
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error'   => $e->getMessage(),
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


    public function getStaff(Request $request){
        try{


            // dd($request->all());

            $result = $this->groupStaffService->getStaffData($request);
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
            $result = $this->groupStaffService->addStaff($validated, $request);


            $added   = $result['added_count'] ?? 0;
            $already = $result['already_count'] ?? 0;

            $status = 1; 
            $message = 'Գործողությունը կատարված է';

            if ($added > 0 && $already > 0) {
                $status  = 2;
                $message = 'Մասամբ պահպանվեց. Որոշ աշխատակիցներ արդեն կան խմբում';
            } elseif ($added === 0 && $already > 0) {
                $status  = 3;
                $message = 'Ընտրված աշխատակիցները արդեն կան խմբում';
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

    public function staffPage(Request $request, $groupId){
        
        $schoolId = Auth::user()->school_id;
        if (Auth::user()->hasRole('super-admin')) {  
            $schoolId = $request->input('school_id');        
        }

         $group = Group::where('school_id', $schoolId)
            ->where('id',$groupId)->first();
        $groupName = $group->name;
        return view('admin.group.staff', compact('groupId', 'groupName', 'schoolId'));
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
