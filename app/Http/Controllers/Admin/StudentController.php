<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StudentRequest\StudentStoreRequest;
use App\Http\Requests\StudentRequest\StudentUpdateRequest;
use App\Http\Requests\StudentCongratulation\StudentCongratulationStoreRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\StudentFile;
use App\Models\SchoolName;
use App\Models\Group;
use App\Models\StudentCongratulation;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCongratulationsEmail;
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
        $groups = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }

        if(Auth::user()->hasRole('school-admin')){
            $schoolId = Auth::user()->school_id;
            $groups = Group::select('id', 'name')
                ->where('school_id', $schoolId)
                ->orderBy('name')
                ->get();
        }
        
        // $birthdayStudentsThisMonth = Student::with(['school', 'group'])
        //     // ->where('school_id', )
        //     ->whereMonth('birth_date', Carbon::now())
        //     ->whereNull('deleted_at') 
        //     ->get();

            // dd($birthdayStudentsThisMonth);

        return view('admin.student.index', compact('schools','groups'));
    }

    public function create(){
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        return view('admin.student.form', compact('schools'));
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
            $isGuest = $request->boolean('is_guest');
            
            $formattedBirthDate = Carbon::createFromFormat('d.m.Y', $validated['birth_date'])->format('Y-m-d');
            $formattedStudentDate = Carbon::createFromFormat('d.m.Y', $validated['created_date'])->format('Y-m-d');
            $student_expected_payments = $validated['student_expected_payments'];

            $student = Student::create([
                'first_name' => $validated['first_name'],       
                'last_name' => $validated['last_name'],
                'father_name' => $validated['father_name'],
                'email' => $validated['email'], 
                'phone_1' => $validated['phone_1'], 
                'phone_2' => $validated['phone_2'], 
                'parent_first_name' => $validated['parent_first_name'], 
                'parent_last_name' => $validated['parent_last_name'], 
                'student_expected_payments' => $student_expected_payments, 
                'student_debts' => $student_expected_payments,
                'address' => $validated['address'],  
                'soc_number' => $validated['soc_number'],  
                'birth_date' => $formattedBirthDate,
                'created_date' => $formattedStudentDate, 
                'school_id' => $schoolId,
                'is_guest' => $isGuest,  
            ]); 
            
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store("student_files/{$student->id}", 'public');

                    StudentFile::create([
                        'student_id' => $student->id,
                        'path' => $path,
                        'url' => Storage::disk('public')->url($path),
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            return response()->json([
                'status' => 1, 
                'message' => 'Գործողությունը կատարված է։',
                'redirect'=> route('admin.student.index'),
            ]); 

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }


    public function edit($id){
        $schools = [];
        $student = Student::with('files')->findOrFail($id);
         if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        $files = $student->files->map(function ($f) {
            $url = $f->url ?? Storage::disk('public')->url($f->path);
            $isImage = Str::endsWith(strtolower($f->name ?? $f->path), [
                '.jpg','.jpeg','.png','.gif','.webp', 'pdf'
            ]);

            return [
                'id' => $f->id,
                'name' => $f->name ?? basename($f->path),
                'size' => (int) ($f->size ?? 0),
                'url'  => $url,
                'thumb' => $isImage ? $url : null, 
            ];
        })->values();

        return view('admin.student.form', [
            'student' => $student,
            'studentFilesJson' => $files->toJson(),
            'schools' => $schools,
        ]);
    }

    public function update(StudentUpdateRequest $request, $id){
        DB::beginTransaction();
        try {

            $validated = $request->validated();
            $student = Student::findOrFail($id);
            $isGuest = $request->boolean('is_guest');

            $schoolId = Auth::user()->school_id;

            if (Auth::user()->hasRole('super-admin')) {
                $schoolId = $request->school_id;
            }

            $formattedBirthDate = Carbon::createFromFormat('d.m.Y', $validated['birth_date'])->format('Y-m-d');
            $formattedStudentDate = Carbon::createFromFormat('d.m.Y', $validated['created_date'])->format('Y-m-d');    

            $student_expected_payments = $validated['student_expected_payments'];

            $student->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'father_name' => $validated['father_name'],
                'email' => $validated['email'],
                'phone_1' => $validated['phone_1'],
                'phone_2' => $validated['phone_2'],
                'parent_first_name' => $validated['parent_first_name'], 
                'parent_last_name' => $validated['parent_last_name'], 
                'student_expected_payments' => $student_expected_payments,
                // 'student_debts' => Null, 
                'address' => $validated['address'],
                'soc_number' => $validated['soc_number'],
                'birth_date' => $formattedBirthDate,
                'created_date' => $formattedStudentDate,
                'school_id' => $schoolId,
                'is_guest' => $isGuest,
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
                        'path' => $path,
                        'url' => Storage::disk('public')->url($path),
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                    ]);
                }
            } 

            DB::commit();
            return response()->json([
                'status' => 1, 
                'message' => 'Գործողությունը կատարված է։',
                'redirect' => route('admin.student.edit', ['id' => $student->id]),
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(),
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


    public function birthdayBlock(Request $request){
        $user = Auth::user();

        $schoolId       = $request->input('school_id');
        $groupId        = $request->input('group_id');
        $from           = $request->input('range_from'); 
        $to             = $request->input('range_to');
        $currentDate    = Carbon::now();  

        $currentDate = Carbon::now();
        $year = $currentDate->year;

        $q = Student::with(['school', 'group', 'studentCongratulation'])
                ->whereNull('deleted_at')
                 ->withExists([
                    'studentCongratulation as this_year_send_congratulation_email' => function ($qq) use ($year) {
                        $qq->whereYear('birth_date', $year);
                    }
                ]);;

        if ($user->hasRole(['super-admin', 'super-accountant', 'school-accountant'])) {
            if (!empty($schoolId)) $q->where('students.school_id', $schoolId);
            if (!empty($groupId))  $q->where('students.group_id', $groupId);
        } else {
            $q->where('students.school_id', $user->school_id);
            if (!empty($groupId)) $q->where('students.group_id', $groupId);
        }

        if (!empty($from) && !empty($to)) {
            if ($from <= $to) {
                $q->whereRaw("DATE_FORMAT(students.birth_date, '%m-%d') BETWEEN ? AND ?", [$from, $to]);
            } else {
                $q->where(function($w) use ($from, $to) {
                    $w->whereRaw("DATE_FORMAT(students.birth_date, '%m-%d') >= ?", [$from])
                    ->orWhereRaw("DATE_FORMAT(students.birth_date, '%m-%d') <= ?", [$to]);
                });
            }
        } else {
            $q->whereMonth('birth_date', Carbon::now()->month);
        }

        $birthdayStudentsThisMonth = $q
            ->orderByRaw("DATE_FORMAT(students.birth_date, '%m-%d') ASC")
            ->get();
     
        $fromMonthFilter = null;
        $toMonthFilter   = null;

        if (!empty($from)) {
            $fromMonthFilter = (int) explode('-', $from)[0]; 
        }

        if (!empty($to)) {
            $toMonthFilter = (int) explode('-', $to)[0];
        }

        return view('admin.student.partials.birthday_block', compact(
            'birthdayStudentsThisMonth', 
            'currentDate', 
            'fromMonthFilter',
            'toMonthFilter'))->render();
    }


    public function sendCongratulations(StudentCongratulationStoreRequest $request){
        try{

            $validated = $request->validated();
            $currentDate = Carbon::now();
            $year = $currentDate->year;
            $month = $currentDate->month;

            $existingStudentIds = StudentCongratulation::whereIn('student_id', $validated['student_ids'])
                    ->whereYear('birth_date', $year)
                    ->pluck('student_id')
                    ->toArray();

            $newStudentIds = array_values(array_diff($validated['student_ids'], $existingStudentIds));

            if (empty($newStudentIds)) {
                return response()->json([
                    'status' => 3,
                    'message' => 'Գործողությունը կատարված է։'
                ]);

            }

            $students = Student::whereIn('id', $newStudentIds)->get();

            foreach($students  as $student){
                if (!empty($student->email)) {
                    Mail::to($student->email)->queue(new SendCongratulationsEmail($student));
                }
            }

            $rows = array_map(function ($id) use ($currentDate) {
                return [
                    'student_id' => $id,
                    'birth_date' => $currentDate,
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate,
                ];
            }, $newStudentIds);

            StudentCongratulation::insert($rows);

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


}
