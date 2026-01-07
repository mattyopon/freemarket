<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;

class LoginController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \App\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // メール認証が完了していない場合は、メール認証誘導画面にリダイレクト
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            // 商品一覧画面にリダイレクト
            return redirect()->route('items.index');
        }

        return back()->withErrors([
            'login' => 'ログイン情報が登録されていません',
        ])->withInput($request->only('email'));
    }
}

