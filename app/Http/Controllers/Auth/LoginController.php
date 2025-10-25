<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
public function show(){ return view('auth.login'); }


public function login(Request $r){
$cred = $r->validate([ 'email'=>'required|email', 'password'=>'required' ]);
if (Auth::attempt($cred)) { $r->session()->regenerate(); return redirect()->intended('/'); }
return back()->withErrors(['email'=>'Invalid credentials']);
}


public function logout(Request $r){ Auth::logout(); $r->session()->invalidate(); $r->session()->regenerateToken(); return redirect('/login'); }
}
