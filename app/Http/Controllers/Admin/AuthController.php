<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\LoginRequest;

class AuthController extends Controller
{
    public function login() {
        return view('admin.auth.login');
    }

    public function postLogin(LoginRequest $request) {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        $remember = $request->remember ? true : false;
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->route('admin.categories.index');
        } else {
            return redirect()->back()->withInput()->with('msg', 'Email hoặc mật khẩu không chính xác');
        }
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
