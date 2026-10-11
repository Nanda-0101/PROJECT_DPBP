@extends('layouts.app')

@section('page', 'account-settings')
@section('title', 'Pengaturan Akun · CamRent')

@section('content')
<section class="section">
  <div class="app-container container-narrow">
    <div class="section-head">
      <div>
        <span class="eyebrow badge badge-rent"><i class="bi bi-person-gear"></i> Akun Saya</span>
        <h1 style="font-size:2rem;margin-top:12px;">Pengaturan akun</h1>
        <p style="margin-top:8px;">Perbarui informasi profil dan keamanan akunmu.</p>
      </div>
    </div>

    <div class="glass-strong card">
      <form method="POST" action="{{ route('account.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="settings-profile-preview">
          @php
            $initials = collect(explode(' ', trim($user->name)))
                ->filter()
                ->take(2)
                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('');
          @endphp
          <div class="settings-avatar">
            @if($avatarUrl)
              <img src="{{ $avatarUrl }}" alt="Foto profil {{ $user->name }}" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
              <span hidden>{{ $initials }}</span>
            @else
              <span>{{ $initials }}</span>
            @endif
          </div>
          <div>
            <strong>{{ $user->name }}</strong>
            <div class="hint">{{ ucfirst($user->role) }} · {{ $user->email }}</div>
          </div>
        </div>

        <div class="field">
          <label for="avatar">Foto profil</label>
          <input id="avatar" type="file" name="avatar" class="input" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
          <div class="hint">Format JPG, PNG, atau WEBP. Maksimal 4 MB.</div>
        </div>
        <div class="field">
          <label for="name">Nama</label>
          <input id="name" name="name" class="input" value="{{ old('name', $user->name) }}" maxlength="120" required>
        </div>
        <div class="field">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" class="input" value="{{ old('email', $user->email) }}" maxlength="150" required>
        </div>
        <div class="field">
          <label for="phone">Nomor HP / WhatsApp</label>
          <input id="phone" name="phone" class="input" value="{{ old('phone', $user->phone) }}" maxlength="20">
        </div>

        <div class="divider"></div>
        <h2 style="font-size:1.15rem;margin-bottom:6px;">Ubah kata sandi</h2>
        <p class="hint" style="margin-bottom:16px;">Kosongkan jika tidak ingin mengubah kata sandi.</p>
        <div class="field">
          <label for="current_password">Kata sandi saat ini</label>
          <input id="current_password" type="password" name="current_password" class="input" autocomplete="current-password">
        </div>
        <div class="field">
          <label for="password">Kata sandi baru</label>
          <input id="password" type="password" name="password" class="input" minlength="8" autocomplete="new-password">
        </div>
        <div class="field">
          <label for="password_confirmation">Konfirmasi kata sandi baru</label>
          <input id="password_confirmation" type="password" name="password_confirmation" class="input" minlength="8" autocomplete="new-password">
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle"></i> Simpan perubahan</button>
          <a href="{{ route('dashboard') }}" class="btn btn-ghost">Kembali ke Dashboard</a>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection
