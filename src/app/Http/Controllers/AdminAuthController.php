<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    /**
     * 管理者ログイン画面
     */
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    /**
     * 管理者ログイン処理
     */
    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            if (Auth::user()->role !== 'admin') {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'ログイン情報が登録されていません',
                ]);
            }

            $request->session()->regenerate();

            return redirect('/admin');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
