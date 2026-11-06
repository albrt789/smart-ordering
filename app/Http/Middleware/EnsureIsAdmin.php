<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Silakan login sebagai administrator terlebih dahulu.');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        if ($user->role !== 'admin') {
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Akun Anda saat ini (' . $user->email . ') berstatus Kasir. Silakan login dengan akun Administrator di bawah ini.'
            ]);
        }

        return $next($request);
    }
}
