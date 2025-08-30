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
use App\Http\Controllers\Admin\StudentHistoryController;
use App\Http\Controllers\Admin\DeletedStudentController;



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
        Route::get('/', [SchoolController::class, 'index'])->name('admin.school.index');
        Route::post('/getData', [SchoolController::class, 'getSchoolData'])->name('admin.school.data');
        Route::get('/create', [SchoolController::class, 'create'])->name('admin.school.create');
        Route::post('/add', [SchoolController::class, 'add'])->name('admin.school.add');
        Route::get('/{id}/edit', [SchoolController::class, 'edit'])->name('admin.school.edit');
        Route::post('/{id}/update', [SchoolController::class, 'update'])->name('admin.school.update');
        Route::post('/{id}/delete', [SchoolController::class, 'delete'])->name('admin.school.delete');
       
    });

    Route::group(['prefix'=>'user','middleware' => ['role:super-admin']],  function(){
        Route::get('/', [UserController::class, 'index'])->name('admin.user.index');
        Route::post('/getData',  [UserController::class, 'getUserData'])->name('admin.user.data');
        Route::get('/create', [UserController::class, 'create'])->name('admin.user.create');
        Route::post('/add', [UserController::class, 'add'])->name('admin.user.add');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
        Route::post('/{id}/update', [UserController::class, 'update'])->name('admin.user.update');
        Route::post('/{id}/delete', [UserController::class, 'delete'])->name('admin.user.delete');
    });


    Route::group(['prefix'=>'group','middleware' => ['role:school-admin|super-admin']], function(){
        Route::get('/', [GroupController::class, 'index'])->name('admin.group.index');
        Route::post('/getData',  [GroupController::class, 'getGroupData'])->name('admin.group.data');
        Route::get('/create', [GroupController::class, 'create'])->name('admin.group.create');
        Route::post('/add', [GroupController::class, 'add'])->name('admin.group.add');
        Route::get('/{id}/edit', [GroupController::class, 'edit'])->name('admin.group.edit');
        Route::post('/{id}/update', [GroupController::class, 'update'])->name('admin.group.update');
        Route::post('/{id}/delete', [GroupController::class, 'delete'])->name('admin.group.delete');
        Route::post('/getStudents',  [GroupController::class, 'getStudents'])->name('admin.group.getStudents');
        Route::post('/addStudenets',  [GroupController::class, 'addStudenets'])->name('admin.group.addStudenets');
        Route::get('/{groupId}/students',  [GroupController::class, 'studentsPage'])->name('admin.group.studentsPage');    
        Route::post('/{id}/getStudenetsList',  [GroupController::class, 'getStudenetsList'])->name('admin.group.getStudenetsList');
        Route::post('/student/{studentId}/delete',  [GroupController::class, 'deleteGroupStudent'])->name('admin.group.deleteGroupStudent');
        Route::post('/getStaff', [GroupController::class, 'getStaff'])->name('admin.group.getStaff');
        Route::post('/addStaff', [GroupController::class, 'addStaff'])->name('admin.group.addStaff');
        Route::get('/{groupId}/staff', [GroupController::class, 'staffPage'])->name('admin.group.staffPage');    
        Route::post('/{id}/getStaffList',  [GroupController::class, 'getStaffList'])->name('admin.group.getStaffList');
        Route::post('/staff/{staffId}/{groupId}/delete', [GroupController::class, 'deleteGroupStaff'])->name('admin.group.deleteGroupStaff');

        Route::post('/studentRepeat', [GroupController::class, 'studentRepeat'])->name('admin.group.studentRepeat'); 
       
    });

    Route::group(['prefix'=>'staff','middleware' => ['role:school-admin|super-admin']], function(){
        Route::get('/', [StaffController::class, 'index'])->name('admin.staff.index');
        Route::post('/getData', [StaffController::class, 'getStaffData'])->name('admin.staff.data');
        Route::get('/create', [StaffController::class, 'create'])->name('admin.staff.create');
        Route::post('/add', [StaffController::class, 'add'])->name('admin.staff.add');
        Route::get('/{id}/edit', [StaffController::class, 'edit'])->name('admin.staff.edit');
        Route::post('/{id}/update', [StaffController::class, 'update'])->name('admin.staff.update');
        Route::post('/{id}/delete', [StaffController::class, 'delete'])->name('admin.staff.delete');
    });


    Route::group(['prefix'=>'student','middleware' => ['role:school-admin|super-admin']], function(){
        Route::get('/', [StudentController::class, 'index'])->name('admin.student.index');
        Route::post('/getData', [StudentController::class, 'getSudentData'])->name('admin.student.data');
        Route::get('/create', [StudentController::class, 'create'])->name('admin.student.create');
        Route::post('/add', [StudentController::class, 'add'])->name('admin.student.add');
        Route::get('/{id}/edit', [StudentController::class, 'edit'])->name('admin.student.edit');
        Route::post('/{id}/update', [StudentController::class, 'update'])->name('admin.student.update');
        Route::post('/{id}/delete', [StudentController::class, 'delete'])->name('admin.student.delete');
        Route::post('/{student}/groupHistory', action: [StudentHistoryController::class, 'groupHistoryData'])->name('admin.student.groupHistoryData');
    });


    Route::group(['prefix'=>'payment','middleware' => ['role:school-accountant|super-admin|super-accountant']], function(){
        Route::get('/', [PaymentController::class, 'index'])->name('admin.payment.index');
        Route::post('/getData', [PaymentController::class, 'getPaymentData'])->name('admin.payment.data');
        Route::get('/filters', [PaymentController::class,'filters'])->name('admin.payment.filters');
        Route::get('/getGroups', [PaymentController::class,'getGroups'])->name('admin.payment.getGroups');
        Route::get('/getStudents/{groupId}', [PaymentController::class,'getStudents'])->name('admin.payment.getStudents');
        Route::post('/add', [PaymentController::class,'add'])->name('admin.payment.add');
        Route::post('/history', [PaymentController::class, 'history'])->name('admin.payment.history');
        Route::post('/update/{id}', [PaymentController::class, 'update'])->name('admin.payment.update');
        Route::post('/{id}/delete', [PaymentController::class, 'delete'])->name('admin.payment.delete');
        Route::get('/student/{student}', [PaymentController::class, 'studentPage'])->name('admin.payment.student')->withTrashed();;
        Route::post('/student/{student}/data', [PaymentController::class, 'studentPaymentsData'])->name('admin.payment.student.data')->withTrashed();;
        Route::get('/student/filters/{student}', [PaymentController::class,'studentFilters'])->name('admin.payment.studentfilters')->withTrashed();;
        Route::get('/getSchools', [PaymentController::class, 'getSchools'])->name('admin.payment.getSchools');
        Route::get('/getGroupsBySchool/{schoolId}', [PaymentController::class, 'getGroupsBySchool'])->name('admin.payment.getGroupsBySchool');
        Route::get('/getStudentData/{studentId}', [PaymentController::class, 'getStudentData'])->name('admin.payment.getStudentData');
        
    });

    Route::group(['prefix'=>'deleted_student','middleware' => ['role:school-admin|super-admin']], function(){
        Route::get('/', [DeletedStudentController::class, 'index'])->name('admin.deleted.students.index');
        Route::post('/getData', [DeletedStudentController::class, 'getSudentData'])->name('admin.deleted.students.data');
    });

});

