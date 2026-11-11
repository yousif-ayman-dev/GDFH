<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBanned
{
    /**
     * Handle an incoming request.
     * Prevents banned users from accessing the application across active sessions and subsequent requests.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isBanned()) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'تم حظر هذا الحساب من قِبل إدارة المنصة. يرجى التواصل مع الدعم الفني.',
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'تم حظر هذا الحساب من قِبل إدارة المنصة. يرجى التواصل مع الدعم الفني.',
            ]);
        }

        return $next($request);
    }
}
