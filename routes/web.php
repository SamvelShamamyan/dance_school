<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AuthController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\PaymentController;



// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', [AuthController::class, 'index']);
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');



// Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');


Route::group(['namespace'=>'Admin','prefix'=>'admin','middleware'=>['auth']], function(){
   
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
  
    Route::group(['prefix'=>'school','middleware' => ['role:super-admin']], function(){
        Route::get('/', action: [SchoolController::class, 'index'])->name('admin.school.index');
        Route::post('/getData', action: [SchoolController::class, 'getSchoolData'])->name('admin.school.data');
        Route::get('/create', [SchoolController::class, 'create'])->name('admin.school.create');
        Route::post('/add', [SchoolController::class, 'add'])->name('admin.school.add');
        Route::get('/{id}/edit', [SchoolController::class, 'edit'])->name('admin.school.edit');
        Route::post('/{id}/update', [SchoolController::class, 'update'])->name('admin.school.update');
        Route::post('/{id}/delete', [SchoolController::class, 'delete'])->name('admin.school.delete');
       
    });

    Route::group(['prefix'=>'user','middleware' => ['role:super-admin']],  function(){
        Route::get('/', [UserController::class, 'index'])->name('admin.user.index');
        Route::post('/getData', action: [UserController::class, 'getUserData'])->name('admin.user.data');
        Route::get('/create', [UserController::class, 'create'])->name('admin.user.create');
        Route::post('/add', [UserController::class, 'add'])->name('admin.user.add');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
        Route::post('/{id}/update', [UserController::class, 'update'])->name('admin.user.update');
        Route::post('/{id}/delete', [UserController::class, 'delete'])->name('admin.user.delete');
    });


    Route::group(['prefix'=>'group','middleware' => ['role:school-admin']], function(){
        Route::get('/', [GroupController::class, 'index'])->name('admin.group.index');
        Route::post('/getData', action: [GroupController::class, 'getGroupData'])->name('admin.group.data');
        Route::get('/create', [GroupController::class, 'create'])->name('admin.group.create');
        Route::post('/add', [GroupController::class, 'add'])->name('admin.group.add');
        Route::get('/{id}/edit', [GroupController::class, 'edit'])->name('admin.group.edit');
        Route::post('/{id}/update', [GroupController::class, 'update'])->name('admin.group.update');
        Route::post('/{id}/delete', [GroupController::class, 'delete'])->name('admin.group.delete');
        Route::post('/getStudents', action: [GroupController::class, 'getStudents'])->name('admin.group.getStudents');
        Route::post('/addStudenets', action: [GroupController::class, 'addStudenets'])->name('admin.group.addStudenets');
        Route::get('/{id}/students', [GroupController::class, 'studentsPage'])->name('admin.group.studentsPage');    
        Route::post('/{id}/getStudenetsList', action: [GroupController::class, 'getStudenetsList'])->name('admin.group.getStudenetsList');
        Route::post('/student/{studentId}/delete', action: [GroupController::class, 'deleteGroupStudent'])->name('admin.group.deleteGroupStudent');
        Route::post('/getStaff', action: [GroupController::class, 'getStaff'])->name('admin.group.getStaff');
        Route::post('/addStaff', action: [GroupController::class, 'addStaff'])->name('admin.group.addStaff');
        Route::get('/{id}/staff', [GroupController::class, 'staffPage'])->name('admin.group.staffPage');    
        Route::post('/{id}/getStaffList', action: [GroupController::class, 'getStaffList'])->name('admin.group.getStaffList');
        Route::post('/staff/{staffId}/{groupId}/delete', action: [GroupController::class, 'deleteGroupStaff'])->name('admin.group.deleteGroupStaff');

        Route::post('/studentRepeat', [GroupController::class, 'studentRepeat'])->name('admin.group.studentRepeat'); 
       
    });

    Route::group(['prefix'=>'staff','middleware' => ['role:school-admin']], function(){
        Route::get('/', [StaffController::class, 'index'])->name('admin.staff.index');
        Route::post('/getData', action: [StaffController::class, 'getStaffData'])->name('admin.staff.data');
        Route::get('/create', [StaffController::class, 'create'])->name('admin.staff.create');
        Route::post('/add', [StaffController::class, 'add'])->name('admin.staff.add');
        Route::get('/{id}/edit', [StaffController::class, 'edit'])->name('admin.staff.edit');
        Route::post('/{id}/update', [StaffController::class, 'update'])->name('admin.staff.update');
        Route::post('/{id}/delete', [StaffController::class, 'delete'])->name('admin.staff.delete');
    });


    Route::group(['prefix'=>'student','middleware' => ['role:school-admin']], function(){
        Route::get('/', [StudentController::class, 'index'])->name('admin.student.index');
        Route::post('/getData', action: [StudentController::class, 'getSudentData'])->name('admin.student.data');
        Route::get('/create', [StudentController::class, 'create'])->name('admin.student.create');
        Route::post('/add', [StudentController::class, 'add'])->name('admin.student.add');
        Route::get('/{id}/edit', [StudentController::class, 'edit'])->name('admin.student.edit');
        Route::post('/{id}/update', [StudentController::class, 'update'])->name('admin.student.update');
        Route::post('/{id}/delete', [StudentController::class, 'delete'])->name('admin.student.delete');
    });


    Route::group(['prefix'=>'payment','middleware' => ['role:school-accountant|super-admin']], function(){
        Route::get('/', [PaymentController::class, 'index'])->name('admin.payment.index');
        Route::post('/getData', action: [PaymentController::class, 'getPaymentData'])->name('admin.payment.data');
        Route::get('/filters', [PaymentController::class,'filters'])->name('admin.payment.filters');
        Route::get('/getGroups', [PaymentController::class,'getGroups'])->name('admin.payment.getGroups');
        Route::get('/getStudents/{groupId}', action: [PaymentController::class,'getStudents'])->name('admin.payment.getStudents');
        Route::post('/add', [PaymentController::class,'add'])->name('admin.payment.add');
        Route::post('/history', [PaymentController::class, 'history'])->name('admin.payment.history');
        Route::post('/update/{id}', [PaymentController::class, 'update'])->name('admin.payment.update');
        Route::post('/{id}/delete', [PaymentController::class, 'delete'])->name('admin.payment.delete');
    
    });


});

