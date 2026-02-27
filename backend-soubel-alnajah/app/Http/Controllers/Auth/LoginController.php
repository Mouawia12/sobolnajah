<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function validateLogin(Request $request): void
    {
        $request->validate([
            $this->username() => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            $this->username() . '.required' => 'يرجى إدخال البريد الإلكتروني.',
            $this->username() . '.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'password.required' => 'يرجى إدخال كلمة المرور.',
        ]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $email = (string) $request->input($this->username());
        $userExists = User::query()->where($this->username(), $email)->exists();

        throw ValidationException::withMessages([
            $userExists ? 'password' : $this->username() => [
                $userExists
                    ? 'كلمة المرور غير صحيحة. حاول مرة أخرى.'
                    : 'البريد الإلكتروني غير موجود.',
            ],
        ]);
    }

    /**
     * Logout trait
     *
     * @author Yugo <dedy.yugo.purwanto@gmail.com>
     * @param  Request $request
     * @return void         
     */
    protected function logout(Request $request,$lang)
    {

        $lang1=$lang;
        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        return redirect("/".$lang1);


  
    }
}
