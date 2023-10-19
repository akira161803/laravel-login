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
        $user = User::where('email', '=', $credentials['email'])->first();

        if (!is_null($user)) {
            //エラーカウントチェック
            if($user->locked_flg === 1) {
                return back()->withErrors([
                    'danger' => 'アカウントがロックされています。',
                ]);
            }

            //ログイン処理
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                //エラーカウントをゼロにする
                if ($user->error_count > 0) {
                    $user->error_count = 0;
                    $user->save();
                }

                return redirect()->route('home')->with('success', 'ログイン成功しました！');
            }

            //エラーカウントを１増やす
            $user->error_count = $user->error_count + 1;
            //エラーカウントが１以上の場合はアカウントをロックする
            if ($user->error_count > 5) {
                $user->locked_flg = 1;
                $user->save();
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