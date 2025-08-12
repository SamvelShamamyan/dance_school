<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StaffRequest\StaffStoreRequest;
use App\Http\Requests\StaffRequest\StaffUpdateRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Staff;
use App\Services\StaffService;
use Throwable;

class StaffController extends Controller
{
    protected $staffService;
    public function __construct(StaffService $staffService){
        $this->staffService = $staffService;
    }

    public function index(){
        return view('admin.staff.index');
    }

    public function create(){
        return view('admin.staff.form');
    }

    public function getStaffData(Request $request){
        $result = $this->staffService->getStaffData($request);
        return response()->json($result);
    }

    public function add(StaffStoreRequest $request){
        try{

            $validated = $request->validated();
            $formattedBirthDate = Carbon::createFromFormat('d.m.Y', $validated['birth_date'])->format('Y-m-d');
            $formattedStaffDate = Carbon::createFromFormat('d.m.Y', $validated['staff_date'])->format('Y-m-d');
            Staff::create([
                'first_name'    => $validated['first_name'],       
                'last_name'     => $validated['last_name'],
                'father_name'   => $validated['father_name'],
                'email'         => $validated['email'], 
                'address'       => $validated['address'],  
                'soc_number'    => $validated['soc_number'],  
                'birth_date'    => $formattedBirthDate,
                'created_date'  => $formattedStaffDate, 
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
        $staff = Staff::findOrFail($id);
        return view('admin.staff.form', compact('staff')); 
    }


    public function update(StaffUpdateRequest $request, $id) {            
        try{

            $validated = $request->validated();
            $staff = Staff::findOrFail($id);   
            $formattedBirthDate = Carbon::createFromFormat('d.m.Y', $validated['birth_date'])->format('Y-m-d');
            $formattedStaffDate = Carbon::createFromFormat('d.m.Y', $validated['staff_date'])->format('Y-m-d');
            $staff->update([
                'first_name'    => $validated['first_name'],
                'last_name'     => $validated['last_name'],
                'father_name'   => $validated['father_name'],
                'email'         => $validated['email'],
                'address'       => $validated['address'],
                'soc_number'    => $validated['soc_number'],
                'birth_date'    => $formattedBirthDate,
                'created_date'  => $formattedStaffDate,
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
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json([
                'status' => -2,
                'message' => 'Տվյալներ չեն գտնվել։'
            ], 404);
        }

        $staff->delete();

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
