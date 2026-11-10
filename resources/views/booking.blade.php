@extends('layouts.app')

@section('page', 'booking')
@section('title', 'Checkout Sewa · CamRent')

@section('content')
<header class="section" style="padding-bottom:10px;">
  <div class="app-container">
    <span class="eyebrow badge badge-rent"><i class="bi bi-calendar-check"></i> Checkout</span>
    <h1 style="font-size:2.1rem;margin:12px 0 8px;">Checkout peralatan sewa</h1>
    <p style="max-width:600px;">Periksa alat yang disewa, tentukan tanggal lewat kalender, dan selesaikan pembayaran dalam beberapa langkah.</p>
  </div>
</header>

<form method="POST" action="{{ route('booking.store') }}" id="bookingForm">
  @csrf
  <section class="app-container booking-layout">
    <div class="booking-main">

      {{-- Step 1 --}}
      <div class="glass card bk-step">
        <div class="bk-step-title" style="justify-content:space-between;">
          <span style="display:flex;align-items:center;gap:10px;"><span class="bk-num">1</span> Alat yang disewa</span>
          <a href="{{ route('cart.index') }}" class="link-more" style="font-size:.85rem;"><i class="bi bi-pencil-square"></i> Ubah di keranjang</a>
        </div>
        <div id="checkoutItems"></div>
        @error('selected')<div class="hint" style="color:var(--danger);">{{ $message }}</div>@enderror
      </div>

      {{-- Rekomendasi --}}
      @if($recommendations->isNotEmpty())
        <div class="glass card bk-step" id="recBlock">
          <div class="bk-step-title" style="margin-bottom:6px;"><i class="bi bi-stars gradient-text"></i> Rekomendasi untuk kamu</div>
          <div class="hint" style="margin:0 0 12px;">Berdasarkan kategori yang paling sering kamu sewa, lihat, dan isi keranjangmu.</div>
          <div class="rec-scroll">
            @foreach($recommendations as $p)
              <div class="rec-item" data-rec-id="{{ $p->id }}">
                @include('partials.rec-card', ['p' => $p, 'ajax' => true, 'from' => 'checkout'])
              </div>
            @endforeach
          </div>
        </div>
      @endif

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
        <div class="field" style="margin-bottom:0;">
          <label>Metode pembayaran</label>
          @php $selectedMethod = old('payment_method', 'Cash'); @endphp
          @foreach($paymentGroups as $group)
            <div class="pay-group">
              <div class="pay-group-title">{{ $group['label'] }} <span class="hint">· {{ $group['hint'] }}</span></div>
              <div class="pay-grid">
                @foreach($group['methods'] as $method)
                  <label class="radio-card {{ $selectedMethod === $method['key'] ? 'selected' : '' }}">
                    <input type="radio" name="payment_method" value="{{ $method['key'] }}" @checked($selectedMethod === $method['key'])>
                    <i class="bi {{ $method['icon'] }}"></i> {{ $method['label'] }}
                  </label>
                @endforeach
              </div>
            </div>
          @endforeach
          <div class="payment-info-box" id="paymentInfo"></div>
        </div>
      </div>
    </div>

    {{-- Ringkasan --}}
    <aside class="booking-side">
      <div class="glass card summary-card">
        <h4 style="margin-bottom:14px;"><i class="bi bi-receipt gradient-text"></i> Ringkasan Sewa</h4>
        <div id="summaryBody">
          <div class="sum-empty">Pilih tanggal sewa untuk melihat ringkasan.</div>
        </div>
        <button type="submit" class="btn btn-primary btn-block" style="margin-top:16px;" id="submitBooking">
          <i class="bi bi-check2-circle"></i> Konfirmasi Sewa
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
    const calDate   = new Date();
    let startDate = null, endDate = null;

    const PAYMENT_METHODS = @json($paymentMethods);
    const INITIAL_ITEMS   = @json($initialItems);
    const OLD = @json(['start' => old('start_date'), 'end' => old('end_date')]);

    const $start = document.getElementById('startDateInput');
    const $end   = document.getElementById('endDateInput');
    const $items = document.getElementById('checkoutItems');
    const rupiah = value => 'Rp ' + Number(value).toLocaleString('id-ID');

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[char]));
    }

    function dateKey(d) {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    function parseDateKey(value) {
        const [year, month, day] = value.split('-').map(Number);
        return new Date(year, month - 1, day);
    }
    function sameDay(a,b){ return a && b && dateKey(a) === dateKey(b); }
    function formatDateID(d){ return d.toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}); }

    // ===== Daftar alat (sumber data: DOM #checkoutItems) =====
    function addItemRow(item) {
        const row = document.createElement('div');
        row.className = 'co-item';
        row.dataset.checkoutItem = '';
        row.dataset.id = item.id;
        row.dataset.priceDay = item.price_day;
        row.dataset.priceHalf = item.price_half;
        row.dataset.qty = item.qty;
        row.dataset.name = item.name;

        const thumb = document.createElement(item.image ? 'img' : 'div');
        thumb.className = 'co-thumb';
        if (item.image) {
            thumb.src = item.image;
            thumb.alt = item.name;
        } else {
            thumb.classList.add('co-thumb-icon');
            thumb.innerHTML = `<i class="bi ${escapeHtml(item.icon || 'bi-box-seam')}"></i>`;
        }

        const info = document.createElement('div');
        info.className = 'co-info';
        info.innerHTML = `
            <div class="co-name">${escapeHtml(item.name)}</div>
            <div class="hint">${escapeHtml(item.category)} · ${rupiah(item.price_day)}/hari</div>`;

        const qty = document.createElement('span');
        qty.className = 'co-qty';
        qty.textContent = `×${item.qty}`;

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected[]';
        input.value = item.id;

        row.append(thumb, info, qty, input);
        $items.append(row);
    }

    const itemRows = () => [...$items.querySelectorAll('[data-checkout-item]')].map(row => ({
        id: row.dataset.id,
        name: row.dataset.name,
        qty: +row.dataset.qty,
        priceDay: +row.dataset.priceDay,
        priceHalf: +row.dataset.priceHalf,
    }));

    // ===== Kalender =====
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
            cell.addEventListener('click', () => pickDate(parseDateKey(cell.dataset.date)));
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

    // ===== Harga: sama dengan perhitungan server (BookingController::priceFor) =====
    function computePrice() {
        const items = itemRows();
        if (!startDate || !items.length) return null;
        const end = endDate || startDate;
        const durType = document.querySelector('input[name="duration_type"]:checked').value;
        let duration, lines;
        if (sameDay(startDate, end)) {
            duration = durType === 'half' ? '12 Jam' : '1 Hari';
            lines = items.map(i => ({
                ...i,
                subtotal: (durType === 'half' ? (i.priceHalf || Math.round(i.priceDay * 0.6)) : i.priceDay) * i.qty,
            }));
        } else {
            const days = Math.round((end - startDate) / 86400000) + 1;
            duration = days + ' Hari';
            lines = items.map(i => ({ ...i, subtotal: i.priceDay * days * i.qty }));
        }
        const subtotal = lines.reduce((sum, line) => sum + line.subtotal, 0);
        return { duration, lines, subtotal, dp: Math.round(subtotal * 0.3), end };
    }

    function renderSummary() {
        const body = document.getElementById('summaryBody');
        const submit = document.getElementById('submitBooking');
        submit.disabled = itemRows().length === 0;

        const price = computePrice();
        if (!price) { body.innerHTML = '<div class="sum-empty">Pilih tanggal sewa untuk melihat ringkasan.</div>'; return; }
        const payType = document.querySelector('input[name="payment_type"]:checked').value;
        const payMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const payNow = payType === 'DP' ? price.dp : price.subtotal;
        const methodLabel = PAYMENT_METHODS[payMethod]?.label ?? payMethod;

        body.innerHTML = `
            <div class="sum-items">
                ${price.lines.map(l => `
                    <div class="sum-row"><span>${escapeHtml(l.name)} <span class="hint">×${l.qty}</span></span><span>${rupiah(l.subtotal)}</span></div>`).join('')}
            </div>
            <div class="divider" style="margin:8px 0;"></div>
            <div class="sum-row"><span>Durasi</span><span>${price.duration}</span></div>
            <div class="sum-row"><span>Tanggal</span><span>${formatDateID(startDate)}${!sameDay(startDate, price.end) ? ' – '+formatDateID(price.end) : ''}</span></div>
            <div class="sum-row"><span>Skema</span><span>${payType}</span></div>
            <div class="sum-row"><span>Metode</span><span>${escapeHtml(methodLabel)}</span></div>
            <div class="sum-row"><span>Total sewa</span><span>${rupiah(price.subtotal)}</span></div>
            <div class="sum-row total"><span>Bayar sekarang</span><span>${rupiah(payNow)}</span></div>
            ${payType === 'DP' ? `<div class="hint">Sisa ${rupiah(price.subtotal-price.dp)} dibayar saat pengambilan.</div>` : ''}`;
    }

    // ===== Info metode pembayaran =====
    function renderPaymentInfo() {
        const key = document.querySelector('input[name="payment_method"]:checked')?.value;
        const method = PAYMENT_METHODS[key];
        const box = document.getElementById('paymentInfo');
        if (!method) { box.hidden = true; return; }
        box.hidden = false;

        if (method.type === 'cash') {
            box.innerHTML = '<b><i class="bi bi-cash-coin"></i> Bayar tunai</b><div>Bayar langsung ke mitra saat pengambilan alat.</div>';
        } else if (method.type === 'qris') {
            box.innerHTML = `
                <div class="qris-box">
                    <div class="qris-visual" aria-hidden="true"></div>
                    <div><b><i class="bi bi-qr-code"></i> Scan QRIS</b>
                    <div>Kode QRIS ditampilkan setelah booking dibuat. Bisa dibayar lewat aplikasi bank / e-wallet apa pun.</div></div>
                </div>`;
        } else {
            const title = method.type === 'virtual_account' ? `Virtual Account ${escapeHtml(method.label)}` : `Transfer ke ${escapeHtml(method.label)}`;
            box.innerHTML = `
                <b><i class="bi ${escapeHtml(method.icon)}"></i> ${title}</b>
                <div class="pay-number">${escapeHtml(method.account_number)}</div>
                <div>a.n. ${escapeHtml(method.account_name)}</div>
                <div class="hint">Nominal pembayaran sesuai "Bayar sekarang" di ringkasan.</div>`;
        }
    }

    document.getElementById('calPrev').addEventListener('click', () => { calDate.setMonth(calDate.getMonth()-1); renderCalendar(); });
    document.getElementById('calNext').addEventListener('click', () => { calDate.setMonth(calDate.getMonth()+1); renderCalendar(); });

    document.querySelectorAll('input[name="duration_type"], input[name="payment_type"], input[name="payment_method"]').forEach(r => {
        r.addEventListener('change', () => {
            document.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
            document.querySelectorAll('input[type="radio"]:checked').forEach(rc => rc.closest('.radio-card')?.classList.add('selected'));
            renderPaymentInfo();
            renderSummary();
        });
    });

    // Tambah alat dari rekomendasi tanpa meninggalkan halaman (data form tidak hilang).
    document.addEventListener('cart:added', event => {
        const item = event.detail?.item;
        if (!item || $items.querySelector(`[data-checkout-item][data-id="${item.id}"]`)) return;
        addItemRow(item);
        document.querySelector(`[data-rec-id="${item.id}"]`)?.remove();
        if (!document.querySelector('.rec-item')) document.getElementById('recBlock')?.remove();
        renderSummary();
    });

    // Init
    INITIAL_ITEMS.forEach(addItemRow);
    if (OLD.start) {
        startDate = parseDateKey(OLD.start);
        endDate = OLD.end && OLD.end !== OLD.start ? parseDateKey(OLD.end) : null;
        calDate.setFullYear(startDate.getFullYear(), startDate.getMonth(), 1);
        $start.value = OLD.start;
        $end.value = OLD.end || OLD.start;
        document.getElementById('durationChoice').style.display = (!endDate) ? 'block' : 'none';
        renderPicked();
    }
    renderCalendar();
    renderPaymentInfo();
    renderSummary();
});
</script>
@endpush
