<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;      
use App\Http\Requests\LoginRequest;       

class AuthController extends Controller
{
    public function index(){

        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');

    }


public function login(LoginRequest $request){
    
    $credentials = $request->only('email', 'password');

    $remember = $request->boolean('remember');
    
    if (Auth::attempt($credentials, $remember)) {
        $request->session()->regenerate();
        return redirect()->intended('/admin/dashboard');
    }

    return back()->withErrors([
        'errors' => 'Սխալ էլ․ հասցե կամ գաղտնաբառ',
    ]);
}


public function logout(Request $request){
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
}




}
