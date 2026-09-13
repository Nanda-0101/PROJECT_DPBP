@extends('layouts.app')

@section('page', 'home')
@section('title', 'CamRent · Sewa Kamera & Jual Beli Peralatan Untuk Content Creator')

@section('content')
<header class="hero section">
  <div class="app-container hero-grid">
    <div class="hero-copy">
      <span class="eyebrow badge badge-rent"><i class="bi bi-lightning-charge-fill"></i> #1 marketplace kreator</span>
      <h1>Sewa & beli peralatan kreatif, <span class="gradient-text">tanpa ribet.</span></h1>
      <p class="hero-sub">Kamera, lensa, lighting, mic, sampai gimbal — booking online, bayar aman, dan siap dipakai hari ini. Kamu penyewa atau mitra penyedia alat, semua ada di satu tempat.</p>
      <div class="hero-cta">
        <a href="{{ url('/booking') }}" class="btn btn-primary"><i class="bi bi-calendar-check"></i> Booking Kamera</a>
        <a href="{{ url('/marketplace') }}" class="btn btn-secondary"><i class="bi bi-shop"></i> Jelajah Marketplace</a>
      </div>
      <div class="hero-stats" id="heroStats"></div>
    </div>
    <div class="hero-visual glass">
      <div class="hero-visual-head">
        <span class="tag"><i class="bi bi-fire"></i> Lagi trending</span>
      </div>
      <div class="hero-mini-grid" id="heroMiniGrid"></div>
    </div>
  </div>
</header>

<section class="section" style="padding-top:10px;">
  <div class="app-container">
    <div class="section-head">
      <h2><i class="bi bi-grid-3x3-gap-fill gradient-text"></i> Kategori populer</h2>
      <a href="{{ url('/marketplace') }}" class="link-more">Lihat semua <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="cat-grid" id="catGrid"></div>
  </div>
</section>

<section class="section" style="padding-top:10px;">
  <div class="app-container">
    <div class="section-head">
      <h2><i class="bi bi-graph-up-arrow gradient-text"></i> Sedang hangat disewa</h2>
      <a href="{{ url('/marketplace?type=sewa') }}" class="link-more">Semua alat sewa <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="product-grid" id="trendGrid"></div>
  </div>
</section>

<section class="app-container">
  <div class="banner-glass glass">
    <div>
      <h3><i class="bi bi-calendar2-week gradient-text"></i> Booking & pembayaran online</h3>
      <p>Pilih tanggal sewa, tentukan DP atau bayar penuh, lalu bayar via Cash, Transfer, atau QRIS. Semua bisa dipantau statusnya di dashboard kamu.</p>
    </div>
    <a href="{{ url('/booking') }}" class="btn" style="background:var(--ink);color:#fff;"><i class="bi bi-wallet2"></i> Mulai booking</a>
  </div>
</section>

<section class="section">
  <div class="app-container">
    <div class="section-head">
      <h2><i class="bi bi-people-fill gradient-text"></i> Mitra kreator aktif</h2>
      <a href="{{ url('/mitra') }}" class="link-more">Gabung jadi mitra <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="creator-grid" id="creatorGrid"></div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="app-container">
    <div class="section-head"><h2><i class="bi bi-signpost-split-fill gradient-text"></i> Cara kerja CamRent</h2></div>
    <div class="steps-grid">
      <div class="step-card glass">
        <div class="step-num">1</div>
        <h4>Pilih & jadwalkan</h4>
        <p>Cari alat, cek ketersediaan, lalu tentukan tanggal sewa lewat kalender interaktif.</p>
      </div>
      <div class="step-card glass">
        <div class="step-num">2</div>
        <h4>Bayar dengan aman</h4>
        <p>Pilih DP 30% atau bayar penuh, metode Cash, Transfer, atau QRIS — pilih jaminan KTP/SIM.</p>
      </div>
      <div class="step-card glass">
        <div class="step-num">3</div>
        <h4>Ambil & gunakan</h4>
        <p>Konfirmasi dari mitra masuk, ambil alat sesuai jadwal, dan mulai berkarya.</p>
      </div>
      <div class="step-card glass">
        <div class="step-num">4</div>
        <h4>Kembalikan & selesai</h4>
        <p>Kembalikan tepat waktu, jaminan dikembalikan, status pesanan otomatis "Selesai".</p>
      </div>
    </div>
  </div>
</section>

<section class="app-container">
  <div class="banner-glass glass" style="background:linear-gradient(120deg,rgba(108,76,255,.14),rgba(62,139,255,.1));">
    <div>
      <h3><i class="bi bi-headset gradient-text"></i> Ada kendala booking atau pembayaran?</h3>
      <p>Tim Customer Service kami siap membantu 24/7 — dari salah pilih tanggal, kendala pembayaran, hingga alat bermasalah.</p>
    </div>
    <a href="{{ url('/cs') }}" class="btn btn-outline"><i class="bi bi-chat-dots"></i> Hubungi CS</a>
  </div>
</section>
@endsection