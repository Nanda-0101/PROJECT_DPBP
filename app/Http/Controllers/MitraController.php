<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\MitraDocument;
use App\Models\MitraProfile;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MitraController extends Controller
{
    public function index()
    {
        return view('mitra');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:120',
            'pemilik'   => 'required|string|max:120',
            'phone'     => Auth::check() ? 'nullable|string|max:20' : 'required|string|max:20',
            'email'     => Auth::check()
                ? 'nullable|email'
                : 'required|email|unique:users,email',
            'password'  => Auth::check() ? 'nullable' : 'required|string|min:6',
            'nik'       => 'required|digits:16',
            'kategori'  => 'required|string|max:50',
            'kota'      => 'required|string|max:80',
            'rekening'  => 'required|string|max:120',
            'foto'      => 'required|array|min:1',
            'foto.*'    => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $currentUser = Auth::user();
        if ($currentUser && $currentUser->mitraProfile()->exists()) {
            return redirect()->route('dashboard')
                ->with('error', 'Akun ini sudah memiliki profil mitra.');
        }

        $user = DB::transaction(function () use ($request, $currentUser) {
            $user = $currentUser ?: User::create([
                'name'     => $request->nama,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'phone'    => $request->phone,
                'role'     => 'mitra',
                'status'   => 'Menunggu Verifikasi',
            ]);

            if ($currentUser) {
                $user->update([
                    'name' => $request->nama,
                    'role' => 'mitra',
                    'status' => 'Menunggu Verifikasi',
                ]);
            }

            $profile = MitraProfile::create([
                'user_id'      => $user->id,
                'owner_name'   => $request->pemilik,
                'nik'          => $request->nik,
                'category'     => $request->kategori,
                'city'         => $request->kota,
                'bank_account' => $request->rekening,
            ]);

            // Upload foto ke lokal (storage/app/public/mitra)
            foreach ($request->file('foto') as $i => $file) {
                $path = $file->store('mitra', 'public');

                MitraDocument::create([
                    'mitra_id'   => $profile->id,
                    'photo_url'  => $path, // Menyimpan relative path: "mitra/filename.jpg"
                    'sort_order' => $i,
                ]);
            }

            return $user;
        });

        if (!$currentUser) {
            Auth::login($user);
            $request->session()->regenerate();
            $guestUserIds = $request->session()->get('guest_user_ids', []);
            $guestTicketIds = $request->session()->get('guest_ticket_ids', []);

            if ($guestUserIds || $guestTicketIds) {
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

        return redirect()->route('dashboard')
            ->with('success', 'Pendaftaran mitra berhasil! Akun sedang diverifikasi.');
    }
}