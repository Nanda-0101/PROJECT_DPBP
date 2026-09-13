@extends('layouts.app')

@section('page', 'mitra')
@section('title', 'Daftar Jadi Mitra · CamRent')

@section('content')
<section class="section mitra-hero">
  <div class="app-container mitra-grid">
    <div>
      <span class="eyebrow badge badge-rent"><i class="bi bi-briefcase"></i> Untuk Penyedia Alat</span>
      <h1 style="font-size:2.1rem;margin:14px 0 10px;">Jadikan alatmu penghasil cuan sebagai Mitra CamRent</h1>
      <p style="max-width:480px;">Sewakan atau jual peralatan kreatifmu ke ribuan kreator di seluruh Indonesia. Kelola pesanan, pantau pendapatan, semua dari satu dashboard.</p>
      <div class="mitra-benefit-grid">
        <div class="mitra-benefit glass"><i class="bi bi-graph-up-arrow"></i><span>Penghasilan tambahan dari alat menganggur</span></div>
        <div class="mitra-benefit glass"><i class="bi bi-shield-check"></i><span>Jaminan KTP/SIM penyewa di setiap transaksi</span></div>
        <div class="mitra-benefit glass"><i class="bi bi-wallet2"></i><span>Pembayaran otomatis tercatat & aman</span></div>
        <div class="mitra-benefit glass"><i class="bi bi-headset"></i><span>Didampingi tim CS bila ada kendala</span></div>
      </div>
    </div>

    <div class="glass-strong card mitra-form-card">
      <h3 style="margin-bottom:4px;">Formulir pendaftaran mitra</h3>
      <p style="margin-bottom:18px;">Isi data berikut, tim kami akan verifikasi dalam 1x24 jam.</p>
      <form id="mitraForm">
        <div class="field"><label>Nama usaha / brand rental</label><input required class="input" id="mNama" placeholder="Contoh: Alpha Rental Studio"></div>
        <div style="display:flex;gap:12px;">
          <div class="field" style="flex:1;"><label>Nama pemilik</label><input required class="input" id="mPemilik" placeholder="Nama lengkap"></div>
          <div class="field" style="flex:1;"><label>No. HP / WhatsApp</label><input required class="input" id="mPhone" placeholder="08xxxxxxxxxx"></div>
        </div>
        <div class="field"><label>Email</label><input required type="email" class="input" id="mEmail" placeholder="nama@email.com"></div>
        <div class="field"><label>NIK KTP</label><input required maxlength="16" class="input" id="mNik" placeholder="16 digit NIK"></div>
        <div class="field"><label>Kategori alat yang disediakan</label>
          <select class="input" id="mKategori">
            <option>Kamera</option><option>Lensa</option><option>Lighting</option><option>Audio</option><option>Gimbal</option><option>Tripod</option><option>Campuran</option>
          </select>
        </div>
        <div class="field"><label>Kota domisili</label><input required class="input" id="mKota" placeholder="Contoh: Jakarta Selatan"></div>
        <div class="field"><label>No. rekening (untuk pencairan dana)</label><input required class="input" id="mRekening" placeholder="Nama bank & nomor rekening"></div>
        <div class="field">
          <label>Foto alat yang akan disewakan/dijual (wajib, minimal 1)</label>
          <input type="file" class="input" id="mFoto" accept="image/*" multiple>
          <div class="hint"><i class="bi bi-info-circle"></i> Unggah minimal 1 foto asli alat pertamamu sebagai contoh untuk ditampilkan di marketplace.</div>
          <div id="mFotoPreview" class="photo-preview-row"></div>
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-weight:500;margin-bottom:14px;">
          <input type="checkbox" required id="mAgree"> Saya setuju dengan syarat & ketentuan kemitraan CamRent.
        </label>
        <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-send-check"></i> Daftar Sebagai Mitra</button>
      </form>
    </div>
  </div>
</section>
@endsection