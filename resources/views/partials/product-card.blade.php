{{-- Kartu produk sewa. Param: $p, $onMarketplace (bool), $highlight (class opsional) --}}
@php
  $isMarketplace = $onMarketplace ?? false;
  $detailUrl = route('marketplace.product.show', $p);
  $stock = (int) $p->stock;
@endphp
<article class="product-card {{ $highlight ?? '' }}">
  <div class="pc-media">
    @if($isMarketplace)
      <button type="button" class="pc-detail-trigger" data-product-detail data-detail-url="{{ $detailUrl }}"
              aria-label="Lihat specsheet {{ $p->name }}">
        @include('partials.product-image', ['p' => $p])
      </button>
    @else
      <a href="{{ route('marketplace', ['highlight' => $p->id]) }}" class="pc-detail-trigger" aria-label="Lihat {{ $p->name }} di marketplace">
        @include('partials.product-image', ['p' => $p])
      </a>
    @endif
    <span class="pc-chip">{{ $p->category->name ?? '-' }}</span>
    @if($p->is_trending)
      <span class="pc-chip pc-chip-hot" title="Trending"><i class="bi bi-fire"></i><span class="pc-chip-text"> Trending</span></span>
    @endif
  </div>

  <div class="pc-body">
    @if($isMarketplace)
      <button type="button" class="pc-name pc-detail-trigger" data-product-detail data-detail-url="{{ $detailUrl }}">{{ $p->name }}</button>
    @else
      <a href="{{ route('marketplace', ['highlight' => $p->id]) }}" class="pc-name">{{ $p->name }}</a>
    @endif

    <div class="pc-sub">
      @if(!empty($p->rating))
        <span class="rating"><i class="bi bi-star-fill"></i> {{ $p->rating }}</span>
      @else
        <span class="hint" style="margin:0;">Baru</span>
      @endif
      <span class="pc-seller"><i class="bi bi-shop-window"></i> {{ $p->seller->name ?? 'CamRent' }}</span>
    </div>

    <div class="pc-price-row">
      <div class="pc-price"><b>{{ 'Rp '.number_format($p->price_per_day, 0, ',', '.') }}</b><span>/hari</span></div>
      <span class="pc-stock @if($stock <= 1) low @endif">
        <i class="bi bi-box-seam"></i> {{ $stock <= 1 ? 'Sisa '.$stock : 'Stok '.$stock }}
      </span>
    </div>

    <div class="pc-actions">
      <form method="POST" action="{{ route('cart.add', $p) }}" class="js-cart-add">
        @csrf
        <button type="submit" class="btn btn-secondary btn-sm" title="Masukkan ke keranjang sewa"
                aria-label="Masukkan {{ $p->name }} ke keranjang">
          <i class="bi bi-cart-plus"></i><span class="btn-text">Keranjang</span>
        </button>
      </form>
      <form method="POST" action="{{ route('cart.add', $p) }}">
        @csrf
        <input type="hidden" name="rent_now" value="1">
        <button type="submit" class="btn btn-primary btn-sm" title="Sewa sekarang">
          <i class="bi bi-lightning-charge-fill"></i><span class="btn-text">Sewa</span>
        </button>
      </form>
    </div>

    @if($isMarketplace)
      <button type="button" class="pc-spec-link" data-product-detail data-detail-url="{{ $detailUrl }}">
        <i class="bi bi-card-list"></i> Lihat specsheet
      </button>
    @endif
  </div>
</article>
