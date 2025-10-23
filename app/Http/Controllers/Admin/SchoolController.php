<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\SchoolRequest\SchoolStoreRequest;
use App\Http\Requests\SchoolRequest\SchoolUpdateRequest; 

use App\Models\SchoolName;
use App\Services\SchoolService;

use Throwable;


class SchoolController extends Controller
{
    protected $schoolService;
    public function __construct(SchoolService $schoolService){
        $this->schoolService = $schoolService;
    }

    public function index(){
         return view('admin.school.index');
    }


    public function getSchoolData(Request $request){
        $result = $this->schoolService->getSchoolData($request);
        return response()->json($result);
    }


    public function create(){
         return view('admin.school.form');
    }


    public function add(SchoolStoreRequest $request){
        try{

            $validated = $request->validated();
            SchoolName::create(["name" => $validated['name']]);    
            return response()->json([
                'status' => 1, 
                'message' => 'Գործողությունը կատարված է',
                'redirect'=> route('admin.school.index'),
            ]);  

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։'
            ], 500);
        }  
    }

     public function edit($id) {
        $school = SchoolName::findOrFail($id);
        return view('admin.school.form', compact('school')); 
    }

    public function update(SchoolUpdateRequest $request, $id) {            
        try{

            $validated = $request->validated();
            $school = SchoolName::findOrFail($id);   
            $school->update([
                'name'    => $validated['name'],
            ]);

            return response()->json([
                'status' => 1, 
                'message' => 'Գործողությունը կատարված է',
                'redirect' => route('admin.school.edit', ['id' => $school->id]),
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

            $school = SchoolName::find($id);

            if (!$school) {
                return response()->json([
                    'status' => -2,
                    'message' => 'Տվյալներ չեն գտնվել։'
                ], 404);
            }

            $school->delete();

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
