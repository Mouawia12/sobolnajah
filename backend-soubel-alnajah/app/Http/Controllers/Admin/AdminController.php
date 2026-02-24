<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function indexadmin()
        {
            if(Auth::user()->hasRole('admin')){
                return view('admin.home');
            }else {
                return view('auth.login');
            }
            
        }
}
