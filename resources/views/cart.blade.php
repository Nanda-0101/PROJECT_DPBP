@extends('layouts.app')

@section('page', 'cart')
@section('title', 'Keranjang Sewa · CamRent')
@section('breadcrumb', 'Keranjang')

@section('content')
<header class="page-head cart-page-head">
  <div class="app-container cart-page-head-row">
    <div>
      <h1>Keranjang sewa</h1>
      <p>Kumpulkan beberapa alat sekaligus — kamera, lensa, lighting — atur jumlahnya dengan tombol + / −, lalu checkout.</p>
    </div>
    <ol class="flow-steps" aria-label="Langkah sewa">
      <li class="flow-step active"><span class="flow-step-number">1</span><span>Keranjang</span></li>
      <li class="flow-step"><span class="flow-step-number">2</span><span>Checkout</span></li>
      <li class="flow-step"><span class="flow-step-number">3</span><span>Bayar</span></li>
    </ol>
  </div>
</header>

@if($lines->isEmpty())
  <section class="app-container" style="padding-bottom:20px;">
    <div class="card empty-state cart-empty-state">
      <i class="bi bi-cart-x"></i>
      <p>Keranjang sewamu masih kosong.</p>
      <a href="{{ route('marketplace') }}" class="btn btn-primary" style="margin-top:14px;">
        Lihat Catalog
      </a>
    </div>
  </section>
@else
  <form method="POST" action="{{ route('cart.checkout') }}" id="cartForm">
    @csrf
    <section class="app-container booking-layout">
      <div class="booking-main">

        <div class="glass card cart-toolbar">
          <label class="check-row" for="selectAll">
            <input type="checkbox" id="selectAll" checked>
            <span>Pilih semua (<span id="cartLineCount">{{ $lines->count() }}</span> alat)</span>
          </label>
        </div>

        <div id="cartList">
          @foreach($lines as $line)
            @php $p = $line['product']; @endphp
            <div class="card cart-item"
                 data-cart-item
                 data-price="{{ (float) $p->price_per_day }}"
                 data-stock="{{ (int) $p->stock }}"
                 data-name="{{ $p->name }}"
                 data-update-url="{{ route('cart.update', $p) }}"
                 data-remove-url="{{ route('cart.remove', $p) }}">
              <input type="checkbox" name="selected[]" value="{{ $p->id }}" class="cart-check" checked
                     aria-label="Pilih {{ $p->name }}">
              <div class="cart-thumb">@include('partials.product-image', ['p' => $p])</div>
              <div class="cart-info">
                <div class="cart-name">{{ $p->name }}</div>
                <div class="hint">{{ $p->category->name ?? '-' }} · <i class="bi bi-shop-window"></i> {{ $p->seller->name ?? 'CamRent (Vendor)' }}</div>
                <div class="cart-unit">Rp {{ number_format($p->price_per_day, 0, ',', '.') }} <span class="hint">/hari</span></div>
                <div class="hint cart-stock" data-stock-hint @if($line['qty'] < $p->stock) hidden @endif>
                  <i class="bi bi-info-circle"></i> Stok maksimal tercapai ({{ $p->stock }} unit)
                </div>
              </div>
              <div class="cart-qty">
                <div class="qty-stepper">
                  <button type="button" data-qty-minus aria-label="Kurangi jumlah {{ $p->name }}"><i class="bi bi-dash"></i></button>
                  <input type="number" value="{{ $line['qty'] }}" min="1" max="{{ $p->stock }}" readonly data-qty
                         aria-label="Jumlah {{ $p->name }}">
                  <button type="button" data-qty-plus aria-label="Tambah jumlah {{ $p->name }}"><i class="bi bi-plus"></i></button>
                </div>
              </div>
              <div class="cart-line-total">
                <b data-line-total>Rp {{ number_format($line['line_per_day'], 0, ',', '.') }}</b>
                <div class="hint">per hari</div>
              </div>
              <button type="button" class="cart-remove" data-remove aria-label="Hapus {{ $p->name }}" title="Hapus">
                <i class="bi bi-trash3"></i>
              </button>
            </div>
          @endforeach
        </div>
      </div>

      <aside class="booking-side">
        <div class="glass card summary-card">
          <h4 style="margin-bottom:14px;"><i class="bi bi-receipt gradient-text"></i> Ringkasan Sewa</h4>
          <div class="sum-row"><span>Jenis alat dipilih</span><span id="sumKinds">0</span></div>
          <div class="sum-row"><span>Total unit</span><span id="sumUnits">0</span></div>
          <div class="sum-row total"><span>Estimasi / hari</span><span id="sumPerDay">Rp 0</span></div>
          <div class="hint">Total akhir dihitung sesuai tanggal & durasi sewa yang kamu pilih saat checkout.</div>
          @if(auth()->user()?->canRent())
            <button type="submit" class="btn btn-primary btn-block" id="checkoutBtn" style="margin-top:16px;" disabled>
              <i class="bi bi-bag-check"></i> <span>Checkout (0)</span>
            </button>
          @else
            <div class="hint" style="margin-top:16px;text-align:center;">
              Silahkan login untuk dapat menyewa alat.
              @guest
                <a href="{{ route('login') }}" style="color:var(--violet-deep);font-weight:700;">Masuk atau daftar</a>
              @endguest
            </div>
          @endif
          <div class="hint" style="margin-top:10px;text-align:center;">
            <a href="{{ route('marketplace') }}" style="color:var(--violet-deep);font-weight:700;">
              <i class="bi bi-plus-circle"></i> Tambah alat lain
            </a>
          </div>
        </div>
      </aside>
    </section>
  </form>
