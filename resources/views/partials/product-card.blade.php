<div class="product-card">
  {{-- Gunakan ImageHelper untuk mendapatkan URL gambar yang valid --}}
  @php $img = \App\Helpers\ImageHelper::url($p->image); @endphp
  
  @if($img)
    <img class="pc-photo" src="{{ $img }}" alt="{{ $p->name }}">
  @else
    <div class="pc-icon"><i class="bi {{ $p->icon ?? 'bi-box-seam' }}"></i></div>
  @endif

  <div class="pc-name">{{ $p->name }}</div>

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
    {{ $p->seller->name ?? 'Kamu' }}
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
    @if($p->price_per_day > 0)
      <a href="{{ route('booking', ['product' => $p->id]) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-calendar-plus"></i> Booking
      </a>
    @endif
    @if($p->price_sale > 0)
      <button type="button" class="btn btn-secondary btn-sm"
              onclick="document.getElementById('buyProductId').value='{{ $p->id }}';
                       document.getElementById('buySummary').innerHTML='<b>{{ $p->name }}</b> — Rp {{ number_format($p->price_sale,0,',','.') }}';
                       document.getElementById('modalBuy').classList.add('open');">
        <i class="bi bi-cart"></i> Beli
      </button>
    @endif
  </div>
</div>