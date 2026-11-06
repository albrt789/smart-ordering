<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsCashier
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('cashier.login')->with('error', 'Silakan login sebagai kasir terlebih dahulu.');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('cashier.login')->with('error', 'Akun kasir Anda telah dinonaktifkan.');
        }

        if ($user->role !== 'cashier' && $user->role !== 'admin') {
            return redirect()->route('cashier.login')->withErrors([
                'email' => 'Akun Anda tidak memiliki hak akses kasir. Silakan login dengan akun Kasir.'
            ]);
        }

        return $next($request);
    }
}
