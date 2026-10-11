<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $passwordIsHashed = $user
            && password_get_info($user->password)['algoName'] !== 'unknown';
        $passwordMatches = $user && ($passwordIsHashed
            ? Hash::check($request->password, $user->password)
            : hash_equals($user->password, $request->password));

        if (!$passwordMatches) {
            return back()->with('error', 'Email atau kata sandi salah.');
        }

        if (!$passwordIsHashed) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $this->claimGuestActivity($request, $user);

        return redirect()->route('dashboard')
            ->with('success', "Selamat datang, {$user->name}!");
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:user,mitra',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone,
            'role'     => $request->role,
            'status'   => $request->role === 'mitra' ? 'Menunggu Verifikasi' : 'Aktif',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $this->claimGuestActivity($request, $user);

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

    private function claimGuestActivity(Request $request, User $user): void
    {
        $guestUserIds = $request->session()->get('guest_user_ids', []);
        $guestTicketIds = $request->session()->get('guest_ticket_ids', []);

        if (!$guestUserIds && !$guestTicketIds) {
            return;
        }

        DB::transaction(function () use ($guestUserIds, $guestTicketIds, $user) {
            if ($guestUserIds) {
                Booking::whereIn('user_id', $guestUserIds)->update(['user_id' => $user->id]);
                Ticket::whereIn('user_id', $guestUserIds)->update(['user_id' => $user->id]);
            }

            if ($guestTicketIds) {
                Ticket::whereIn('id', $guestTicketIds)->update(['user_id' => $user->id]);
            }
        });

        $request->session()->forget(['guest_user_ids', 'guest_ticket_ids']);
    }
}