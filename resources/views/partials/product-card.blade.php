<div class="product-card">
  @if($buyOnPage ?? false)
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
      <b>{{ 'Rp '.number_format($p->price_per_day ?: $p->price_sale, 0, ',', '.') }}</b>
      <div class="hint">{{ $p->price_per_day ? 'per hari' : 'harga jual' }}</div>
    </div>
    @if($p->price_sale > 0 && $p->price_per_day > 0)
      <span class="badge badge-rent">sewa & jual</span>
    @elseif($p->price_per_day > 0)
      <span class="badge badge-rent">sewa</span>
    @else
      <span class="badge badge-buy">jual</span>
    @endif
  </div>

  <div class="pc-actions">
    @if($buyOnPage ?? false)
      <button type="button" class="btn btn-secondary btn-sm" data-product-detail
              data-detail-url="{{ route('marketplace.product.show', $p) }}">
        <i class="bi bi-card-list"></i> Specsheet
      </button>
    @endif
    @if($p->price_per_day > 0)
      <a href="{{ route('booking', ['product' => $p->id]) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-calendar-plus"></i> Booking
      </a>
    @endif
    @if($p->price_sale > 0)
      @if($buyOnPage ?? false)
        <button type="button" class="btn btn-secondary btn-sm"
                data-buy-url="{{ route('marketplace.buy', $p->id) }}"
                data-product-id="{{ $p->id }}"
                data-product-name="{{ $p->name }}"
                data-product-price="{{ number_format($p->price_sale, 0, ',', '.') }}"
                data-buy-product>
          <i class="bi bi-cart"></i> Beli
        </button>
      @else
        <a href="{{ route('marketplace', ['buy' => $p->id]) }}" class="btn btn-secondary btn-sm">
          <i class="bi bi-cart"></i> Beli
        </a>
      @endif
    @endif
  </div>
</div>