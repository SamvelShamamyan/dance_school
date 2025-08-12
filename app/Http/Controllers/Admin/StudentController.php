<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StudentRequest\StudentStoreRequest;
use App\Http\Requests\StudentRequest\StudentUpdateRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Student;
use App\Services\StudentService;
use Throwable;

class StudentController extends Controller
{
    protected $studentService;
    protected $student;
    public function __construct(StudentService $studentService){
        $this->studentService = $studentService;
        $this->student = new Student();
    }
    public function index(){
        return view('admin.student.index');
    }

    public function create(){
        return view('admin.student.form');
    }

    public function getSudentData(Request $request){
        $result = $this->studentService->getSudentData($request);
        return response()->json($result);
    }
   
    public function add(StudentStoreRequest $request){
        try{

            $validated = $request->validated();
            $formattedBirthDate = Carbon::createFromFormat('d.m.Y', $validated['birth_date'])->format('Y-m-d');
            $formattedStudentDate = Carbon::createFromFormat('d.m.Y', $validated['student_date'])->format('Y-m-d');
            $this->student::create([
                'first_name'    => $validated['first_name'],       
                'last_name'     => $validated['last_name'],
                'father_name'   => $validated['father_name'],
                'email'         => $validated['email'], 
                'address'       => $validated['address'],  
                'soc_number'    => $validated['soc_number'],  
                'birth_date'    => $formattedBirthDate,
                'created_date'  => $formattedStudentDate, 
                'school_id'     => Auth::user()->school_id,  
            ]);   

            return response()->json(['status' => 1, 'message' => 'Պահպանված է']); 

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }

    public function edit($id) {
        $student = Student::findOrFail($id);
        return view('admin.student.form', compact('student')); 
    }

    public function update(StudentUpdateRequest $request, $id) {            
        try{

            $validated = $request->validated();
            $student = $this->student::findOrFail($id);   
            $formattedBirthDate = Carbon::createFromFormat('d.m.Y', $validated['birth_date'])->format('Y-m-d');
            $formattedStudentDate = Carbon::createFromFormat('d.m.Y', $validated['student_date'])->format('Y-m-d');
            $student->update([
                'first_name'    => $validated['first_name'],
                'last_name'     => $validated['last_name'],
                'father_name'   => $validated['father_name'],
                'email'         => $validated['email'],
                'address'       => $validated['address'],
                'soc_number'    => $validated['soc_number'],
                'birth_date'    => $formattedBirthDate,
                'created_date'  => $formattedStudentDate,
            ]);

            return response()->json(['status' => 1, 'message' => 'Թարմացվել է']);

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

            $student = $this->student::find($id);

            if (!$student) {
                return response()->json([
                    'status' => -2,
                    'message' => 'Տվյալներ չեն գտնվել։'
                ], 404);
            }

            $student->delete();

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
