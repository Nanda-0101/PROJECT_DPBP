@extends('layouts.app')

@section('page', 'marketplace')
@section('title', 'Marketplace · CamRent')

@section('content')
<header class="page-head">
  <div class="app-container">
<<<<<<< HEAD
    <span class="eyebrow badge badge-rent"><i class="bi bi-shop"></i> Marketplace</span>
    <h1>Sewa peralatan kreatif</h1>
    <p>Pilih alat dari mitra terpercaya, masukkan ke keranjang, lalu sewa sekaligus dalam satu kali checkout.</p>
=======
    <span class="eyebrow badge badge-buy"><i class="bi bi-shop"></i> Marketplace</span>
    <h1 style="font-size:2.1rem;margin:12px 0 8px;">Sewa peralatan kreatif</h1>
    <p style="max-width:600px;">Semua alat dari mitra terpercaya, siap disewa harian. Punya alat menganggur? Klik "Sewakan Barang Saya".</p>
>>>>>>> 2a5ba4dc5ab56e538507b4c5e2ab220457c23057
  </div>
</header>

<section class="app-container">
  <form class="mp-toolbar glass" method="GET" action="{{ route('marketplace') }}">
    <div class="mp-search">
      <i class="bi bi-search"></i>
      <input type="text" name="q" value="{{ $q }}" class="input" placeholder="Cari nama alat, merek, atau kategori...">
    </div>
    <select name="category" class="input mp-select" onchange="this.form.submit()">
      <option value="">Semua kategori</option>
      @foreach($categories as $c)
        <option value="{{ $c->name }}" @selected($category === $c->name)>{{ $c->name }}</option>
      @endforeach
    </select>
    <select name="source" class="input mp-select" onchange="this.form.submit()">
      <option value="">Semua</option>
      <option value="mitra" @selected($source === 'mitra')>Mitra</option>
      <option value="camrent" @selected($source === 'camrent')>CamRent</option>
    </select>
    <select name="sort" class="input mp-select" onchange="this.form.submit()">
      <option value="trending"   @selected($sort === 'trending')>Urutkan: Trending</option>
      <option value="price-asc"  @selected($sort === 'price-asc')>Harga terendah</option>
      <option value="price-desc" @selected($sort === 'price-desc')>Harga tertinggi</option>
      <option value="rating"     @selected($sort === 'rating')>Rating tertinggi</option>
    </select>
    @auth
<<<<<<< HEAD
      <button type="button" class="btn btn-outline" onclick="document.getElementById('modalSewakan').classList.add('open')">
        <i class="bi bi-plus-circle"></i> Sewakan Barang Saya
      </button>
    @else
      <a href="{{ route('login') }}" class="btn btn-outline">
=======
      <button type="button" class="btn btn-primary" onclick="document.getElementById('modalSewakan').classList.add('open')">
        <i class="bi bi-plus-circle"></i> Sewakan Barang Saya
      </button>
    @else
      <a href="{{ route('login') }}" class="btn btn-primary">
>>>>>>> 2a5ba4dc5ab56e538507b4c5e2ab220457c23057
        <i class="bi bi-plus-circle"></i> Sewakan Barang Saya
      </a>
    @endauth
  </form>

  <div class="mp-chip-row">
    <a href="{{ route('marketplace') }}" class="mp-chip {{ !$category ? 'active' : '' }}">Semua</a>
    @foreach($categories as $c)
      <a href="{{ route('marketplace', ['category' => $c->name]) }}"
         class="mp-chip {{ $category === $c->name ? 'active' : '' }}">{{ $c->name }}</a>
    @endforeach
  </div>

  <div class="mp-result-bar">
    <div><b>{{ $products->count() }} alat</b> <span class="hint" style="display:inline;">tersedia untuk disewa{{ $category ? ' · '.$category : '' }}</span></div>
    <span class="hint" style="margin:0;"><i class="bi bi-lightbulb"></i> Klik foto atau nama alat untuk melihat specsheet</span>
  </div>

  <div class="product-grid mp-grid">
    @forelse($products as $p)
<<<<<<< HEAD
      @include('partials.product-card', [
        'p' => $p,
        'onMarketplace' => true,
        'highlight' => $highlight == $p->id ? 'highlighted' : '',
      ])
=======
      <div class="product-card {{ $highlight == $p->id ? 'highlighted' : '' }}">
        @include('partials.product-card', ['p' => $p, 'onMarketplace' => true])
      </div>
