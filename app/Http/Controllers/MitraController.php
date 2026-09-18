<?php

namespace App\Http\Controllers;

use App\Models\MitraDocument;
use App\Models\MitraProfile;
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
            'phone'     => 'required|string|max:20',
            'email'     => 'required|email|unique:users,email',
            'nik'       => 'required|digits:16',
            'kategori'  => 'required|string|max:50',
            'kota'      => 'required|string|max:80',
            'rekening'  => 'required|string|max:120',
            'foto'      => 'required|array|min:1',
            'foto.*'    => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->nama,
                'email'    => $request->email,
                'password' => Hash::make('mitra123'), // Terenkripsi aman
                'phone'    => $request->phone,
                'role'     => 'mitra',
                'status'   => 'Menunggu Verifikasi',
            ]);

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

            Auth::login($user);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Pendaftaran mitra berhasil! Akun sedang diverifikasi.');
    }
}