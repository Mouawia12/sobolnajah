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
            'password.change.page',
            'admin.password.change.page',
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

        // التلميذ والولي يغيّران كلمة المرور من صفحة ملفهم (home)؛ بقية الأدوار
        // تُوجَّه لصفحة تغيير كلمة المرور، وإلا تحدث حلقة إعادة توجيه لا تنتهي
        // (home ← لوحة الأستاذ/المحاسب ← home ...).
        $target = $user->hasRole(['student', 'guardian']) ? 'home' : 'password.change.page';

        return redirect()->route($target)->withErrors([
            'error' => 'يجب تغيير كلمة المرور قبل متابعة استخدام النظام.',
        ]);
    }
}
