{{-- Kartu produk sewa. Param: $p, $onMarketplace (bool), $highlight (class opsional) --}}
@php
  $isMarketplace = $onMarketplace ?? false;
  $detailUrl = route('marketplace.product.show', $p);
  $stock = (int) $p->stock;
  $isOwnMitraProduct = auth()->user()?->isMitra()
      && (int) $p->seller_id === (int) auth()->id();

  // Ringkasan spesifikasi singkat (maks. 2 chip) dari baris "Label: nilai".
  $quickSpecs = $p->relationLoaded('specs')
      ? $p->specs->map(function ($s) {
          $t = trim((string) $s->spec_text);
          $pos = strpos($t, ': ');
          return ($pos !== false && $pos <= 32) ? trim(substr($t, $pos + 2)) : $t;
        })->filter(fn ($t) => $t !== '' && mb_strlen($t) <= 24)->take(2)->values()
      : collect();
@endphp
<article class="product-card {{ $highlight ?? '' }}">
  <div class="pc-media">
    @if($isMarketplace)
      <button type="button" class="pc-detail-trigger" data-product-detail data-detail-url="{{ $detailUrl }}"
              aria-label="Lihat specsheet {{ $p->name }}">
        @include('partials.product-image', ['p' => $p])
      </button>
    @else
      <a href="{{ route('marketplace', ['highlight' => $p->id]) }}" class="pc-detail-trigger" aria-label="Lihat {{ $p->name }} di Catalog">
        @include('partials.product-image', ['p' => $p])
      </a>
    @endif
    <span class="pc-chip">{{ $p->category->name ?? '-' }}</span>
    @if($p->is_trending)
      <span class="pc-chip pc-chip-hot" title="Trending"><i class="bi bi-fire"></i><span class="pc-chip-text"> Trending</span></span>
    @endif
    @if($isMarketplace)
      <span class="pc-quick" aria-hidden="true"><i class="bi bi-arrows-angle-expand"></i> Lihat detail</span>
    @endif
  </div>

  <div class="pc-body">
    @if($p->brand)
      <span class="pc-brand">{{ $p->brand }}</span>
    @endif
    @if($isMarketplace)
      <button type="button" class="pc-name pc-detail-trigger" data-product-detail data-detail-url="{{ $detailUrl }}">{{ $p->name }}</button>
    @else
      <a href="{{ route('marketplace', ['highlight' => $p->id]) }}" class="pc-name">{{ $p->name }}</a>
    @endif

    <div class="pc-sub">
      @if(!empty($p->rating))
        <span class="rating"><i class="bi bi-star-fill"></i> {{ $p->rating }}</span>
      @else
        <span class="pc-new">Baru</span>
      @endif
      <span class="pc-seller"><i class="bi bi-shop-window"></i> {{ $p->seller->name ?? 'CamRent' }}</span>
    </div>

    @if($quickSpecs->isNotEmpty())
      <ul class="pc-specs">
        @foreach($quickSpecs as $spec)
          <li>{{ $spec }}</li>
        @endforeach
      </ul>
    @endif

    <div class="pc-price-row">
      <div class="pc-price"><b>{{ 'Rp '.number_format($p->price_per_day, 0, ',', '.') }}</b><span>/hari</span></div>
      <span class="pc-stock @if($stock <= 1) low @endif">
        <i class="bi bi-box-seam"></i> {{ $stock <= 1 ? 'Sisa '.$stock : 'Stok '.$stock }}
      </span>
    </div>

    <div class="pc-actions">
      @if($isOwnMitraProduct)
        <span class="hint">Barang sendiri tidak dapat disewa.</span>
      @else
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
      @endif
    </div>

    @if($isMarketplace)
      <button type="button" class="pc-spec-link" data-product-detail data-detail-url="{{ $detailUrl }}">
        <i class="bi bi-card-list"></i> Lihat specsheet <i class="bi bi-arrow-right"></i>
      </button>
    @endif
  </div>
</article>
