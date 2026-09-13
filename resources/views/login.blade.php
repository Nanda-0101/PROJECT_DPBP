@extends('layouts.app')

@section('page', 'login')
@section('title', 'Masuk / Daftar · CamRent')

@section('content')
<section class="section login-section">
  <div class="app-container container-narrow">
    <div class="glass-strong card login-card">
      <div class="login-tabs">
        <button class="login-tab active" data-tab="login">Masuk</button>
        <button class="login-tab" data-tab="register">Daftar Akun</button>
      </div>

      <form id="loginForm" class="login-form active">
        <div class="field"><label>Email</label><input required type="email" class="input" id="loginEmail" placeholder="nama@email.com"></div>
        <div class="field"><label>Kata sandi</label><input required type="password" class="input" id="loginPassword" placeholder="••••••••"></div>
        <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
        <div class="hint" style="margin-top:14px;text-align:center;">
          Demo akun user: <b>user@camrent.id</b> / user123<br>
          Demo akun mitra: <b>mitra@camrent.id</b> / mitra123
        </div>
      </form>

      <form id="registerForm" class="login-form">
        <div class="field"><label>Daftar sebagai</label>
          <div style="display:flex;gap:10px;">
            <label class="radio-card selected" style="flex:1;" data-role="user"><input type="radio" name="regRole" value="user" checked> <i class="bi bi-person"></i> User</label>
            <label class="radio-card" style="flex:1;" data-role="mitra"><input type="radio" name="regRole" value="mitra"> <i class="bi bi-shop"></i> Mitra</label>
          </div>
        </div>
        <div class="field"><label>Nama lengkap</label><input required class="input" id="regName" placeholder="Nama kamu"></div>
        <div class="field"><label>Email</label><input required type="email" class="input" id="regEmail" placeholder="nama@email.com"></div>
        <div class="field"><label>No. HP</label><input required class="input" id="regPhone" placeholder="08xxxxxxxxxx"></div>
        <div class="field"><label>Kata sandi</label><input required type="password" class="input" id="regPassword" placeholder="Minimal 6 karakter"></div>
        <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-person-plus"></i> Buat Akun</button>
      </form>
    </div>
  </div>
</section>
@endsection