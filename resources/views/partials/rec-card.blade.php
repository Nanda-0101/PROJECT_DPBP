{{-- Kartu rekomendasi ringkas. Param: $p (Product dengan atribut `reason`), $ajax (bool), $from (opsional) --}}
<div class="rec-card">
  @include('partials.product-image', ['p' => $p])
  <span class="rec-reason"><i class="bi bi-stars"></i> {{ $p->reason ?? 'Rekomendasi' }}</span>
  <div class="rec-name">{{ $p->name }}</div>
  <div class="pc-meta">
    <span>{{ $p->category->name ?? '-' }}</span>
    @if(!empty($p->rating))
      <span class="rating"><i class="bi bi-star-fill"></i> {{ $p->rating }}</span>
    @endif
  </div>
  <div class="rec-price">
    <b>{{ 'Rp '.number_format($p->price_per_day, 0, ',', '.') }}</b>
    <span class="hint">/hari</span>
  </div>
  @if($ajax ?? false)
    {{-- Dipakai di dalam <form> checkout (form bersarang tidak valid) → tombol AJAX, bukan form. --}}
    <button type="button" class="btn btn-secondary btn-sm btn-block js-quick-add"
            data-url="{{ route('cart.add', $p) }}" data-from="{{ $from ?? '' }}"
            aria-label="Tambah {{ $p->name }} ke keranjang">
      <i class="bi bi-cart-plus"></i> Tambah
    </button>
  @else
    <form method="POST" action="{{ route('cart.add', $p) }}">
      @csrf
      <button type="submit" class="btn btn-secondary btn-sm btn-block" aria-label="Tambah {{ $p->name }} ke keranjang">
        <i class="bi bi-cart-plus"></i> Tambah
      </button>
    </form>
  @endif
</div>
