<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\UserRequest\UserStoreRequest; 
use App\Http\Requests\UserRequest\UserUpdateRequest; 
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCreatedMail;
use App\Models\SchoolName;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Services\UserService;
use Throwable;


class UserController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService){
        $this->userService = $userService;
    }
    public function index(){
          return view('admin.user.index');
    }

    public function create(){
        $roles = Role::where('name', '!=', 'super-admin')->get();
        $schoolNameData = SchoolName::all();
        $is_create = true;
        return view('admin.user.form', compact('schoolNameData', 'roles','is_create'));
    }


    public function getUserData(Request $request){
        $result = $this->userService->getUserData($request);
        return response()->json($result);
    }

    public function add(UserStoreRequest $request){
        try{

            $validated = $request->validated();
            $defaultPassword =  Str::random(10); //'12345';
            

            $user = User::create([
                'first_name'    => $validated['first_name'],
                'last_name'     => $validated['last_name'],
                'father_name'   => $validated['father_name'],
                'email'         => $validated['email'],
                'school_id'     => $validated['school_id'],
                'password'      => Hash::make($defaultPassword),
            ]);

            $user->assignRole($request->role_name);
            Mail::to($user->email)->queue(new UserCreatedMail($user, $defaultPassword));

            return response()->json(['status' => 1, 'message' => 'Գործողությունը կատարված է']);  

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }

    public function edit($id) {
        $user = User::findOrFail($id);
        $roles = Role::where('name', '!=', 'super-admin')->get();
        $schoolNameData = SchoolName::all();
        $userRole = $user->getRoleNames()->first(); 
        $is_create = false;
        return view('admin.user.form', compact('user','roles', 'schoolNameData', 'userRole', 'is_create')); 
    }


    public function update(UserUpdateRequest $request, $id) {            
        try{

            $validated = $request->validated();
            $user = User::findOrFail($id);   
            $user->update([
                'first_name'    => $validated['first_name'],
                'last_name'     => $validated['last_name'],
                'father_name'   => $validated['father_name'],
                'email'         => $validated['email'],
                'password'      => Hash::make($validated['password']),
                'school_id'     => $validated['school_id'],  
            ]);


             if ($request->filled('role_name')) {
                $user->syncRoles([$request->input('role_name')]);
            }

            return response()->json(['status' => 1, 'message' => 'Գործողությունը կատարված է']);

        }catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  

    }


}