>>>>>>> 2a5ba4dc5ab56e538507b4c5e2ab220457c23057
    @empty
      <div class="empty-state" style="grid-column:1/-1;">
        <i class="bi bi-search"></i>
        <p>Tidak ada alat yang cocok dengan pencarianmu.</p>
      </div>
    @endforelse
  </div>
</section>

{{-- MODAL: Specsheet produk --}}
<div class="modal-backdrop" id="modalProductDetail" role="presentation">
  <section class="modal-box glass-strong product-detail-modal" role="dialog"
           aria-modal="true" aria-label="Specsheet produk">
    <button type="button" class="modal-close" data-detail-close aria-label="Tutup specsheet">
      <i class="bi bi-x-lg"></i>
    </button>
    <div id="productDetailContent" aria-live="polite">
      <p class="hint">Memuat specsheet...</p>
    </div>
  </section>
</div>

{{-- MODAL: Sewakan Barang --}}
<div class="modal-backdrop" id="modalSewakan">
  <div class="modal-box glass-strong">
    <button type="button" class="modal-close" onclick="document.getElementById('modalSewakan').classList.remove('open')">
      <i class="bi bi-x-lg"></i>
    </button>
    <h3 style="margin-bottom:6px;"><i class="bi bi-tag gradient-text"></i> Sewakan Barang Saya</h3>
    <p style="margin-bottom:18px;">Isi detail alat yang ingin kamu sewakan ke kreator lain.</p>
    <form method="POST" action="{{ route('marketplace.rent-out') }}" enctype="multipart/form-data">
      @csrf
      <div class="field"><label>Nama barang</label><input required name="name" class="input" placeholder="Contoh: Nikon Z6 II"></div>
      <div class="field"><label>Kategori</label>
        <select required name="category" class="input">
          @foreach($categories as $c)
            <option value="{{ $c->name }}">{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:flex;gap:12px;">
        <div class="field" style="flex:2;"><label>Harga sewa/hari (Rp)</label><input required type="number" min="1" name="price_rent" class="input" placeholder="0"></div>
        <div class="field" style="flex:1;"><label>Jumlah unit</label><input type="number" min="1" max="99" name="stock" value="1" class="input"></div>
      </div>
      <div class="field"><label>Deskripsi kondisi barang</label><textarea name="desc" class="input" rows="3"></textarea></div>
      <div class="field">
        <label>Foto barang (wajib, minimal 1)</label>
        <input type="file" name="photos[]" class="input" accept="image/*" multiple required>
        <div class="hint"><i class="bi bi-info-circle"></i> Unggah minimal 1 foto asli.</div>
      </div>
      <button type="submit" class="btn btn-primary btn-block">
        <i class="bi bi-upload"></i> Tayangkan untuk Disewa
      </button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const detailModal = document.getElementById('modalProductDetail');
    const detailContent = document.getElementById('productDetailContent');

    const addDetailText = (container, label, value) => {
        if (value === null || value === undefined || value === '') return;
        const row = document.createElement('div');
        row.className = 'product-detail-row';
        const title = document.createElement('b');
        title.textContent = label;
        const text = document.createElement('span');
        text.textContent = value;
        row.append(title, text);
        container.append(row);
    };

    // Aksi sewa di specsheet: atur jumlah (+ / −), lalu masukkan keranjang atau sewa langsung.
    const buildRentActions = (product) => {
        const maxQty = Math.max(1, Number(product.stock) || 1);
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = product.cart_add_url;
        form.className = 'js-cart-add detail-rent-actions';

        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = window.CamRent.csrf;

        const stepper = document.createElement('div');
        stepper.className = 'qty-stepper';
        const minus = document.createElement('button');
        minus.type = 'button';
        minus.setAttribute('aria-label', 'Kurangi jumlah');
        minus.innerHTML = '<i class="bi bi-dash"></i>';
        const qty = document.createElement('input');
        qty.type = 'number';
        qty.name = 'qty';
        qty.value = 1;
        qty.min = 1;
        qty.max = maxQty;
        qty.readOnly = true;
        qty.setAttribute('aria-label', 'Jumlah unit disewa');
        const plus = document.createElement('button');
        plus.type = 'button';
        plus.setAttribute('aria-label', 'Tambah jumlah');
        plus.innerHTML = '<i class="bi bi-plus"></i>';
        const sync = () => {
            minus.disabled = Number(qty.value) <= 1;
            plus.disabled = Number(qty.value) >= maxQty;
        };
        minus.addEventListener('click', () => { qty.value = Math.max(1, Number(qty.value) - 1); sync(); });
        plus.addEventListener('click', () => { qty.value = Math.min(maxQty, Number(qty.value) + 1); sync(); });
        sync();
        stepper.append(minus, qty, plus);

        const stockHint = document.createElement('span');
        stockHint.className = 'hint';
        stockHint.textContent = `Maks. ${maxQty} unit`;

        const addButton = document.createElement('button');
        addButton.type = 'submit';
        addButton.className = 'btn btn-secondary';
        addButton.innerHTML = '<i class="bi bi-cart-plus"></i> Masukkan Keranjang';

        const rentNow = document.createElement('button');
        rentNow.type = 'submit';
        rentNow.name = 'rent_now';
        rentNow.value = '1';
        rentNow.dataset.noAjax = '';
        rentNow.className = 'btn btn-primary';
        rentNow.innerHTML = '<i class="bi bi-calendar-plus"></i> Sewa Sekarang';

        const qtyRow = document.createElement('div');
        qtyRow.className = 'detail-qty-row';
        const qtyLabel = document.createElement('b');
        qtyLabel.textContent = 'Jumlah';
        qtyRow.append(qtyLabel, stepper, stockHint);

        const buttons = document.createElement('div');
        buttons.className = 'detail-rent-buttons';
        buttons.append(addButton, rentNow);

        form.append(token, qtyRow, buttons);
        return form;
    };

    const showProductDetail = async (url) => {
        detailModal.classList.add('open');
        detailContent.replaceChildren();
        const loading = document.createElement('p');
        loading.className = 'hint';
        loading.textContent = 'Memuat specsheet...';
        detailContent.append(loading);

        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error(`Gagal memuat specsheet (${response.status}).`);
            const product = await response.json();
            detailContent.replaceChildren();

            const heading = document.createElement('h3');
            heading.id = 'productDetailTitle';
            heading.textContent = product.name;
            detailContent.append(heading);

            if (product.image) {
                const image = document.createElement('img');
                image.className = 'detail-photo';
                image.src = product.image;
                image.alt = product.name;
                detailContent.append(image);
            }

            addDetailText(detailContent, 'Merek', product.brand);
            addDetailText(detailContent, 'Kategori', product.category);
            addDetailText(detailContent, 'Pemilik', product.seller);
            addDetailText(detailContent, 'Rating', product.rating ? `${product.rating} / 5` : null);
            addDetailText(detailContent, 'Deskripsi', product.description);
            addDetailText(detailContent, 'Stok tersedia', product.stock);

            const prices = document.createElement('div');
            prices.className = 'detail-price-box';
            [
                ['Sewa / hari', product.price_per_day],
                ['Sewa / 12 jam', product.price_per_half_day],
            ].forEach(([label, price]) => {
                if (!price || price <= 0) return;
                const item = document.createElement('div');
                item.className = 'dp-item';
                const title = document.createElement('span');
                title.textContent = label;
                const amount = document.createElement('b');
                amount.textContent = `Rp ${Number(price).toLocaleString('id-ID')}`;
                item.append(title, amount);
                prices.append(item);
            });
            if (prices.childElementCount) detailContent.append(prices);

            const specsHeading = document.createElement('h4');
            specsHeading.textContent = 'Spesifikasi';
            detailContent.append(specsHeading);
            if (product.specs?.length) {
                const specs = document.createElement('ul');
                specs.className = 'product-spec-list';
                product.specs.forEach(spec => {
                    const item = document.createElement('li');
                    item.textContent = spec;
                    specs.append(item);
                });
                detailContent.append(specs);
            } else {
                const empty = document.createElement('p');
                empty.className = 'hint';
                empty.textContent = 'Spesifikasi belum tersedia.';
                detailContent.append(empty);
            }
            detailContent.append(buildRentActions(product));
        } catch (error) {
            detailContent.replaceChildren();
            const message = document.createElement('p');
            message.className = 'hint';
            message.textContent = error.message || 'Specsheet tidak dapat dimuat. Silakan coba lagi.';
            detailContent.append(message);
        }
    };

    document.querySelectorAll('[data-product-detail]').forEach(button => {
        button.addEventListener('click', () => showProductDetail(button.dataset.detailUrl));
    });

    detailModal.querySelectorAll('[data-detail-close]').forEach(button => {
        button.addEventListener('click', () => detailModal.classList.remove('open'));
    });
    detailModal.addEventListener('click', event => {
        if (event.target === detailModal) detailModal.classList.remove('open');
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') detailModal.classList.remove('open');
    });

});
</script>
@endpush