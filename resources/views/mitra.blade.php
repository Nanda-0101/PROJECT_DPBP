@extends('layouts.app')

@section('page', 'mitra')
@section('title', 'Daftar Jadi Mitra · CamRent')

@section('content')
<section class="section mitra-hero">
  <div class="app-container mitra-grid">
    <div>
      <span class="eyebrow badge badge-rent"><i class="bi bi-briefcase"></i> Untuk Penyedia Alat</span>
      <h1 style="font-size:2.1rem;margin:14px 0 10px;">Jadikan alatmu penghasil cuan sebagai Mitra CamRent</h1>
      <p style="max-width:480px;">Sewakan peralatan kreatifmu ke ribuan kreator di seluruh Indonesia.</p>
      <div class="mitra-benefit-grid">
        <div class="mitra-benefit glass"><i class="bi bi-graph-up-arrow"></i><span>Penghasilan tambahan</span></div>
        <div class="mitra-benefit glass"><i class="bi bi-shield-check"></i><span>Jaminan penyewa</span></div>
        <div class="mitra-benefit glass"><i class="bi bi-wallet2"></i><span>Pembayaran aman</span></div>
        <div class="mitra-benefit glass"><i class="bi bi-headset"></i><span>Didampingi CS</span></div>
      </div>
    </div>

    <div class="glass-strong card mitra-form-card">
      <h3 style="margin-bottom:4px;">Formulir pendaftaran mitra</h3>
      <p style="margin-bottom:18px;">Isi data berikut, verifikasi dalam 1x24 jam.</p>
      <form method="POST" action="{{ route('mitra.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="field"><label>Nama akun / usaha</label><input required name="nama" class="input" value="{{ old('nama', auth()->user()->name ?? '') }}" placeholder="Contoh: Alpha Rental Studio"></div>
        <div class="responsive-field-row" style="display:flex;gap:12px;">
          <div class="field" style="flex:1;"><label>Nama pemilik</label><input required name="pemilik" class="input"></div>
          <div class="field" style="flex:1;"><label>No. HP / WhatsApp</label><input {{ auth()->check() ? 'readonly' : 'required' }} name="phone" class="input" value="{{ old('phone', auth()->user()->phone ?? '') }}"></div>
        </div>
        <div class="field"><label>Email</label><input {{ auth()->check() ? 'readonly' : 'required' }} type="email" name="email" class="input" value="{{ old('email', auth()->user()->email ?? '') }}"></div>
        @guest
          <div class="field"><label>Kata sandi akun</label><input required type="password" name="password" minlength="6" class="input" placeholder="Minimal 6 karakter"></div>
        @endguest
        <div class="field"><label>NIK KTP</label><input required maxlength="16" name="nik" class="input" placeholder="16 digit NIK"></div>
        <div class="field"><label>Kategori alat</label>
          <select name="kategori" class="input">
            <option>Kamera</option><option>Lensa</option><option>Lighting</option>
            <option>Audio</option><option>Gimbal</option><option>Tripod</option><option>Campuran</option>
          </select>
        </div>
        <div class="field"><label>Kota domisili</label><input required name="kota" class="input"></div>
        <div class="field"><label>No. rekening</label><input required name="rekening" class="input" placeholder="Nama bank & nomor rekening"></div>
        <div class="field">
          <label>Foto alat (wajib, minimal 1)</label>
          <input type="file" name="foto[]" class="input" accept="image/*" multiple required>
          <div class="hint"><i class="bi bi-info-circle"></i> Unggah minimal 1 foto asli.</div>
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-weight:500;margin-bottom:14px;">
          <input type="checkbox" required> Saya setuju dengan S&K kemitraan CamRent.
        </label>
        <button type="submit" class="btn btn-primary btn-block">
          <i class="bi bi-send-check"></i> Daftar Sebagai Mitra
        </button>
      </form>
    </div>
  </div>
</section>
@endsection