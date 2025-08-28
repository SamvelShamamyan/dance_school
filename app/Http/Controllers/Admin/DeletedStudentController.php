<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\SchoolName;
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
        return view('admin.deleted_student.index', compact('schools'));
    }

    public function getSudentData(Request $request){
        $result = $this->studentService->getSudentData($request);
        return response()->json($result);
    }

}
