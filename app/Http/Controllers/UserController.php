<?php
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
 
    public function index() {
        return view('users.index', ['users'=>User::orderBy('name')->get()]);
    }

    public function create() { return view('users.create'); }

    public function store(Request $r) {
        $data = $r->validate([
            'name'=>'required','email'=>'required|email|unique:users,email',
            'password'=>'required|min:6',
            'role'=>'required|in:Admin,Manager,Cashier',
        ]);
        $u = User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>bcrypt($data['password'])
        ]);
        if (method_exists($u,'assignRole')) $u->assignRole($data['role']);
        return redirect()->route('users.index')->with('ok','User created');
    }
}
