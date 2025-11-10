<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\SchoolName;
use App\Models\Student;
use App\Services\DeletedStudentService;
use Throwable;

class DeletedStudentController extends Controller
{
    protected $studentService;
    protected $student;
    public function __construct(DeletedStudentService $studentService){
        $this->studentService = $studentService;

        
    }
    public function index(){
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        $is_create = true;
        return view('admin.deletedStudent.index', compact('schools'));
    }

    public function getSudentData(Request $request){
        $result = $this->studentService->getSudentData($request);
        return response()->json($result);
    }


    public function restoreStudentById(int $id){
        try{
            
            Student::withTrashed()->where('id', $id)->restore();

            return response()->json([
                'status' => 1, 
                'message' => 'Գործողությունը կատարված է',
            ]);  

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։'
            ], 500);
        }  
    }

}
