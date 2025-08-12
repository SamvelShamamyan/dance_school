<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\PaymentRequest\PaymentStoreRequest;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Payment;
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
        try{

            $validated = $request->validated();
            $formattedPaidDate = Carbon::createFromFormat('d.m.Y', $validated['paid_at'])->format('Y-m-d');
            Payment::create([
                'school_id'     => Auth::user()->school_id,
                'group_id'      => $validated['group_id'],
                'student_id'    => $validated['student_id'],
                'created_by'    => Auth::user()->id,
                'amount'        => $validated['amount'],                  
                'paid_at'       => $formattedPaidDate,
                'method'        => $validated['method'],
                'status'        => $validated['status'],
                'comment'       => $validated['comment'],
            ]);   

            return response()->json(['status' => 1, 'message' => 'Գործողությունը կատարված է']); 

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
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


    public function getGroups(){
        try{
            $result = $this->paymentService->getGroupsData();
            return response()->json($result);
        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }

    public function getStudents(int $groupId){
        try{
            $result = $this->paymentService->getStudentsData( $groupId);
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
                $request->year,
                $request->group_id,
                $request->status
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

    public function update(Request $request, int $id){
        //^-^// -> to change
        $validated = $request->validate([
            'edit_paid_at' => 'required|date_format:d.m.Y',
            'amount'  => 'required|integer|min:0',
            'method'  => 'required|in:cash,card,online',
            'status'  => 'required|in:paid,pending,failed,refunded',
            'comment' => 'nullable|string|max:255',
        ]);

        $payment = Payment::where('id', $id)
            ->where('school_id', Auth::user()->school_id)
            ->firstOrFail();

        $payment->update([
            'paid_at' => Carbon::createFromFormat('d.m.Y', $validated['edit_paid_at'])->format('Y-m-d'),
            'amount'  => (int) $validated['amount'],
            'method'  => $validated['method'],  
            'status'  => $validated['status'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json(['status' => 1, 'message' => 'Գործողությունը կատարված է']);
    }


    public function delete(int $id){
        $payment = Payment::where('id', $id)
            ->where('school_id', Auth::user()->school_id)
            ->firstOrFail();

        $payment->delete();

        return response()->json(['status'=>1, 'message' => 'Գործողությունը կատարված է']);
    }


}
