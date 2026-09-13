@extends('layouts.app')

@section('page', 'booking')
@section('title', 'Booking Kamera · CamRent')

@section('content')
<header class="section" style="padding-bottom:10px;">
  <div class="app-container">
    <span class="eyebrow badge badge-rent"><i class="bi bi-calendar-check"></i> Booking</span>
    <h1 style="font-size:2.1rem;margin:12px 0 8px;">Booking peralatan sewa</h1>
    <p style="max-width:600px;">Pilih alat, tentukan tanggal lewat kalender, dan selesaikan pembayaran dalam beberapa langkah.</p>
  </div>
</header>

<section class="app-container booking-layout">

  <div class="booking-main">
    <div class="glass card bk-step">
      <div class="bk-step-title"><span class="bk-num">1</span> Pilih alat</div>
      <select id="productSelect" class="input"></select>
      <div id="productPreview" class="bk-product-preview"></div>
    </div>

    <div class="glass card bk-step">
      <div class="bk-step-title"><span class="bk-num">2</span> Pilih tanggal sewa</div>
      <div class="calendar-wrap">
        <div class="calendar-head">
          <button type="button" class="btn btn-ghost btn-sm" id="calPrev"><i class="bi bi-chevron-left"></i></button>
          <span id="calMonthLabel" style="font-weight:800;"></span>
          <button type="button" class="btn btn-ghost btn-sm" id="calNext"><i class="bi bi-chevron-right"></i></button>
        </div>
        <div class="calendar-dow">
          <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
        </div>
        <div class="calendar-grid" id="calGrid"></div>
        <div class="calendar-legend">
          <span><i class="dot dot-start"></i> Mulai</span>
          <span><i class="dot dot-range"></i> Dalam rentang</span>
          <span><i class="dot dot-end"></i> Selesai</span>
        </div>
      </div>
      <div class="bk-picked" id="bkPicked">Belum ada tanggal dipilih. Klik tanggal mulai, lalu tanggal selesai.</div>

      <div id="durationChoice" class="field" style="display:none;margin-top:14px;">
        <label>Durasi sewa (tanggal sama)</label>
        <div style="display:flex;gap:10px;">
          <label class="radio-card" style="flex:1;" data-dur="half"><input type="radio" name="durType" value="half"> 12 Jam</label>
          <label class="radio-card selected" style="flex:1;" data-dur="full"><input type="radio" name="durType" value="full" checked> 1 Hari Penuh</label>
        </div>
      </div>
    </div>

    <div class="glass card bk-step">
      <div class="bk-step-title"><span class="bk-num">3</span> Data penyewa</div>
      <div class="field"><label>Nama penyewa</label><input class="input" id="renterName" placeholder="Nama lengkap sesuai KTP"></div>
      <div style="display:flex;gap:14px;">
        <div class="field" style="flex:1;"><label>No. HP / WhatsApp</label><input class="input" id="renterPhone" placeholder="08xxxxxxxxxx"></div>
        <div class="field" style="flex:1;"><label>Kota pengambilan</label><input class="input" id="renterCity" placeholder="Contoh: Jakarta Selatan" list="cityList">
          <datalist id="cityList"><option>Jakarta</option><option>Bandung</option><option>Surabaya</option><option>Yogyakarta</option><option>Medan</option></datalist>
        </div>
      </div>
      <div class="field"><label>Catatan tambahan (opsional)</label><textarea class="input" id="renterNote" rows="2" placeholder="Contoh: ambil sore hari, dsb."></textarea></div>
    </div>

    <div class="glass card bk-step">
      <div class="bk-step-title"><span class="bk-num">4</span> Jaminan</div>
      <div class="field"><label>Jenis jaminan yang dititipkan</label>
        <select id="guaranteeSelect" class="input">
          <option value="KTP">KTP asli</option>
          <option value="SIM">SIM asli</option>
          <option value="Paspor">Paspor asli</option>
          <option value="KTP + Deposit">KTP asli + deposit tambahan</option>
        </select>
      </div>
      <div class="hint"><i class="bi bi-shield-check"></i> Jaminan dikembalikan penuh saat alat dikembalikan dalam kondisi baik & tepat waktu.</div>
    </div>

    <div class="glass card bk-step">
      <div class="bk-step-title"><span class="bk-num">5</span> Pembayaran</div>
      <div class="field">
        <label>Skema pembayaran</label>
        <div style="display:flex;gap:10px;">
          <label class="radio-card selected" style="flex:1;" data-pay="DP"><input type="radio" name="payType" value="DP" checked> DP 30%</label>
          <label class="radio-card" style="flex:1;" data-pay="Bayar Penuh"><input type="radio" name="payType" value="Bayar Penuh"> Bayar Penuh</label>
        </div>
      </div>
      <div class="field">
        <label>Metode pembayaran</label>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <label class="radio-card selected" style="flex:1;min-width:110px;" data-method="Cash"><input type="radio" name="payMethod" value="Cash" checked> <i class="bi bi-cash-coin"></i> Cash</label>
          <label class="radio-card" style="flex:1;min-width:110px;" data-method="Transfer"><input type="radio" name="payMethod" value="Transfer"> <i class="bi bi-bank"></i> Transfer</label>
          <label class="radio-card" style="flex:1;min-width:110px;" data-method="QRIS"><input type="radio" name="payMethod" value="QRIS"> <i class="bi bi-qr-code"></i> QRIS</label>
        </div>
      </div>
      <div id="paymentInfoBox" class="payment-info-box"></div>
    </div>
  </div>

  <aside class="booking-side">
    <div class="glass card summary-card">
      <h4 style="margin-bottom:14px;"><i class="bi bi-receipt gradient-text"></i> Ringkasan Booking</h4>
      <div id="summaryBody"></div>
      <button class="btn btn-primary btn-block" id="submitBooking" style="margin-top:16px;"><i class="bi bi-check2-circle"></i> Konfirmasi Booking</button>
      <div class="hint" style="margin-top:10px;text-align:center;"><i class="bi bi-headset"></i> Salah pilih alat / tanggal? <a href="{{ url('/cs') }}" style="color:var(--violet-deep);font-weight:700;">Hubungi CS</a></div>
    </div>
  </aside>
</section>
@endsection