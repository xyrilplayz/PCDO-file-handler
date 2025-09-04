<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class xy extends Controller
{
    function login()
    {
        if (Auth::check()) {
            return redirect(route('home'));
        }

        return view('login');
    }


    function registration()
    {
        if (Auth::check()) {
            return redirect(route('home'));
        }
        return view('registration');
    }

    function loginPost(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->intended(route('home'));
        }
        return redirect(route('login'))->with("error", "Incorrect login Details");
    }

    function registrationPost(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['password'] = Hash::make($request->password);
        $users = User::create($data);
        if (!$users) {
            return redirect(route(name: 'registration'))->with("error", "Incorrect Registration Details");
        }
        return redirect(route('login'))->with("success", "Registered in");
    }

    function logout()
    {
        Session::flush();
        Auth::guard('web')->logout();
        return redirect(route('login'));

    }

    function dashboard()
    {
       return view('dashboard');
    }
}
