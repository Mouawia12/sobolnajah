<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ConfirmsPasswords;
use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Auth;
class ConfirmPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Confirm Password Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password confirmations and
    | uses a simple trait to include the behavior. You're free to explore
    | this trait and override any functions that require customization.
    |
    */

    use ConfirmsPasswords;

    /**
     * Where to redirect users when the intended url fails.
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
        $this->middleware('auth');
    }

    public function studentChangePassword(Request $request){
            $validated = $request->validate([
                'password' => ['required', 'string'],
                'newPassword' => ['required', 'string', 'min:8', 'different:password'],
                'confirmNewPassword' => ['required', 'same:newPassword'],
            ]);

            if (!(Hash::check($request->get('password'), Auth::user()->password))) {
                return redirect()->back()->withErrors(['كلمة السر القديمة غير صحيحة']);
            }
    
            if(strcmp($request->get('newPassword'),$request->get('confirmNewPassword'))){
                // Current password and new password same
                return redirect()->back()->withErrors(['يرجى كتابة نفس كلمة السر']);
            }
    
            // $validatedData = $request->validate([
            //     'newPassword' => 'required',
            //     'confirmNewPassword' => 'required|string|min:8|int',
            // ]);
    
            //Change Password
            $user = Auth::user();
            $user->password = Hash::make($request->get('newPassword'));
            $user->must_change_password = false;
            $user->save();
    
            return redirect()->back()->withSuccess("b");
        

        
    }




}
