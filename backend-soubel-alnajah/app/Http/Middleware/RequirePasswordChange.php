<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequirePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->must_change_password) {
            return $next($request);
        }

        $allowedRoutes = [
            'home',
            'changePassword',
            'logout',
            'login',
            'password.request',
            'password.email',
            'password.reset',
            'password.update',
        ];

        if ($request->routeIs($allowedRoutes)) {
            return $next($request);
        }

        return redirect()->route('home')->withErrors([
            'error' => 'يجب تغيير كلمة المرور قبل متابعة استخدام النظام.',
        ]);
    }
}
