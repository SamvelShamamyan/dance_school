<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\PaymentRequest\PaymentStoreRequest;
use App\Http\Requests\PaymentRequest\PaymentUpdateRequest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use App\Models\SchoolName;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Group;
use App\Services\PaymentService;


use Throwable;

class PaymentController extends Controller
{
     protected $paymentService;
    public function __construct(PaymentService $paymentService){
        $this->paymentService = $paymentService;
    }
    public function index(){
        return view('admin.payment.index');
    }

    public function add(PaymentStoreRequest $request){
        try {
            $schoolId = Auth::user()->school_id;
            if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant')) {
                $schoolId = $request->input('school_id');
            }

            $validated = $request->validated();
            $paidAt = Carbon::createFromFormat('d.m.Y', $validated['paid_at'])->format('Y-m-d');

            DB::transaction(function () use ($schoolId, $validated, $paidAt) {

                $payment = Payment::create([
                    'school_id'  => $schoolId,
                    'group_id'   => $validated['group_id'],
                    'student_id' => $validated['student_id'],
                    'created_by' => Auth::id(),
                    'amount'     => $validated['amount'],
                    'paid_at'    => $paidAt, 
                    'method'     => $validated['method'],
                    // 'status'     => $validated['status'],
                    'comment'    => $validated['comment'],
                ]);

                
                $student = Student::lockForUpdate()->findOrFail($payment->student_id);

                $y = (int) Carbon::parse($payment->paid_at)->format('Y');
                $m = (int) Carbon::parse($payment->paid_at)->format('m');
                $paidThisMonth = Payment::where('student_id', $student->id)
                    ->whereYear('paid_at', $y)
                    ->whereMonth('paid_at', $m)
                    ->sum('amount');

                $T = (float) ($student->student_debts ?? 0.0);       
                $R = (float) ($student->student_prepayment ?? 0.0);
                $A = (float) $payment->amount;                       

                $payToDebt = min($A, $T);
                $T_after   = $T - $payToDebt;
       
                $leftover  = $A - $payToDebt;
                $R_after   = $R + max(0.0, $leftover);

                Student::whereKey($student->id)->update([
                    'student_transactions' => $paidThisMonth, 
                    'student_prepayment'   => $R_after,      
                    'student_debts'        => $T_after,     
                ]);
            });

            return response()->json(['status' => 1, 'message' => 'Գործողությունը կատարված է']);
        } catch (Throwable $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getPaymentData(Request $request){  
        $result = $this->paymentService->getPaymentData($request);
        return response()->json($result);
    }

    public function filters(Request $request){
        return response()->json($this->paymentService->getFilterOptions($request));
    }


    public function getGroups(Request $request){
        try{

            if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant')) {
                 $schoolId = $request->query('school_id');
            }
            $result = $this->paymentService->getGroupsData($request);
            return response()->json($result);

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }

    public function getStudents(Request $request, int $groupId){
        try{
            $result = $this->paymentService->getStudentsData( $groupId, $request);
            return response()->json($result);
        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }

    public function history(Request $request){
        //^-^// -> to change
        $request->validate([
            'student_id' => 'required|integer',
            'year'       => 'nullable|integer',
            'group_id'   => 'nullable|integer',
            'status'     => 'nullable|string'
        ]);

        try {
            $rows = $this->paymentService->getStudentHistory(
                (int)$request->student_id,
                $request->input('year'),
                $request->input('group_id'),
                $request->input('status'),
                5, 
                $request
            );

            return response()->json(['status' => 1, 'data' => $rows]);

        }catch(Throwable $e){
                return response()->json([
                    'status' => 0,
                    'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                    'error' => $e->getMessage(), 
                ], 500);
        }  
    }

    public function update(PaymentUpdateRequest $request, int $id){

        $schoolId = Auth::user()->school_id;

        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant')) {
            $schoolId = $request->school_id;      
        }

        $validated = $request->validated();
        $formattedPaidDate = Carbon::createFromFormat('d.m.Y', $validated['paid_at'])->format('Y-m-d');
        $payment = Payment::where('id', $id)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $payment->update([
            'paid_at' => $formattedPaidDate,
            'amount'  => (int) $validated['amount'],
            'method'  => $validated['method'],  
            'status'  => $validated['status'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json(['status' => 1, 'message' => 'Գործողությունը կատարված է']);
    }


    public function delete(Request $request, $id){

        $schoolId = Auth::user()->school_id;

        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant')) {
            $schoolId = $request->input('school_id');   
        }

        $payment = Payment::where('id', $id)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $payment->delete();

        return response()->json(['status'=>1, 'message' => 'Գործողությունը կատարված է']);
    }


    public function studentFilters(Request $request, Student $student){
         //^-^// -> to change
        // if ($student->school_id !== Auth::user()->school_id) {
        //     abort(403);
        // }

        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant')) {
        $schoolId = (int) $request->query('school_id');
        if (!$schoolId || $schoolId !== (int) $student->school_id) {
            abort(403);
        }
        } else {
            if ($student->school_id !== Auth::user()->school_id) {
                abort(403);
            }
        }

        $data = $this->paymentService->getStudentFilterOptions($student->id, $request);
        return response()->json($data);
    }

    public function studentPage(Request $request, Student $student){

        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant')) {
            $schoolId = $request->query('school_id');
            if (!$schoolId || $student->school_id != $schoolId) {
                abort(403);
            }
        } else {
            if ($student->school_id !== Auth::user()->school_id) {
                abort(403);
            }
        }

         return view('admin.payment.student', [
            'student'    => $student,
            'school_id' =>  $request->query('school_id'),
            'prefilters' => [
                'year'   => $request->query('year'),
                'status' => $request->query('status'),
            ],
        ]);
    }


    public function studentPaymentsData(Request $request, Student $student){
         //^-^// -> to change
        $data = $this->paymentService->getStudentPaymentsTable($request, $student->id);
        return response()->json($data);
    }

    public function getSchools(){
        try {

            $schools = SchoolName::select('id', 'name')
                ->orderBy('name')
                ->get();

            return response()->json($schools);

        } catch (Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Չհաջողվեց բեռնել դպրոցները',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getGroupsBySchool(int $schoolId){
        try {
            $groups = Group::select('id', 'name')
                ->where('school_id', $schoolId)
                ->orderBy('name')
                ->get();

            return response()->json($groups);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Չհաջողվեց բեռնել խմբերը',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getStudentData($studentId){
        try{

            $student = Student::findOrFail($studentId);
            return response()->json($student);

        } catch (Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Չհաջողվեց բեռնել խմբերը',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
