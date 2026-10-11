@extends('layouts.app')

@section('page', 'home')
@section('title', 'CamRent · Sewa Kamera & Jual Beli Peralatan Untuk Content Creator')

@section('content')
<header class="hero section">
  <div class="app-container hero-grid">
    <div class="hero-copy">
      <span class="eyebrow badge badge-rent"><i class="bi bi-lightning-charge-fill"></i> #1 marketplace kreator</span>
      <h1>Sewa & beli peralatan kreatif, <span class="gradient-text">tanpa ribet.</span></h1>
      <p class="hero-sub">Kamera, lensa, lighting, mic, sampai gimbal — booking online, bayar aman, dan siap dipakai hari ini.</p>
      <div class="hero-cta">
        <a href="{{ route('booking') }}" class="btn btn-primary"><i class="bi bi-calendar-check"></i> Booking Kamera</a>
        <a href="{{ route('marketplace') }}" class="btn btn-secondary"><i class="bi bi-shop"></i> Jelajah Marketplace</a>
      </div>
      <div class="hero-stats">
        <div class="stat"><b>{{ $stats['products'] }}+</b><span>alat tersedia</span></div>
        <div class="stat"><b>{{ $stats['creators'] }}+</b><span>kreator aktif</span></div>
        <div class="stat"><b>⭐ {{ $stats['rating'] }}</b><span>rating rata-rata</span></div>
      </div>
    </div>
    <div class="hero-visual glass">
      <div class="hero-visual-head">
        <span class="tag"><i class="bi bi-fire"></i> Lagi trending</span>
      </div>
      <div class="hero-mini-grid">
        @forelse($trending as $p)
          @php $img = \App\Helpers\ImageHelper::url($p->image); @endphp
          <div class="hero-mini-card">
            @if($img)
              <img class="hero-mini-photo" src="{{ $img }}" alt="{{ $p->name }}" loading="lazy"
                   onerror="this.hidden=true;this.nextElementSibling.hidden=false">
              <i class="bi {{ $p->icon ?? 'bi-box-seam' }} hero-mini-fallback" hidden></i>
            @else
              <i class="bi {{ $p->icon ?? 'bi-box-seam' }}"></i>
            @endif
            <span style="font-weight:700;">{{ $p->name }}</span>
            <div class="mm-price">
              <span>{{ 'Rp '.number_format($p->price_per_day,0,',','.') }}/hari</span>
              <span class="badge badge-rent">sewa</span>
            </div>
          </div>
        @empty
          <p class="hint">Belum ada produk trending.</p>
        @endforelse
      </div>
    </div>
  </div>
</header>

{{-- KATEGORI --}}
<section class="section" style="padding-top:10px;">
  <div class="app-container">
    <div class="section-head">
      <h2><i class="bi bi-grid-3x3-gap-fill gradient-text"></i> Kategori populer</h2>
      <a href="{{ route('marketplace') }}" class="link-more">Lihat semua <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="cat-grid">
      @foreach($categories as $c)
        <a href="{{ route('marketplace', ['category' => $c->name]) }}" class="cat-card">
          <i class="bi {{ $c->icon }}"></i><span>{{ $c->name }}</span>
        </a>
      @endforeach
    </div>
  </div>
</section>

{{-- TRENDING GRID --}}
<section class="section" style="padding-top:10px;">
  <div class="app-container">
    <div class="section-head">
      <h2><i class="bi bi-graph-up-arrow gradient-text"></i> Sedang hangat disewa</h2>
      <a href="{{ route('marketplace', ['type' => 'sewa']) }}" class="link-more">Semua alat sewa <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="product-grid">
      @foreach($trendGrid as $p)
        @include('partials.product-card', ['p' => $p])
      @endforeach
    </div>
  </div>
</section>

{{-- BANNER BOOKING --}}
<section class="app-container">
  <div class="banner-glass glass">
    <div>
      <h3><i class="bi bi-calendar2-week gradient-text"></i> Booking & pembayaran online</h3>
      <p>Pilih tanggal sewa, tentukan DP atau bayar penuh, lalu bayar via Cash, Transfer, atau QRIS.</p>
    </div>
    <a href="{{ route('booking') }}" class="btn" style="background:var(--ink);color:#fff;">
      <i class="bi bi-wallet2"></i> Mulai booking
    </a>
  </div>
</section>

{{-- CREATORS --}}
<section class="section">
  <div class="app-container">
    <div class="section-head">
      <h2><i class="bi bi-people-fill gradient-text"></i> Mitra kreator aktif</h2>
      <a href="{{ route('mitra.index') }}" class="link-more">Gabung jadi mitra <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="creator-grid">
      @foreach($creators as $u)
        <div class="creator-card">
          <div class="creator-avatar"><i class="bi bi-camera-reels-fill"></i></div>
          <h5>{{ $u->name }}</h5>
          <div class="role">Mitra CamRent</div>
          <div class="rating"><i class="bi bi-star-fill"></i> 4.8 · {{ $u->products_count }} alat</div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- CARA KERJA --}}
<section class="section" style="padding-top:0;">
  <div class="app-container">
    <div class="section-head">
      <h2><i class="bi bi-signpost-split-fill gradient-text"></i> Cara kerja CamRent</h2>
    </div>
    <div class="steps-grid">
      <div class="step-card glass"><div class="step-num">1</div><h4>Pilih & jadwalkan</h4><p>Tentukan tanggal sewa lewat kalender interaktif.</p></div>
      <div class="step-card glass"><div class="step-num">2</div><h4>Bayar dengan aman</h4><p>DP 30% atau bayar penuh via Cash, Transfer, atau QRIS.</p></div>
      <div class="step-card glass"><div class="step-num">3</div><h4>Ambil & gunakan</h4><p>Konfirmasi mitra masuk, ambil alat sesuai jadwal.</p></div>
      <div class="step-card glass"><div class="step-num">4</div><h4>Kembalikan & selesai</h4><p>Kembalikan tepat waktu, jaminan dikembalikan.</p></div>
    </div>
  </div>
</section>

{{-- CS BANNER --}}
<section class="app-container">
  <div class="banner-glass glass" style="background:linear-gradient(120deg,rgba(108,76,255,.14),rgba(62,139,255,.1));">
    <div>
      <h3><i class="bi bi-headset gradient-text"></i> Ada kendala booking atau pembayaran?</h3>
      <p>Tim CS kami siap membantu 24/7.</p>
    </div>
    <a href="{{ route('cs') }}" class="btn btn-outline"><i class="bi bi-chat-dots"></i> Hubungi CS</a>
  </div>
</section>
@endsection