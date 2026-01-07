<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    /**
     * メール認証誘導画面を表示
     *
     * @return \Illuminate\View\View
     */
    public function notice()
    {
        return view('auth.verify-email');
    }

    /**
     * メール認証を処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @param  string  $hash
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = \App\Models\User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            // 既に認証済みの場合はログイン画面にリダイレクト
            return redirect()->route('login')->with('status', 'メールアドレスは既に認証済みです。ログインしてください。');
        }

        // ハッシュを検証
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, '認証リンクが無効です');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // メール認証完了後、プロフィール設定画面にリダイレクト
        return redirect()->route('profile.edit')->with('status', 'メールアドレスの認証が完了しました。');
    }

    /**
     * 認証メールを再送信
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', '認証メールを再送信しました');
    }
}

