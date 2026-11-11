@extends('layouts.app')

@section('page', 'dashboard')
@section('title', 'Dashboard · CamRent')

@section('content')
<section class="app-container dash-wrap">
  <div class="dash-head glass card">
    <div class="dash-avatar">{{ $user ? strtoupper(substr($user->name, 0, 2)) : 'T' }}</div>
    <div style="flex:1;">
      <h2 style="font-size:1.4rem;">{{ $user?->name ?? 'Aktivitas Tamu' }}</h2>
      <div class="hint">
        @if($user)
          {{ $isMitra ? 'Akun Mitra / Penyedia Alat' : 'Akun Pengguna' }}
        @else
          Pesanan yang dibuat dari browser ini. Masuk atau daftar untuk menyimpan riwayat ke akun.
        @endif
      </div>
    </div>
    @if($isMitra)
      <span class="status-pill status-{{ strtolower(str_replace(' ', '-', $user->status)) }}">{{ $user->status }}</span>
    @endif
  </div>

  @if(session('payment_summary'))
    @include('partials.payment-summary', ['summary' => session('payment_summary')])
  @endif

  <div class="dash-tabs">
    <div class="dash-tab active" data-tab="riwayat"><i class="bi bi-clock-history"></i> Riwayat Sewa</div>
    @if($isMitra)
      <div class="dash-tab" data-tab="toko"><i class="bi bi-shop"></i> Toko Saya</div>
    @endif
    @if($user)
      <div class="dash-tab" data-tab="profil"><i class="bi bi-person"></i> Profil</div>
    @endif
  </div>

  {{-- RIWAYAT --}}
  <div class="dash-panel" id="panelRiwayat">
    <div class="section-head" style="margin-bottom:14px;">
      <h3 style="font-size:1.2rem;"><i class="bi bi-clock-history gradient-text"></i> Riwayat Sewa</h3>
    </div>
    @forelse($bookings as $b)
      <div class="order-item">
        <div class="order-icon"><i class="bi bi-camera-reels"></i></div>
        <div class="order-info">
          <div class="oi-title">{{ $b->items->first()->product_name_snap ?? '-' }}
            <span class="hint">· {{ $b->code }}</span></div>
          <div class="oi-items">
            @foreach($b->items as $it)
              <span class="tag">{{ $it->product_name_snap }} ×{{ $it->qty }}</span>
            @endforeach
          </div>
          <div class="oi-meta">
            {{ $b->start_date->format('d M Y') }}
            @if($b->end_date->ne($b->start_date)) – {{ $b->end_date->format('d M Y') }} @endif
            · {{ $b->duration_label }} · {{ \App\Support\PaymentMethods::label($b->payment_method) }}
          </div>
        </div>
        <div class="order-price">
          Rp {{ number_format($b->total_amount, 0, ',', '.') }}
          <div><span class="status-pill status-{{ strtolower(str_replace(' ', '-', $b->status)) }}">{{ $b->status }}</span></div>
        </div>
        <div class="order-actions">
          <a href="{{ route('cs', ['booking' => $b->id]) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-headset"></i> Ada masalah?
          </a>
        </div>
      </div>
    @empty
      <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>Belum ada riwayat sewa.</p>
        <a href="{{ route('marketplace') }}" class="btn btn-primary" style="margin-top:10px;">Sewa sekarang</a>
        <a href="{{ route('cart.index') }}" class="btn btn-secondary" style="margin-top:10px;">Lihat Keranjang</a>
      </div>
    @endforelse
  </div>

  {{-- TOKO (mitra only) --}}
  @if($isMitra)
    <div class="dash-panel" id="panelToko" style="display:none;">
      <div class="section-head" style="margin-bottom:14px;">
        <h3 style="font-size:1.2rem;"><i class="bi bi-shop gradient-text"></i> Pesanan Sewa Masuk ke Toko Saya</h3>
      </div>
      @forelse($myOrders as $b)
        <div class="order-item">
          <div class="order-icon"><i class="bi bi-person-check"></i></div>
          <div class="order-info">
            <div class="oi-title">{{ $b->items->first()->product_name_snap }}
              <span class="hint">· {{ $b->code }}</span></div>
            <div class="oi-items">
              @foreach($b->items as $it)
                <span class="tag">{{ $it->product_name_snap }} ×{{ $it->qty }}</span>
              @endforeach
            </div>
            <div class="oi-meta">Penyewa: {{ $b->user->name ?? '-' }} · {{ $b->user->phone ?? '-' }} · {{ $b->start_date->format('d M Y') }}</div>
          </div>
          <div class="order-price">
            Rp {{ number_format($b->total_amount, 0, ',', '.') }}
            <div><span class="status-pill status-{{ strtolower(str_replace(' ', '-', $b->status)) }}">{{ $b->status }}</span></div>
          </div>
          <div class="order-actions">
            @if($b->status === 'Menunggu Konfirmasi')
              <form method="POST" action="{{ route('dashboard.booking.status', [$b->id, 'Dikonfirmasi']) }}" style="display:inline">
                @csrf @method('PATCH')
                <button class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Konfirmasi</button>
              </form>
              <form method="POST" action="{{ route('dashboard.booking.status', [$b->id, 'Ditolak']) }}" style="display:inline">
                @csrf @method('PATCH')
                <button class="btn btn-danger btn-sm"><i class="bi bi-x-lg"></i> Tolak</button>
              </form>
            @elseif($b->status === 'Dikonfirmasi')
              <form method="POST" action="{{ route('dashboard.booking.status', [$b->id, 'Selesai']) }}" style="display:inline">
                @csrf @method('PATCH')
                <button class="btn btn-primary btn-sm"><i class="bi bi-check2-all"></i> Tandai Selesai</button>
              </form>
            @endif
          </div>
        </div>
      @empty
        <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada pesanan masuk.</p></div>
      @endforelse

      <div class="section-head" style="margin:30px 0 14px;">
        <h3 style="font-size:1.2rem;"><i class="bi bi-box-seam gradient-text"></i> Produk Saya</h3>
        <a href="{{ route('marketplace') }}" class="link-more">Sewakan alat <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="dash-product-grid">
        @forelse($myProducts as $p)
          <div class="product-card">
            @include('partials.product-image', ['p' => $p])
            <div class="pc-name">{{ $p->name }}</div>
            <div class="hint">{{ $p->category->name ?? '-' }} · Stok {{ $p->stock }}</div>
            <div class="pc-price-row">
              <b>{{ 'Rp '.number_format($p->price_per_day,0,',','.').'/hari' }}</b>
            </div>
          </div>
        @empty
          <div class="empty-state" style="grid-column:1/-1;"><i class="bi bi-box-seam"></i><p>Belum ada produk.</p></div>
        @endforelse
      </div>
    </div>
  @endif

  {{-- PROFIL --}}
  @if($user)
    <div class="dash-panel" id="panelProfil" style="display:none;">
    <div class="glass card" style="max-width:520px;padding:24px;">
      <h3 style="font-size:1.1rem;margin-bottom:16px;"><i class="bi bi-person-badge gradient-text"></i> Info Akun</h3>
      <div class="profil-row"><span>Nama</span><span>{{ $user->name }}</span></div>
      <div class="profil-row"><span>Email</span><span>{{ $user->email }}</span></div>
      <div class="profil-row"><span>No. HP</span><span>{{ $user->phone ?? '-' }}</span></div>
      <div class="profil-row"><span>Peran</span><span>{{ ucfirst($user->role) }}</span></div>
      @if($isMitra)
        <div class="profil-row">
          <span>Status verifikasi</span>
          <span class="status-pill status-{{ strtolower(str_replace(' ', '-', $user->status)) }}">{{ $user->status }}</span>
          </div>
        @endif
      @endif
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.dash-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.dash-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.dash-panel').forEach(p => p.style.display = 'none');
            const panel = document.getElementById('panel' + tab.dataset.tab.charAt(0).toUpperCase() + tab.dataset.tab.slice(1));
            if (panel) panel.style.display = 'block';
        });
    });
});
</script>
@endpush