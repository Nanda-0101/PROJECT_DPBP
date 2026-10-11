@extends('layouts.app')

@section('page', 'login')
@section('title', 'Masuk / Daftar · CamRent')

@section('content')
<section class="section login-section">
  <div class="app-container container-narrow">
    <div class="glass-strong card login-card">

      <div class="login-tabs">
        <button type="button" class="login-tab active" data-tab="login">Masuk</button>
        <button type="button" class="login-tab" data-tab="register">Daftar Akun</button>
      </div>

      {{-- LOGIN --}}
      <form id="loginForm" class="login-form active" method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <div class="field">
          <label>Email</label>
          <input required type="email" name="email" class="input" placeholder="nama@email.com">
        </div>
        <div class="field">
          <label>Kata sandi</label>
          <input required type="password" name="password" class="input" placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary btn-block">
          <i class="bi bi-box-arrow-in-right"></i> Masuk
        </button>
      </form>

      {{-- REGISTER --}}
      <form id="registerForm" class="login-form" method="POST" action="{{ route('register') }}">
        @csrf
        <div class="field">
          <label>Daftar sebagai</label>
          <div style="display:flex;gap:10px;">
            <label class="radio-card selected" style="flex:1;">
              <input type="radio" name="role" value="user" checked> <i class="bi bi-person"></i> User
            </label>
            <label class="radio-card" style="flex:1;">
              <input type="radio" name="role" value="mitra"> <i class="bi bi-shop"></i> Mitra
            </label>
          </div>
        </div>
        <div class="field"><label>Nama lengkap</label><input required name="name" class="input" placeholder="Nama kamu"></div>
        <div class="field"><label>Email</label><input required type="email" name="email" class="input" placeholder="nama@email.com"></div>
        <div class="field"><label>No. HP</label><input required name="phone" class="input" placeholder="08xxxxxxxxxx"></div>
        <div class="field"><label>Kata sandi</label><input required type="password" name="password" class="input" placeholder="Minimal 6 karakter"></div>
        <button type="submit" class="btn btn-primary btn-block">
          <i class="bi bi-person-plus"></i> Buat Akun
        </button>
      </form>

    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Fungsi untuk mengganti tab aktif
    const switchTab = (tabName) => {
        document.querySelectorAll('.login-tab').forEach(t => {
            t.classList.toggle('active', t.dataset.tab === tabName);
        });
        document.querySelectorAll('.login-form').forEach(f => {
            f.classList.remove('active');
        });
        document.getElementById(tabName === 'login' ? 'loginForm' : 'registerForm').classList.add('active');
    };

    // Event listener untuk klik pada tab
    document.querySelectorAll('.login-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            switchTab(tab.dataset.tab);
        });
    });

    // Cek apakah URL memiliki parameter/hash tertentu (misal: #register) agar langsung pindah ke halaman daftar
    if (window.location.hash === '#register') {
        switchTab('register');
    }

    document.querySelectorAll('.radio-card').forEach(card => {
        card.addEventListener('click', () => {
            card.parentElement.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
        });
    });
});
</script>
@endpush