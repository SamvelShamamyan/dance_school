<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StudentRequest\StudentStoreRequest;
use App\Http\Requests\StudentRequest\StudentUpdateRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\StudentFile;
use App\Models\SchoolName;
use App\Services\StudentService;
use Throwable;

class StudentController extends Controller
{
    protected $studentService;
    protected $student;
    public function __construct(StudentService $studentService){
        $this->studentService = $studentService;

        
    }
    public function index(){
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        $is_create = true;
        return view('admin.student.index', compact('schools'));
    }

    public function create(){
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        $is_create = true;
        return view('admin.student.form', compact('schools','is_create'));
    }

    public function getSudentData(Request $request){
        $result = $this->studentService->getSudentData($request);
        return response()->json($result);
    }
   
    public function add(StudentStoreRequest $request){
        try{

            $schoolId = Auth::user()->school_id;

            if (Auth::user()->hasRole('super-admin')) {
                $schoolId = $request->school_id;
            }

            $validated = $request->validated();
            $formattedBirthDate = Carbon::createFromFormat('d.m.Y', $validated['birth_date'])->format('Y-m-d');
            $formattedStudentDate = Carbon::createFromFormat('d.m.Y', $validated['created_date'])->format('Y-m-d');
            $student = Student::create([
                'first_name'    => $validated['first_name'],       
                'last_name'     => $validated['last_name'],
                'father_name'   => $validated['father_name'],
                'email'         => $validated['email'], 
                'address'       => $validated['address'],  
                'soc_number'    => $validated['soc_number'],  
                'birth_date'    => $formattedBirthDate,
                'created_date'  => $formattedStudentDate, 
                'school_id'     => $schoolId,  
            ]);   

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store("student_files/{$student->id}", 'public');

                    StudentFile::create([
                        'student_id' => $student->id,
                        'path'     => $path,
                        'url'      => Storage::disk('public')->url($path),
                        'name'     => $file->getClientOriginalName(),
                        'size'     => $file->getSize(),
                    ]);
                }
            }

            return response()->json(['status' => 1, 'message' => 'Պահպանված է']); 

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }


    public function edit($id){
        $student = Student::with('files')->findOrFail($id);

        $files = $student->files->map(function ($f) {
            $url = $f->url ?? Storage::disk('public')->url($f->path);
            $isImage = Str::endsWith(strtolower($f->name ?? $f->path), [
                '.jpg','.jpeg','.png','.gif','.webp', 'pdf'
            ]);

            return [
                'id'   => $f->id,
                'name' => $f->name ?? basename($f->path),
                'size' => (int) ($f->size ?? 0),
                'url'  => $url,
                'thumb'=> $isImage ? $url : null, 
            ];
        })->values();

        return view('admin.student.form', [
            'student' => $student,
            'studentFilesJson' => $files->toJson(),
            'is_create' => false,
        ]);
    }

    public function update(StudentUpdateRequest $request, $id){
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $student = Student::findOrFail($id);

            $formattedBirthDate = Carbon::createFromFormat('d.m.Y', $validated['birth_date'])->format('Y-m-d');
            $formattedStudentDate = Carbon::createFromFormat('d.m.Y', $validated['created_date'])->format('Y-m-d');
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

            $removeIds = (array) $request->input('removed_files', []);
            if (!empty($removeIds)) {
                $toDelete = StudentFile::where('student_id', $student->id)
                    ->whereIn('id', $removeIds)
                    ->get();

                foreach ($toDelete as $f) {
                    if (!empty($f->path)) {
                        Storage::disk('public')->delete($f->path);
                    }
                }

                StudentFile::whereIn('id', $toDelete->pluck('id'))->delete();
            }

            $incoming = $request->file('files');
            if ($incoming) {
                $incoming = is_array($incoming) ? $incoming : [$incoming];

                foreach ($incoming as $file) {
                    if (!$file) continue;

                    $path = $file->store("students_files/{$student->id}", 'public');

                    StudentFile::create([
                        'student_id' => $student->id,
                        'path'     => $path,
                        'url'      => Storage::disk('public')->url($path),
                        'name'     => $file->getClientOriginalName(),
                        'size'     => $file->getSize(),
                    ]);
                }
            } 

            DB::commit();
            return response()->json(['status' => 1, 'message' => 'Թարմացվել է']);

        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id){
        try {

            $student = Student::find($id);

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
