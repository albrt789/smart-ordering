<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // ================= CASHIER AUTH =================
    public function showCashierLogin()
    {
        if (Auth::check() && Auth::user()->isCashier()) {
            return redirect()->route('cashier.dashboard');
        }
        return view('auth.cashier_login');
    }

    public function cashierLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun kasir ini telah dinonaktifkan oleh administrator.']);
            }

            if ($user->role !== 'cashier' && $user->role !== 'admin') {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda tidak memiliki hak akses kasir.']);
            }

            $request->session()->regenerate();
            return redirect()->route('cashier.dashboard');
        }

        return back()->withErrors(['email' => 'Kombinasi email atau password salah.'])->withInput();
    }

    public function cashierLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('cashier.login')->with('success', 'Anda telah berhasil logout.');
    }

    // ================= ADMIN AUTH =================
    public function showAdminLogin()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.admin_login');
    }

    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun administrator ini dinonaktifkan.']);
            }

            if ($user->role !== 'admin') {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda bukan akun administrator.']);
            }

            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Kombinasi email atau password admin salah.'])->withInput();
    }

    public function adminLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login')->with('success', 'Anda telah berhasil keluar dari panel admin.');
    }
}

