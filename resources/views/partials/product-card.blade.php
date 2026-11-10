<div class="product-card">
  @if($onMarketplace ?? false)
    <button type="button" class="pc-detail-trigger" data-product-detail
            data-detail-url="{{ route('marketplace.product.show', $p) }}"
            aria-label="Lihat specsheet {{ $p->name }}">
      @include('partials.product-image', ['p' => $p])
    </button>
    <button type="button" class="pc-name pc-detail-trigger" data-product-detail
            data-detail-url="{{ route('marketplace.product.show', $p) }}">
      {{ $p->name }}
    </button>
  @else
    @include('partials.product-image', ['p' => $p])
    <div class="pc-name">{{ $p->name }}</div>
  @endif

  <div class="pc-meta">
    <span>{{ $p->category->name ?? '-' }}</span>
    @if(!empty($p->rating))
      <span class="rating"><i class="bi bi-star-fill"></i> {{ $p->rating }}</span>
    @else
      <span>Baru</span>
    @endif
  </div>

  <div class="pc-seller">
    <i class="bi bi-shop-window"></i>
    {{ $p->seller->name ?? 'CamRent (Vendor)' }}
  </div>

  <div class="pc-price-row">
    <div class="pc-price">
      <b>{{ 'Rp '.number_format($p->price_per_day, 0, ',', '.') }}</b>
      <div class="hint">per hari</div>
    </div>
    <span class="badge badge-rent">sewa</span>
  </div>

  <div class="pc-actions">
    @if($onMarketplace ?? false)
      <button type="button" class="btn btn-secondary btn-sm" data-product-detail
              data-detail-url="{{ route('marketplace.product.show', $p) }}">
        <i class="bi bi-card-list"></i> Specsheet
      </button>
    @endif
    <form method="POST" action="{{ route('cart.add', $p) }}" class="js-cart-add">
      @csrf
      <button type="submit" class="btn btn-secondary btn-sm" title="Masukkan ke keranjang sewa" aria-label="Masukkan {{ $p->name }} ke keranjang">
        <i class="bi bi-cart-plus"></i> Keranjang
      </button>
    </form>
    <form method="POST" action="{{ route('cart.add', $p) }}">
      @csrf
      <input type="hidden" name="rent_now" value="1">
      <button type="submit" class="btn btn-primary btn-sm">
        <i class="bi bi-calendar-plus"></i> Sewa
      </button>
    </form>
  </div>
</div>
