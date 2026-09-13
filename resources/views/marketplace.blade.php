@extends('layouts.app')

@section('page', 'marketplace')
@section('title', 'Marketplace · CamRent')

@section('content')
<header class="section" style="padding-bottom:20px;">
  <div class="app-container">
    <span class="eyebrow badge badge-buy"><i class="bi bi-shop"></i> Marketplace</span>
    <h1 style="font-size:2.1rem;margin:12px 0 8px;">Sewa atau beli peralatan kreatif</h1>
    <p style="max-width:600px;">Semua alat dari mitra terpercaya — bisa disewa harian/12 jam atau dibeli langsung. Ingin menjual alatmu sendiri? Klik "Jual Barang Saya".</p>
  </div>
</header>

<section class="app-container">
  <div class="mp-toolbar glass">
    <div class="mp-search">
      <i class="bi bi-search"></i>
      <input type="text" id="searchInput" class="input" placeholder="Cari nama alat, merek, atau kategori...">
    </div>
    <select id="categoryFilter" class="input mp-select"><option value="">Semua kategori</option></select>
    <select id="typeFilter" class="input mp-select">
      <option value="">Sewa & Jual</option>
      <option value="sewa">Khusus disewa</option>
      <option value="jual">Khusus dijual</option>
    </select>
    <select id="sortFilter" class="input mp-select">
      <option value="trending">Urutkan: Trending</option>
      <option value="price-asc">Harga terendah</option>
      <option value="price-desc">Harga tertinggi</option>
      <option value="rating">Rating tertinggi</option>
    </select>
    <button class="btn btn-primary" id="btnJual"><i class="bi bi-plus-circle"></i> Jual Barang Saya</button>
  </div>

  <div class="mp-chip-row" id="chipRow"></div>

  <div class="product-grid mp-grid" id="marketGrid" style="margin-top:22px;"></div>
  <div class="empty-state" id="emptyState" style="display:none;">
    <i class="bi bi-search"></i>
    <p>Tidak ada alat yang cocok dengan pencarianmu. Coba kata kunci lain.</p>
  </div>
</section>

{{-- Modal: Detail Produk --}}
<div class="modal-backdrop" id="modalDetail">
  <div class="modal-box glass-strong" id="modalDetailBody"></div>
</div>

{{-- Konfirmasi Beli --}}
<div class="modal-backdrop" id="modalBuy">
  <div class="modal-box glass-strong">
    <button class="modal-close" onclick="closeModal('modalBuy')"><i class="bi bi-x-lg"></i></button>
    <h3 style="margin-bottom:6px;"><i class="bi bi-cart-check gradient-text"></i> Konfirmasi Pembelian</h3>
    <p style="margin-bottom:18px;">Lengkapi data berikut untuk menyelesaikan pembelian.</p>
    <form id="buyForm">
      <input type="hidden" id="buyProductId">
      <div id="buySummary" class="tag" style="display:block;padding:12px 14px;margin-bottom:16px;font-size:.9rem;"></div>
      <div class="field"><label>Nama pembeli</label><input required class="input" id="buyName" placeholder="Nama lengkap"></div>
      <div class="field"><label>No. HP / WhatsApp</label><input required class="input" id="buyPhone" placeholder="08xxxxxxxxxx"></div>
      <div class="field"><label>Alamat pengiriman</label><textarea required class="input" id="buyAddress" rows="2" placeholder="Alamat lengkap"></textarea></div>
      <div class="field">
        <label>Metode pembayaran</label>
        <div style="display:flex;gap:10px;">
          <label class="radio-card" style="flex:1;"><input type="radio" name="buyPay" value="Transfer" checked> Transfer</label>
          <label class="radio-card" style="flex:1;"><input type="radio" name="buyPay" value="QRIS"> QRIS</label>
          <label class="radio-card" style="flex:1;"><input type="radio" name="buyPay" value="Cash"> COD</label>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-check-circle"></i> Selesaikan Pembelian</button>
    </form>
  </div>
</div>

{{-- Jual Barang --}}
<div class="modal-backdrop" id="modalJual">
  <div class="modal-box glass-strong">
    <button class="modal-close" onclick="closeModal('modalJual')"><i class="bi bi-x-lg"></i></button>
    <h3 style="margin-bottom:6px;"><i class="bi bi-tag gradient-text"></i> Jual Barang Saya</h3>
    <p style="margin-bottom:18px;">Untuk user biasa maupun mitra — isi detail alat yang ingin dijual atau disewakan.</p>
    <form id="jualForm">
      <div class="field"><label>Nama barang</label><input required class="input" id="jualNama" placeholder="Contoh: Nikon Z6 II"></div>
      <div class="field"><label>Kategori</label>
        <select required class="input" id="jualKategori">
          <option>Kamera</option><option>Lensa</option><option>Lighting</option><option>Audio</option><option>Gimbal</option><option>Tripod</option><option>Video</option>
        </select>
      </div>
      <div style="display:flex;gap:12px;">
        <div class="field" style="flex:1;"><label>Harga jual (Rp)</label><input type="number" min="0" class="input" id="jualHargaJual" placeholder="0 jika tidak dijual"></div>
        <div class="field" style="flex:1;"><label>Harga sewa/hari (Rp)</label><input type="number" min="0" class="input" id="jualHargaSewa" placeholder="0 jika tidak disewakan"></div>
      </div>
      <div class="field"><label>Deskripsi kondisi barang</label><textarea class="input" id="jualDesk" rows="3" placeholder="Kondisi, kelengkapan, tahun beli, dll."></textarea></div>
      <div class="field">
        <label>Foto barang (wajib, minimal 1)</label>
        <input type="file" class="input" id="jualFoto" accept="image/*" multiple>
        <div class="hint"><i class="bi bi-info-circle"></i> Unggah minimal 1 foto asli barangmu agar calon penyewa/pembeli lebih percaya.</div>
        <div id="jualFotoPreview" class="photo-preview-row"></div>
      </div>
      <div class="hint" style="margin-bottom:14px;"><i class="bi bi-info-circle"></i> Barang akan tampil di marketplace setelah dicek singkat oleh tim CamRent.</div>
      <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-upload"></i> Tayangkan Barang</button>
    </form>
  </div>
</div>
@endsection