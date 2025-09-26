<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StudentAttendancesRequest\StudentAttendancesStoreRequest;
use App\Services\StudentAttendancesService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ScheduleGroup;
use App\Models\SchoolName;
use App\Models\StudentAttendance;
use App\Models\Student;
use Carbon\Carbon;
use Throwable;


class StudentAttendancesController extends Controller
{
    protected $studentAttendances;
    public function __construct(StudentAttendancesService $studentAttendances){
        date_default_timezone_set('Asia/Yerevan');
        $this->studentAttendances = $studentAttendances;     
    }

    public function index(){
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        return view('admin.studentAttendances.index', compact('schools'));
    }

    public function getStudentAttendancesData(Request $request){
        $result = $this->studentAttendances->getStudentAttendancesData($request);
        return response()->json($result);
    }

    public function checkStudentAttendances($id){

        $isTrue = false;
        
        $scheduleGroupData = ScheduleGroup::find($id);
        $studentsList = Student::where('group_id', $scheduleGroupData->group_id)
            ->select('id')
            ->selectRaw("CONCAT_WS(' ', first_name, last_name, father_name) AS full_name")
            ->get();
   
        $checkedAttendance = StudentAttendance::query()
            ->join('schedule_groups', 'schedule_groups.id', '=', 'student_attendances.schedule_group_id')
            ->join('groups', 'groups.id', '=', 'schedule_groups.group_id')
            ->join('school_names', 'school_names.id', '=', 'schedule_groups.school_id')
            ->join('students', 'students.id', '=', 'student_attendances.student_id')
            ->where('student_attendances.schedule_group_id', $id)
            ->select([
                'student_attendances.*', 'school_names.name as school_name', 'groups.name as group_name', 
                DB::raw("CONCAT_WS(' ', students.first_name, students.last_name, students.father_name) AS full_name"),
            ])
            ->orderBy('student_attendances.inspection_date', 'desc')
            ->get()
            ->groupBy(function ($row) {          

            return Carbon::parse($row->inspection_date)->toDateString();
        });

        $scheduleGroupTime = ScheduleGroup::where('id', $id)->first(['start_time', 'end_time', 'week_day']);
        $currentTime = Carbon::now()->format('H:i:s'); 
        $today = Carbon::now();
     
        if($today->dayOfWeek == $scheduleGroupTime->week_day && ($currentTime >= $scheduleGroupTime->start_time && $currentTime <= $scheduleGroupTime->end_time)){
          $isTrue = true;  
        }

        return view('admin.studentAttendances.form',compact('studentsList', 'id', 'checkedAttendance', 'isTrue'));
    }

    public function add(StudentAttendancesStoreRequest $request){
        try {

            // $inspectionDate = Carbon::createFromFormat('d.m.Y', $request->inspection_date)->format('Y-m-d');
            $inspectionDate = Carbon::now();
            $curentDate = $inspectionDate->toDate();

            $validated = $request->validated();

            foreach ( $validated['attendance_check']  as $studentId => $status) {
                StudentAttendance::create([
                    'schedule_group_id' => $request->input('schedule_group_id'),
                    'student_id'        => (int)$studentId,
                    'is_guest'          => isset($request->attendance_guest[$studentId]) ? 1 : 0,
                    'checked_status'    => (int)$status, 
                    'inspection_date'   => $curentDate,
                ]);
            }

            return response()->json([
                'status'  => 1,
                'message' => 'Գործողությունը կատարված է։',
                'redirect'=> '',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

  
}
