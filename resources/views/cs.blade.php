@extends('layouts.app')

@section('page', 'cs')
@section('title', 'Pusat Bantuan (CS) · CamRent')

@section('content')
<header class="section" style="padding-bottom:10px;">
  <div class="app-container">
    <span class="eyebrow badge badge-rent"><i class="bi bi-headset"></i> Customer Service</span>
    <h1 style="font-size:2.1rem;margin:12px 0 8px;">Ada kendala? Kami bantu selesaikan.</h1>
    <p style="max-width:600px;">Salah booking, salah tanggal, kendala pembayaran, atau alat bermasalah — laporkan di sini dan tim kami akan segera merespons.</p>
  </div>
</header>

<section class="app-container cs-layout">
  <div class="glass card cs-form-card">
    <h3 style="margin-bottom:4px;"><i class="bi bi-flag gradient-text"></i> Ajukan Pengaduan</h3>
    <p style="margin-bottom:18px;">Ceritakan kendalamu, sertakan nomor pesanan jika ada.</p>
    <form id="csForm">
      <div class="field"><label>Kategori pengaduan</label>
        <select class="input" id="csCategory">
          <option>Salah booking / tanggal</option>
          <option>Kendala pembayaran</option>
          <option>Alat rusak / tidak sesuai</option>
          <option>Keterlambatan pengiriman</option>
          <option>Lainnya</option>
        </select>
      </div>
      <div class="field"><label>Pesanan terkait (opsional)</label>
        <select class="input" id="csBooking"><option value="">-- Tidak terkait pesanan tertentu --</option></select>
      </div>
      <div class="field"><label>Nama pelapor</label><input required class="input" id="csName" placeholder="Nama lengkap"></div>
      <div class="field"><label>No. HP / Email untuk dihubungi</label><input required class="input" id="csContact" placeholder="08xxxxxxxxxx atau email"></div>
      <div class="field"><label>Deskripsi kendala</label><textarea required class="input" id="csDesc" rows="4" placeholder="Jelaskan detail masalah yang kamu alami..."></textarea></div>
      <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-send"></i> Kirim Pengaduan</button>
    </form>
  </div>

  <div class="cs-side">
    <div class="glass card cs-chat-card">
      <h4 style="margin-bottom:10px;"><i class="bi bi-chat-dots-fill gradient-text"></i> Live Chat CS</h4>
      <div class="chat-window" id="chatWindow"></div>
      <form id="chatForm" class="chat-input-row">
        <input class="input" id="chatInput" placeholder="Ketik pesan..." autocomplete="off">
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send"></i></button>
      </form>
    </div>

    <div class="glass card" style="margin-top:20px;">
      <h4 style="margin-bottom:14px;"><i class="bi bi-list-check gradient-text"></i> Riwayat Tiket Saya</h4>
      <div id="ticketList"></div>
    </div>
  </div>
</section>

<section class="app-container" id="faq" style="margin-top:20px;">
  <div class="section-head"><h2 style="font-size:1.4rem;"><i class="bi bi-question-circle-fill gradient-text"></i> Pertanyaan Umum</h2></div>
  <div id="faqAccordion" class="faq-accordion"></div>
</section>
@endsection