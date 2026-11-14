@extends('layouts.app')

@section('page', 'marketplace')
@section('title', 'Catalog · CamRent')
@section('breadcrumb', 'Catalog')

@section('content')
<header class="page-head">
  <div class="app-container">
    <h1>Sewa peralatan kreatif</h1>
    <p>Pilih alat dari mitra terpercaya, masukkan ke keranjang, lalu sewa sekaligus dalam satu kali checkout.</p>
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
      <button type="button" class="btn btn-outline" onclick="document.getElementById('modalSewakan').classList.add('open')">
        <i class="bi bi-plus-circle"></i> Sewakan Barang Saya
      </button>
    @else
      <a href="{{ route('login') }}" class="btn btn-outline">
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
    <div class="mp-result-info">
      <p class="mp-result-count"><b>{{ $products->count() }} alat</b> tersedia untuk disewa{{ $category ? ' · '.$category : '' }}</p>
      <p class="mp-result-tip"><i class="bi bi-lightbulb"></i> Klik foto atau nama alat untuk melihat specsheet</p>
    </div>
  </div>

  <div class="product-grid mp-grid">
    @forelse($products as $p)
      @include('partials.product-card', [
        'p' => $p,
        'onMarketplace' => true,
        'highlight' => $highlight == $p->id ? 'highlighted' : '',
      ])
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
      <div class="responsive-field-row" style="display:flex;gap:12px;">
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

    const el = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
    };
    const icon = (name) => {
        const i = document.createElement('i');
        i.className = `bi ${name}`;
        return i;
    };
    const iconButton = (className, label, iconName) => {
        const button = el('button', className);
        button.type = 'button';
        button.setAttribute('aria-label', label);
        button.append(icon(iconName));
        return button;
    };

    // ---- Lightbox: foto penuh tanpa terpotong ----
    const lightbox = el('div', 'pd-lightbox');
    lightbox.hidden = true;
    const lightboxImage = el('img', 'pd-lightbox-img');
    const lightboxClose = iconButton('pd-lightbox-close', 'Tutup foto', 'bi-x-lg');
    lightbox.append(lightboxImage, lightboxClose);
    document.body.append(lightbox);
    const closeLightbox = () => { lightbox.hidden = true; };
    lightbox.addEventListener('click', closeLightbox);

    // ---- Galeri foto (beberapa sudut) ----
    let activeGallery = null;

    const buildGallery = (product) => {
        const images = product.images?.length
            ? product.images
            : (product.image ? [{ url: product.image, label: 'Tampak utama' }] : []);

        const gallery = el('div', 'pd-gallery');
        const stage = el('div', 'pd-stage');
        gallery.append(stage);

        if (!images.length) {
            stage.classList.add('pd-stage-empty');
            stage.append(icon('bi-image'), el('span', null, 'Foto belum tersedia'));
            activeGallery = null;
            return gallery;
        }

        let index = 0;
        const image = el('img', 'pd-stage-img');
        const empty = el('div', 'pd-stage-fallback');
        empty.hidden = true;
        empty.append(icon('bi-image'), el('span', null, 'Foto tidak dapat dimuat'));
        image.addEventListener('error', () => { image.hidden = true; empty.hidden = false; });
        image.addEventListener('load', () => { image.hidden = false; empty.hidden = true; });

        const angle = el('span', 'pd-angle');
        const counter = el('span', 'pd-counter');
        const zoom = iconButton('pd-zoom', 'Perbesar foto', 'bi-arrows-fullscreen');
        stage.append(image, empty, angle, counter, zoom);

        const thumbButtons = [];
        const show = (next) => {
            index = (next + images.length) % images.length;
            const current = images[index];
            image.hidden = false;
            empty.hidden = true;
            image.src = current.url;
            image.alt = `${product.name} — ${current.label}`;
            angle.textContent = current.label;
            counter.textContent = `${index + 1} / ${images.length}`;
            counter.hidden = images.length < 2;
            thumbButtons.forEach((thumb, i) => {
                const active = i === index;
                thumb.classList.toggle('active', active);
                thumb.setAttribute('aria-current', active ? 'true' : 'false');
                if (active) thumb.scrollIntoView({ block: 'nearest', inline: 'center' });
            });
        };

        const openZoom = () => {
            lightboxImage.src = images[index].url;
            lightboxImage.alt = image.alt;
            lightbox.hidden = false;
        };
        image.addEventListener('click', openZoom);
        zoom.addEventListener('click', openZoom);

        if (images.length > 1) {
            const prev = iconButton('pd-nav pd-prev', 'Foto sebelumnya', 'bi-chevron-left');
            const next = iconButton('pd-nav pd-next', 'Foto berikutnya', 'bi-chevron-right');
            prev.addEventListener('click', () => show(index - 1));
            next.addEventListener('click', () => show(index + 1));
            stage.append(prev, next);

            // geser kiri/kanan di layar sentuh
            let startX = null;
            stage.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
            stage.addEventListener('touchend', e => {
                if (startX === null) return;
                const delta = e.changedTouches[0].clientX - startX;
                if (Math.abs(delta) > 40) show(index + (delta < 0 ? 1 : -1));
                startX = null;
            });

            const strip = el('div', 'pd-thumbs');
            images.forEach((item, i) => {
                const thumb = el('button', 'pd-thumb');
                thumb.type = 'button';
                thumb.title = item.label;
                thumb.setAttribute('aria-label', `Lihat ${item.label}`);
                const picture = el('img');
                picture.src = item.url;
                picture.alt = '';
                picture.loading = 'lazy';
                thumb.append(picture);
                thumb.addEventListener('click', () => show(i));
                thumbButtons.push(thumb);
                strip.append(thumb);
            });
            gallery.append(strip);
        } else {
            gallery.append(el('p', 'pd-photo-hint', 'Klik foto untuk memperbesar.'));
        }

        show(0);
        activeGallery = { show, move: (step) => show(index + step), count: images.length };
        return gallery;
    };

    // ---- Tabel spesifikasi ----
    const buildSpecs = (product) => {
        const wrap = el('section', 'pd-specs');
        wrap.append(el('h4', null, 'Spesifikasi'));

        const rows = [];
        if (product.brand) rows.push({ label: 'Merek', value: product.brand });
        (product.spec_rows ?? []).forEach(row => rows.push(row));
        const features = product.spec_features ?? [];

        if (!rows.length && !features.length) {
            wrap.append(el('p', 'hint', 'Spesifikasi belum tersedia.'));
            return wrap;
        }

        if (rows.length) {
            const table = el('dl', 'spec-table');
            rows.forEach(row => {
                const line = el('div', 'spec-row');
                line.append(el('dt', null, row.label), el('dd', null, row.value));
                table.append(line);
            });
            wrap.append(table);
        }
        if (features.length) {
            if (rows.length) wrap.append(el('h5', 'pd-sub', 'Fitur'));
            const list = el('ul', 'product-spec-list');
            features.forEach(text => list.append(el('li', null, text)));
            wrap.append(list);
        }
        return wrap;
    };

    const buildPrices = (product) => {
        const prices = el('div', 'detail-price-box');
        [
            ['Sewa / hari', product.price_per_day],
            ['Sewa / 12 jam', product.price_per_half_day],
        ].forEach(([label, price]) => {
            if (!price || price <= 0) return;
            const item = el('div', 'dp-item');
            item.append(el('span', null, label), el('b', null, `Rp ${Number(price).toLocaleString('id-ID')}`));
            prices.append(item);
        });
        return prices;
    };

    const buildInfo = (product) => {
        const info = el('div', 'pd-info');

        const chips = el('div', 'pd-chips');
        if (product.category) chips.append(el('span', 'pd-chip', product.category));
        if (product.rating) {
            const rating = el('span', 'pd-chip pd-chip-rating');
            rating.append(icon('bi-star-fill'), document.createTextNode(` ${product.rating} / 5`));
            chips.append(rating);
        }
        const stock = el('span', 'pd-chip');
        stock.append(icon('bi-box-seam'), document.createTextNode(` Stok ${product.stock}`));
        chips.append(stock);
        info.append(chips);

        const heading = el('h3', null, product.name);
        heading.id = 'productDetailTitle';
        info.append(heading);

        const owner = el('p', 'pd-owner');
        owner.append(icon('bi-shop-window'), document.createTextNode(` ${product.seller}`));
        info.append(owner);

        if (product.description) info.append(el('p', 'pd-desc', product.description));
        const prices = buildPrices(product);
        if (prices.childElementCount) info.append(prices);
        info.append(buildSpecs(product));
        return info;
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
        activeGallery = null;
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

            const layout = el('div', 'pd-layout');
            layout.append(buildGallery(product), buildInfo(product));
            detailContent.append(layout);
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
        if (event.key === 'Escape') {
            // Esc menutup foto zoom dulu, baru modal.
            if (!lightbox.hidden) closeLightbox();
            else detailModal.classList.remove('open');
            return;
        }
        if (!detailModal.classList.contains('open') || !activeGallery || activeGallery.count < 2) return;
        if (event.target.closest('input, textarea, select')) return;
        if (event.key === 'ArrowLeft') activeGallery.move(-1);
        if (event.key === 'ArrowRight') activeGallery.move(1);
    });

});
</script>
@endpush