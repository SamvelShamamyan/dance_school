<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\StudentGroupHistoryService;


class StudentHistoryController extends Controller
{
    protected $studentGroupHistoryService;
    public function __construct(StudentGroupHistoryService $studentGroupHistoryService){
        $this->studentGroupHistoryService = $studentGroupHistoryService;
    }
    
    public function groupHistoryData(Request $request, int $student){
        $result = app(StudentGroupHistoryService::class)->getHistoryData($request, $student);
        return response()->json($result);
    }
}
