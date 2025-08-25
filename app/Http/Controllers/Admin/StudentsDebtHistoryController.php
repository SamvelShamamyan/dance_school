<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\PaymentService;
use App\Services\StudentsDebtHistoryService;
use Throwable;

class StudentsDebtHistoryController extends Controller
{
    protected $paymentService;
    protected $studentsDebtHistoryService;
    public function __construct(PaymentService $paymentService, StudentsDebtHistoryService $studentsDebtHistoryService){
        $this->paymentService = $paymentService;
        $this->studentsDebtHistoryService = $studentsDebtHistoryService;
    }
    
    public function index(){
        return view('admin.debts.index'); 
    }


    public function getData(Request $request){
        try {
            $result = $this->studentsDebtHistoryService->getDebtData($request);
            return response()->json($result);
        } catch (Throwable $e) {
            return response()->json([
                'status'=>0,
                'message'=>'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error'=>$e->getMessage()
            ], 500);
        }
    }

    public function filters(Request $request){
         return response()->json( $this->paymentService->getFilterOptions($request));
    }

 

}
