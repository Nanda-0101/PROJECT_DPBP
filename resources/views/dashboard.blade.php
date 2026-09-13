@extends('layouts.app')

@section('page', 'dashboard')
@section('title', 'Dashboard · CamRent')

@section('content')
<section class="app-container dash-wrap">
  <div class="dash-head glass card">
    <div class="dash-avatar" id="dashAvatar"></div>
    <div style="flex:1;">
      <h2 id="dashName" style="font-size:1.4rem;"></h2>
      <div id="dashRole" class="hint"></div>
    </div>
    <span id="dashStatusBadge"></span>
  </div>

  <div class="dash-tabs" id="dashTabs"></div>

  <div class="dash-panel" id="panelRiwayat">
    <div class="section-head" style="margin-bottom:14px;">
      <h3 style="font-size:1.2rem;"><i class="bi bi-clock-history gradient-text"></i> Riwayat Sewa & Pembelian</h3>
    </div>
    <div id="riwayatList"></div>
  </div>

  <div class="dash-panel" id="panelToko" style="display:none;">
    <div class="section-head" style="margin-bottom:14px;">
      <h3 style="font-size:1.2rem;"><i class="bi bi-shop gradient-text"></i> Pesanan Masuk ke Toko Saya</h3>
    </div>
    <div id="tokoOrderList"></div>

    <div class="section-head" style="margin:30px 0 14px;">
      <h3 style="font-size:1.2rem;"><i class="bi bi-box-seam gradient-text"></i> Produk Saya di Marketplace</h3>
      <a href="{{ url('/marketplace') }}" class="link-more">Tambah produk <i class="bi bi-arrow-right"></i></a>
    </div>
    <div id="tokoProductList" class="dash-product-grid"></div>
  </div>

  <div class="dash-panel" id="panelProfil" style="display:none;">
    <div class="glass card" style="max-width:520px;padding:24px;">
      <h3 style="font-size:1.1rem;margin-bottom:16px;"><i class="bi bi-person-badge gradient-text"></i> Info Akun</h3>
      <div id="profilBody"></div>
    </div>
  </div>
</section>

<div class="modal-backdrop" id="modalBookingDetail">
  <div class="modal-box glass-strong" id="modalBookingDetailBody"></div>
</div>
@endsection