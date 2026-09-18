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

<form method="POST" action="{{ route('booking.store') }}" id="bookingForm">
  @csrf
  <section class="app-container booking-layout">
    <div class="booking-main">

      {{-- Step 1 --}}
      <div class="glass card bk-step">
        <div class="bk-step-title"><span class="bk-num">1</span> Pilih alat</div>
        <select id="productSelect" name="product_id" class="input">
          @foreach($products as $p)
            <option value="{{ $p->id }}"
                    data-price-day="{{ $p->price_per_day }}"
                    data-price-half="{{ $p->price_per_half_day }}"
                    data-name="{{ $p->name }}"
                    data-category="{{ $p->category->name ?? '-' }}"
                    data-stock="{{ $p->stock }}"
                    @selected($selected == $p->id)>
              {{ $p->name }} — Rp {{ number_format($p->price_per_day,0,',','.') }}/hari
            </option>
          @endforeach
        </select>
        <div id="productPreview" class="bk-product-preview"></div>
      </div>

      {{-- Step 2 --}}
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
        </div>
        <div class="bk-picked" id="bkPicked">Belum ada tanggal dipilih.</div>

        <input type="hidden" name="start_date" id="startDateInput">
        <input type="hidden" name="end_date" id="endDateInput">

        <div id="durationChoice" class="field" style="display:none;margin-top:14px;">
          <label>Durasi sewa (tanggal sama)</label>
          <div style="display:flex;gap:10px;">
            <label class="radio-card" style="flex:1;">
              <input type="radio" name="duration_type" value="half"> 12 Jam
            </label>
            <label class="radio-card selected" style="flex:1;">
              <input type="radio" name="duration_type" value="full" checked> 1 Hari Penuh
            </label>
          </div>
        </div>
      </div>

      {{-- Step 3 --}}
      <div class="glass card bk-step">
        <div class="bk-step-title"><span class="bk-num">3</span> Data penyewa</div>
        <div class="field"><label>Nama penyewa</label><input name="renter_name" class="input" placeholder="Nama lengkap sesuai KTP" value="{{ old('renter_name', auth()->user()->name ?? '') }}" required></div>
        <div style="display:flex;gap:14px;">
          <div class="field" style="flex:1;"><label>No. HP / WhatsApp</label><input name="renter_phone" class="input" placeholder="08xxxxxxxxxx" value="{{ old('renter_phone', auth()->user()->phone ?? '') }}" required></div>
          <div class="field" style="flex:1;"><label>Kota pengambilan</label><input name="renter_city" class="input" placeholder="Contoh: Jakarta Selatan"></div>
        </div>
        <div class="field"><label>Catatan tambahan (opsional)</label><textarea name="note" class="input" rows="2"></textarea></div>
      </div>

      {{-- Step 4 --}}
      <div class="glass card bk-step">
        <div class="bk-step-title"><span class="bk-num">4</span> Jaminan</div>
        <div class="field">
          <label>Jenis jaminan yang dititipkan</label>
          <select name="guarantee" class="input" required>
            <option value="KTP">KTP asli</option>
            <option value="SIM">SIM asli</option>
            <option value="Paspor">Paspor asli</option>
            <option value="KTP + Deposit">KTP asli + deposit tambahan</option>
          </select>
        </div>
        <div class="hint"><i class="bi bi-shield-check"></i> Jaminan dikembalikan penuh saat alat dikembalikan dalam kondisi baik.</div>
      </div>

      {{-- Step 5 --}}
      <div class="glass card bk-step">
        <div class="bk-step-title"><span class="bk-num">5</span> Pembayaran</div>
        <div class="field">
          <label>Skema pembayaran</label>
          <div style="display:flex;gap:10px;">
            <label class="radio-card selected" style="flex:1;">
              <input type="radio" name="payment_type" value="DP" checked> DP 30%
            </label>
            <label class="radio-card" style="flex:1;">
              <input type="radio" name="payment_type" value="Bayar Penuh"> Bayar Penuh
            </label>
          </div>
        </div>
        <div class="field">
          <label>Metode pembayaran</label>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <label class="radio-card selected" style="flex:1;min-width:110px;">
              <input type="radio" name="payment_method" value="Cash" checked> <i class="bi bi-cash-coin"></i> Cash
            </label>
            <label class="radio-card" style="flex:1;min-width:110px;">
              <input type="radio" name="payment_method" value="Transfer"> <i class="bi bi-bank"></i> Transfer
            </label>
            <label class="radio-card" style="flex:1;min-width:110px;">
              <input type="radio" name="payment_method" value="QRIS"> <i class="bi bi-qr-code"></i> QRIS
            </label>
          </div>
        </div>
      </div>
    </div>

    {{-- Ringkasan --}}
    <aside class="booking-side">
      <div class="glass card summary-card">
        <h4 style="margin-bottom:14px;"><i class="bi bi-receipt gradient-text"></i> Ringkasan Booking</h4>
        <div id="summaryBody">
          <div class="sum-empty">Pilih alat & tanggal untuk melihat ringkasan.</div>
        </div>
        <button type="submit" class="btn btn-primary btn-block" style="margin-top:16px;">
          <i class="bi bi-check2-circle"></i> Konfirmasi Booking
        </button>
        <div class="hint" style="margin-top:10px;text-align:center;">
          <i class="bi bi-headset"></i> Salah pilih?
          <a href="{{ route('cs') }}" style="color:var(--violet-deep);font-weight:700;">Hubungi CS</a>
        </div>
      </div>
    </aside>
  </section>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const sel       = document.getElementById('productSelect');
    const calDate   = new Date();
    let startDate = null, endDate = null;

    const $start = document.getElementById('startDateInput');
    const $end   = document.getElementById('endDateInput');

    function dateKey(d) { return d.toISOString().slice(0,10); }
    function sameDay(a,b){ return a && b && dateKey(a) === dateKey(b); }
    function formatDateID(d){ return d.toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}); }

    function currentProduct() {
        const opt = sel.options[sel.selectedIndex];
        return {
            id: sel.value,
            name: opt.dataset.name,
            category: opt.dataset.category,
            stock: opt.dataset.stock,
            priceDay: +opt.dataset.priceDay,
            priceHalf: +opt.dataset.priceHalf,
        };
    }

    function renderPreview() {
        const p = currentProduct();
        const box = document.getElementById('productPreview');
        box.innerHTML = `
            <div class="bpp-icon"><i class="bi bi-camera"></i></div>
            <div>
                <div style="font-weight:800;">${p.name}</div>
                <div class="hint">${p.category} · Stok: ${p.stock}</div>
            </div>
            <div class="bpp-price">
                <div>Rp ${p.priceHalf.toLocaleString('id-ID')} <span class="hint">/12 jam</span></div>
                <div>Rp ${p.priceDay.toLocaleString('id-ID')} <span class="hint">/hari</span></div>
            </div>`;
    }

    function renderCalendar() {
        const y = calDate.getFullYear(), m = calDate.getMonth();
        document.getElementById('calMonthLabel').textContent = `${monthNames[m]} ${y}`;
        const first = new Date(y, m, 1);
        let offset = first.getDay() - 1; if (offset < 0) offset = 6;
        const days = new Date(y, m+1, 0).getDate();
        const grid = document.getElementById('calGrid');
        let html = '';
        for (let i = 0; i < offset; i++) html += `<div class="cal-cell blank"></div>`;
        const today = new Date(); today.setHours(0,0,0,0);
        for (let d = 1; d <= days; d++) {
            const dt = new Date(y, m, d); dt.setHours(0,0,0,0);
            let cls = 'cal-cell';
            if (dt < today) cls += ' disabled';
            if (sameDay(dt, today)) cls += ' today';
            if (sameDay(dt, startDate)) cls += ' start';
            if (sameDay(dt, endDate)) cls += ' end';
            if (startDate && endDate && dt > startDate && dt < endDate) cls += ' in-range';
            html += `<div class="${cls}" data-date="${dateKey(dt)}">${d}</div>`;
        }
        grid.innerHTML = html;
        grid.querySelectorAll('.cal-cell:not(.blank):not(.disabled)').forEach(cell => {
            cell.addEventListener('click', () => pickDate(new Date(cell.dataset.date)));
        });
    }

    function pickDate(d) {
        d.setHours(0,0,0,0);
        if (!startDate || (startDate && endDate)) { startDate = d; endDate = null; }
        else if (d < startDate) { startDate = d; }
        else { endDate = d; }
        $start.value = dateKey(startDate);
        $end.value   = dateKey(endDate || startDate);
        document.getElementById('durationChoice').style.display =
            (!endDate || sameDay(startDate, endDate)) ? 'block' : 'none';
        renderCalendar();
        renderPicked();
        renderSummary();
    }

    function renderPicked() {
        const box = document.getElementById('bkPicked');
        if (!startDate) { box.textContent = 'Belum ada tanggal dipilih.'; return; }
        if (!endDate)   { box.innerHTML = `Mulai: <b>${formatDateID(startDate)}</b> — pilih tanggal selesai.`; return; }
        box.innerHTML = `Sewa: <b>${formatDateID(startDate)}</b> s/d <b>${formatDateID(endDate)}</b>`;
    }

    function computePrice() {
        if (!startDate) return null;
        const p = currentProduct();
        const end = endDate || startDate;
        const durType = document.querySelector('input[name="duration_type"]:checked').value;
        let duration, subtotal;
        if (sameDay(startDate, end)) {
            if (durType === 'half') { duration = '12 Jam'; subtotal = p.priceHalf || Math.round(p.priceDay * 0.6); }
            else { duration = '1 Hari'; subtotal = p.priceDay; }
        } else {
            const days = Math.round((end - startDate) / 86400000) + 1;
            duration = days + ' Hari';
            subtotal = p.priceDay * days;
        }
        return { duration, subtotal, dp: Math.round(subtotal * 0.3), end };
    }

    function renderSummary() {
        const body = document.getElementById('summaryBody');
        const price = computePrice();
        if (!price) { body.innerHTML = '<div class="sum-empty">Pilih alat & tanggal.</div>'; return; }
        const p = currentProduct();
        const payType = document.querySelector('input[name="payment_type"]:checked').value;
        const payMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const payNow = payType === 'DP' ? price.dp : price.subtotal;

        body.innerHTML = `
            <div class="sum-row"><span>Alat</span><span>${p.name}</span></div>
            <div class="sum-row"><span>Durasi</span><span>${price.duration}</span></div>
            <div class="sum-row"><span>Tanggal</span><span>${formatDateID(startDate)}${!sameDay(startDate, price.end) ? ' – '+formatDateID(price.end) : ''}</span></div>
            <div class="sum-row"><span>Skema</span><span>${payType}</span></div>
            <div class="sum-row"><span>Metode</span><span>${payMethod}</span></div>
            <div class="sum-row"><span>Total sewa</span><span>Rp ${price.subtotal.toLocaleString('id-ID')}</span></div>
            <div class="sum-row total"><span>Bayar sekarang</span><span>Rp ${payNow.toLocaleString('id-ID')}</span></div>
            ${payType === 'DP' ? `<div class="hint">Sisa Rp ${(price.subtotal-price.dp).toLocaleString('id-ID')} dibayar saat pengambilan.</div>` : ''}`;
    }

    sel.addEventListener('change', () => { renderPreview(); renderSummary(); });
    document.getElementById('calPrev').addEventListener('click', () => { calDate.setMonth(calDate.getMonth()-1); renderCalendar(); });
    document.getElementById('calNext').addEventListener('click', () => { calDate.setMonth(calDate.getMonth()+1); renderCalendar(); });

    document.querySelectorAll('input[name="duration_type"], input[name="payment_type"], input[name="payment_method"]').forEach(r => {
        r.addEventListener('change', () => {
            document.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
            document.querySelectorAll('input[type="radio"]:checked').forEach(rc => rc.closest('.radio-card')?.classList.add('selected'));
            renderSummary();
        });
    });

    // Init
    renderPreview();
    renderCalendar();
});
</script>
@endpush