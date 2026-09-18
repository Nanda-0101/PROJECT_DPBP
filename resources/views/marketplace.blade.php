@extends('layouts.app')

@section('page', 'marketplace')
@section('title', 'Marketplace · CamRent')

@section('content')
<header class="section" style="padding-bottom:20px;">
  <div class="app-container">
    <span class="eyebrow badge badge-buy"><i class="bi bi-shop"></i> Marketplace</span>
    <h1 style="font-size:2.1rem;margin:12px 0 8px;">Sewa atau beli peralatan kreatif</h1>
    <p style="max-width:600px;">Semua alat dari mitra terpercaya. Ingin menjual alatmu sendiri? Klik "Jual Barang Saya".</p>
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
    <select name="type" class="input mp-select" onchange="this.form.submit()">
      <option value="">Sewa & Jual</option>
      <option value="sewa" @selected($type === 'sewa')>Khusus disewa</option>
      <option value="jual" @selected($type === 'jual')>Khusus dijual</option>
    </select>
    <select name="sort" class="input mp-select" onchange="this.form.submit()">
      <option value="trending"   @selected($sort === 'trending')>Urutkan: Trending</option>
      <option value="price-asc"  @selected($sort === 'price-asc')>Harga terendah</option>
      <option value="price-desc" @selected($sort === 'price-desc')>Harga tertinggi</option>
      <option value="rating"     @selected($sort === 'rating')>Rating tertinggi</option>
    </select>
    @auth
      <button type="button" class="btn btn-primary" onclick="document.getElementById('modalJual').classList.add('open')">
        <i class="bi bi-plus-circle"></i> Jual Barang Saya
      </button>
    @else
      <a href="{{ route('login') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Jual Barang Saya
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

  <div class="product-grid mp-grid" style="margin-top:22px;">
    @forelse($products as $p)
      <div class="product-card {{ $highlight == $p->id ? 'highlighted' : '' }}">
        @include('partials.product-card', ['p' => $p])
      </div>
    @empty
      <div class="empty-state" style="grid-column:1/-1;">
        <i class="bi bi-search"></i>
        <p>Tidak ada alat yang cocok dengan pencarianmu.</p>
      </div>
    @endforelse
  </div>
</section>

{{-- MODAL: Konfirmasi Beli --}}
<div class="modal-backdrop" id="modalBuy">
  <div class="modal-box glass-strong">
    <button type="button" class="modal-close" onclick="document.getElementById('modalBuy').classList.remove('open')">
      <i class="bi bi-x-lg"></i>
    </button>
    <h3 style="margin-bottom:6px;"><i class="bi bi-cart-check gradient-text"></i> Konfirmasi Pembelian</h3>
    <p style="margin-bottom:18px;">Lengkapi data berikut untuk menyelesaikan pembelian.</p>
    <form id="buyForm" method="POST" action="">
      @csrf
      <input type="hidden" id="buyProductId" name="product_id">
      <div id="buySummary" class="tag" style="display:block;padding:12px 14px;margin-bottom:16px;font-size:.9rem;"></div>
      <div class="field"><label>Nama pembeli</label><input required name="name" class="input" placeholder="Nama lengkap"></div>
      <div class="field"><label>No. HP / WhatsApp</label><input required name="phone" class="input" placeholder="08xxxxxxxxxx"></div>
      <div class="field"><label>Alamat pengiriman</label><textarea required name="address" class="input" rows="2"></textarea></div>
      <div class="field">
        <label>Metode pembayaran</label>
        <div style="display:flex;gap:10px;">
          <label class="radio-card" style="flex:1;"><input type="radio" name="payment" value="Transfer" checked> Transfer</label>
          <label class="radio-card" style="flex:1;"><input type="radio" name="payment" value="QRIS"> QRIS</label>
          <label class="radio-card" style="flex:1;"><input type="radio" name="payment" value="Cash"> COD</label>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block">
        <i class="bi bi-check-circle"></i> Selesaikan Pembelian
      </button>
    </form>
  </div>
</div>

{{-- MODAL: Jual Barang --}}
<div class="modal-backdrop" id="modalJual">
  <div class="modal-box glass-strong">
    <button type="button" class="modal-close" onclick="document.getElementById('modalJual').classList.remove('open')">
      <i class="bi bi-x-lg"></i>
    </button>
    <h3 style="margin-bottom:6px;"><i class="bi bi-tag gradient-text"></i> Jual Barang Saya</h3>
    <p style="margin-bottom:18px;">Isi detail alat yang ingin dijual atau disewakan.</p>
    <form method="POST" action="{{ route('marketplace.sell') }}" enctype="multipart/form-data">
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
        <div class="field" style="flex:1;"><label>Harga jual (Rp)</label><input type="number" min="0" name="price_sale" class="input" placeholder="0"></div>
        <div class="field" style="flex:1;"><label>Harga sewa/hari (Rp)</label><input type="number" min="0" name="price_rent" class="input" placeholder="0"></div>
      </div>
      <div class="field"><label>Deskripsi kondisi barang</label><textarea name="desc" class="input" rows="3"></textarea></div>
      <div class="field">
        <label>Foto barang (wajib, minimal 1)</label>
        <input type="file" name="photos[]" class="input" accept="image/*" multiple required>
        <div class="hint"><i class="bi bi-info-circle"></i> Unggah minimal 1 foto asli.</div>
      </div>
      <button type="submit" class="btn btn-primary btn-block">
        <i class="bi bi-upload"></i> Tayangkan Barang
      </button>
    </form>
  </div>
</div>
@endsection