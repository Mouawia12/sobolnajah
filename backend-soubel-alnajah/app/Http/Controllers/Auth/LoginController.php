<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    public function showLoginForm()
    {
        $this->ensureLocalAdminAccount();

        return view('auth.login');
    }

    protected function validateLogin(Request $request): void
    {
        $request->validate([
            $this->username() => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            $this->username() . '.required' => trans('login.validation_email_required'),
            $this->username() . '.email' => trans('login.validation_email_invalid'),
            'password.required' => trans('login.validation_password_required'),
        ]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $email = (string) $request->input($this->username());
        $userExists = User::query()->where($this->username(), $email)->exists();

        throw ValidationException::withMessages([
            $userExists ? 'password' : $this->username() => [
                $userExists
                    ? trans('login.error_password_invalid')
                    : trans('login.error_email_not_found'),
            ],
        ]);
    }

    public function showAccountantLoginForm()
    {
        return view('auth.login_accountant');
    }

    public function loginAccountant(Request $request)
    {
        $this->ensureLocalAdminAccount();

        $request->validate([
            $this->username() => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only($this->username(), 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                $this->username() => [trans('auth.failed')],
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        if (!$user || !$user->hasRole('accountant')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                $this->username() => ['هذا الحساب ليس حساب محاسب مالي.'],
            ]);
        }

        return redirect()->intended(route('accountant.dashboard'));
    }

    public function login(Request $request)
    {
        $this->ensureLocalAdminAccount();

        return $this->traitLogin($request);
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

    private function traitLogin(Request $request)
    {
        return $this->sendLoginResponseIfAuthenticated($request);
    }

    private function sendLoginResponseIfAuthenticated(Request $request)
    {
        $this->validateLogin($request);

        if (method_exists($this, 'hasTooManyLoginAttempts')
            && $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    private function ensureLocalAdminAccount(): void
    {
        if (!app()->environment('local')) {
            return;
        }

        if (User::query()->exists()) {
            return;
        }

        Role::query()->firstOrCreate(['name' => 'admin']);

        $admin = User::query()->updateOrCreate([
            'email' => (string) env('SITE_ADMIN_EMAIL', 'admin@sobolnajah.com'),
        ], [
            'name' => [
                'fr' => (string) env('SITE_ADMIN_NAME_FR', 'Site Admin'),
                'ar' => (string) env('SITE_ADMIN_NAME_AR', 'مسؤول الموقع'),
                'en' => (string) env('SITE_ADMIN_NAME_EN', 'Site Admin'),
            ],
            'password' => Hash::make((string) env('SITE_ADMIN_PASSWORD', '12345678')),
            'must_change_password' => filter_var(env('SITE_ADMIN_FORCE_PASSWORD_CHANGE', false), FILTER_VALIDATE_BOOL),
        ]);

        if (!$admin->hasRole('admin')) {
            $admin->attachRole('admin');
        }
    }
}
