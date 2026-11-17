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
use Illuminate\Validation\Rule;

class MitraController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $hasProfile = $user?->mitraProfile()->exists() ?? false;
        $profile = $user?->isMitra() ? $user->mitraProfile : null;

        return view('mitra', [
            'mitraProfile' => $profile,
            'canSubmitMitra' => !$user
                || ($user->role === 'user' && !$hasProfile)
                || ($user->isMitra() && $profile !== null),
        ]);
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        abort_if($currentUser?->role === 'admin', 403);

        if ($currentUser && $currentUser->mitraProfile()->exists()) {
            return redirect()->route('dashboard')
                ->with('error', 'Gunakan formulir update untuk mengubah profil mitra.');
        }

        $validated = $request->validate([
            'nama'      => 'required|string|max:120',
            'pemilik'   => 'required|string|max:120',
            'phone'     => Auth::check() ? 'nullable|string|max:20' : 'required|string|max:20',
            'email'     => Auth::check()
                ? 'nullable|email|max:150'
                : 'required|email|max:150|unique:users,email',
            'password'  => Auth::check() ? 'nullable' : 'required|string|min:6',
            'nik'       => ['required', 'digits:16', Rule::unique('mitra_profiles', 'nik')],
            'kategori'  => 'required|string|max:50',
            'kota'      => 'required|string|max:80',
            'rekening'  => 'required|string|max:120',
            'foto'      => 'required|array|min:1|max:256',
            'foto.*'    => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $user = DB::transaction(function () use ($request, $currentUser, $validated) {
            $user = $currentUser ?: User::create([
                'name'     => $validated['nama'],
                'email'    => $validated['email'],
                'password' => Hash::make($request->password),
                'phone'    => $validated['phone'] ?? null,
                'role'     => 'mitra',
                'status'   => 'Terverifikasi',
            ]);

            if ($currentUser) {
                $user->update([
                    'name' => $validated['nama'],
                    'role' => 'mitra',
                    'status' => 'Terverifikasi',
                ]);
            }

            $profile = MitraProfile::create([
                'user_id'      => $user->id,
                'owner_name'   => $validated['pemilik'],
                'nik'          => $validated['nik'],
                'category'     => $validated['kategori'],
                'city'         => $validated['kota'],
                'bank_account' => $validated['rekening'],
                'verified_at'  => now(),
            ]);

            // Upload foto ke lokal (storage/app/public/mitra)
            foreach ($request->file('foto', []) as $i => $file) {
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
            ->with('success', 'Pendaftaran mitra berhasil! Akunmu sekarang aktif sebagai mitra.');
    }

    public function update(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isMitra(), 403);

        DB::transaction(function () use ($request, $user) {
            $profile = $user->mitraProfile()->lockForUpdate()->firstOrFail();
            $nextPhotoOrder = (int) ($profile->documents()->max('sort_order') ?? -1) + 1;
            $maxNewPhotos = max(0, 256 - $nextPhotoOrder);
            $validated = $request->validate([
                'nama'      => 'required|string|max:120',
                'pemilik'   => 'required|string|max:120',
                'nik'       => ['required', 'digits:16', Rule::unique('mitra_profiles', 'nik')->ignore($profile->id)],
                'kategori'  => 'required|string|max:50',
                'kota'      => 'required|string|max:80',
                'rekening'  => 'required|string|max:120',
                'foto'      => ['sometimes', 'array', 'min:1', 'max:'.$maxNewPhotos],
                'foto.*'    => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);

            $profile->fill([
                'owner_name'   => $validated['pemilik'],
                'nik'          => $validated['nik'],
                'category'     => $validated['kategori'],
                'city'         => $validated['kota'],
                'bank_account' => $validated['rekening'],
            ]);

            $photos = $request->file('foto', []);
            $profile->verified_at ??= now();
            $profile->save();
            $user->name = $validated['nama'];
            $user->status = 'Terverifikasi';
            $user->save();

            foreach (array_values($photos) as $index => $file) {
                $path = $file->store('mitra', 'public');

                MitraDocument::create([
                    'mitra_id'   => $profile->id,
                    'photo_url'  => $path,
                    'sort_order' => $nextPhotoOrder + $index,
                ]);
            }
        });

        return redirect()->route('mitra.index')
            ->with('success', 'Profil mitra berhasil diperbarui.');
    }
}