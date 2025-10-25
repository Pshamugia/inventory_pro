<?php 

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function show() { return view('auth.register'); }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'=>'required', 'email'=>'required|email|unique:users,email',
            'password'=>'required|min:6|confirmed'
        ]);

        $user = User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>bcrypt($data['password']),
        ]);

        // Default role for self-registered users (optional)
        if (method_exists($user,'assignRole')) $user->assignRole('Cashier');

        return redirect('/login')->with('ok','Account created. Please login.');
    }
}