@endif

@if($recommendations->isNotEmpty())
  <section class="app-container" style="padding-bottom:30px;">
    <div class="section-head" style="margin-bottom:16px;">
      <h2 style="font-size:1.4rem;"><i class="bi bi-stars gradient-text"></i> Rekomendasi untuk kamu</h2>
    </div>
    <div class="rec-grid">
      @foreach($recommendations as $p)
        @include('partials.rec-card', ['p' => $p, 'ajax' => false])
      @endforeach
    </div>
  </section>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('cartList');
    if (!list) return;

    const selectAll = document.getElementById('selectAll');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const items = () => [...list.querySelectorAll('[data-cart-item]')];
    const qtyOf = item => Number(item.querySelector('[data-qty]').value);

    function refreshSummary() {
        const checked = items().filter(item => item.querySelector('.cart-check').checked);
        const units = checked.reduce((sum, item) => sum + qtyOf(item), 0);
        const perDay = checked.reduce((sum, item) => sum + qtyOf(item) * Number(item.dataset.price), 0);

        document.getElementById('sumKinds').textContent = checked.length;
        document.getElementById('sumUnits').textContent = units;
        document.getElementById('sumPerDay').textContent = window.CamRent.formatRupiah(perDay);
        checkoutBtn.querySelector('span').textContent = `Checkout (${checked.length})`;
        checkoutBtn.disabled = checked.length === 0;
        document.getElementById('cartLineCount').textContent = items().length;
        selectAll.checked = items().length > 0 && checked.length === items().length;
    }

    function refreshStepper(item) {
        const qty = qtyOf(item);
        const stock = Number(item.dataset.stock);
        item.querySelector('[data-qty-plus]').disabled = qty >= stock;
        item.querySelector('[data-stock-hint]').hidden = qty < stock;
    }

    async function changeQuantity(item, nextQty) {
        const buttons = item.querySelectorAll('.qty-stepper button');
        buttons.forEach(button => button.disabled = true);
        try {
            const data = await window.CamRent.request(item.dataset.updateUrl, 'PATCH', { qty: nextQty });
            window.CamRent.setCartCount(data.cart_count);
            if (data.removed) return removeItem(item);
            item.querySelector('[data-qty]').value = data.qty;
            item.querySelector('[data-line-total]').textContent = window.CamRent.formatRupiah(data.line_per_day);
            if (data.message) window.CamRent.toast(data.message, 'error');
        } catch (error) {
            window.CamRent.toast(error.message, 'error');
        } finally {
            buttons.forEach(button => button.disabled = false);
            refreshStepper(item);
            item.querySelector('[data-qty-minus]').disabled = false;
            refreshSummary();
        }
    }

    function removeItem(item) {
        item.remove();
        if (!items().length) return window.location.reload();
        refreshSummary();
    }

    async function deleteItem(item) {
        try {
            const data = await window.CamRent.request(item.dataset.removeUrl, 'DELETE');
            window.CamRent.setCartCount(data.cart_count);
            window.CamRent.toast(data.message);
            removeItem(item);
        } catch (error) {
            window.CamRent.toast(error.message, 'error');
        }
    }

    function confirmDelete(item) {
        window.CamRent.confirm(`Hapus ${item.dataset.name} dari keranjang?`, 'Hapus alat?')
            .then(confirmed => {
                if (confirmed) deleteItem(item);
            });
    }

    list.addEventListener('click', event => {
        const item = event.target.closest('[data-cart-item]');
        if (!item) return;

        if (event.target.closest('[data-qty-plus]')) {
            changeQuantity(item, qtyOf(item) + 1);
        } else if (event.target.closest('[data-qty-minus]')) {
            if (qtyOf(item) <= 1) {
                confirmDelete(item);
            } else {
                changeQuantity(item, qtyOf(item) - 1);
            }
        } else if (event.target.closest('[data-remove]')) {
            confirmDelete(item);
        }
    });

    list.addEventListener('change', event => {
        if (event.target.classList.contains('cart-check')) refreshSummary();
    });
    selectAll.addEventListener('change', () => {
        items().forEach(item => item.querySelector('.cart-check').checked = selectAll.checked);
        refreshSummary();
    });

    items().forEach(refreshStepper);
    refreshSummary();
});
</script>
@endpush
