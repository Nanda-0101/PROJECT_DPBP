<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        // Demo: password plain (sesuai seed). Untuk produksi, pakai Hash::check.
        if (!$user || $user->password !== $request->password) {
            return back()->with('error', 'Email atau kata sandi salah.');
        }

        Auth::login($user, $request->boolean('remember'));

        return redirect()->route('dashboard')
            ->with('success', "Selamat datang, {$user->name}!");
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20',
            'password' => 'required|min:6',
            'role'     => 'required|in:user,mitra',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password, // produksi: Hash::make
            'phone'    => $request->phone,
            'role'     => $request->role,
            'status'   => $request->role === 'mitra' ? 'Menunggu Verifikasi' : 'Aktif',
        ]);

        Auth::login($user);

        $redirect = $request->role === 'mitra'
            ? route('mitra.index')
            : route('dashboard');

        return redirect($redirect)
            ->with('success', 'Akun berhasil dibuat!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Kamu berhasil keluar.');
    }
}