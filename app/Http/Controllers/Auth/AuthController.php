<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return view
     */
    public function showLogin() 
    {
        return view('login.login_form');
    }

    /**
     * @param App\Http\Requests\LoginFormRequest
     */
    public function login(LoginFormRequest $request) 
    {
        $credentials = $request->only('email', 'password');

        //アカウント受け取り
        $user = $this->user->getUserByEmail($credentials['email']);

        if (!is_null($user)) {
            //アカウントがロックされているか判定
            if($this->user->isAccountLocked($user)) {
                return back()->withErrors([
                    'danger' => 'アカウントがロックされています。',
                ]);
            }

            //ログイン処理
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();

                //エラーカウントをゼロにする
                $this->user->resetErrorCount($user);

                return redirect()->route('home')->with('success', 'ログイン成功しました！');
            }

            //エラーカウントを１増やす
            $user->error_count = $this->user->addErrorCount($user->error_count);

            //エラーカウントが１以上の場合はアカウントをロックする
            if ($this->user->lockAccount($user)) {
                return back()->withErrors([
                    'danger' => 'アカウントがロックされました。',
                ]);
            }

            $user->save();
        }

        return back()->withErrors([
            'danger' => 'メールアドレスかパスワードが間違っています。',
        ]);
    }



    /**
     * ユーザーをアプリケーションからログアウトさせる
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login.show')->with('danger', 'ログアウトしました！');
    }
}