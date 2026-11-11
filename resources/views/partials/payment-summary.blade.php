{{-- Instruksi pembayaran setelah checkout. Param: $summary (flash `payment_summary`) --}}
@php
  $method = \App\Support\PaymentMethods::find($summary['method']);
  $type = $method['type'] ?? 'cash';
  $number = $method['account_number'] ?? null;
@endphp
<div class="glass card pay-summary">
  <div class="pay-summary-head">
    <span class="bk-num"><i class="bi bi-wallet2"></i></span>
    <div>
      <h3 style="font-size:1.1rem;">Selesaikan pembayaranmu</h3>
      <div class="hint" style="margin:0;">Pesanan {{ implode(', ', $summary['codes']) }}</div>
    </div>
    <span class="status-pill waiting"><i class="bi bi-hourglass-split"></i> Menunggu pembayaran</span>
  </div>

  <div class="pay-summary-grid">
    <div>
      <div class="pay-amount-label">Bayar sekarang ({{ $summary['type'] }})</div>
      <div class="pay-amount">Rp {{ number_format($summary['pay_now'], 0, ',', '.') }}</div>
      <div class="sum-row"><span>Metode</span><span>{{ $method['label'] ?? $summary['method'] }}</span></div>
      <div class="sum-row"><span>Total sewa</span><span>Rp {{ number_format($summary['total'], 0, ',', '.') }}</span></div>
      @if($summary['pay_now'] < $summary['total'])
        <div class="sum-row"><span>Sisa dibayar saat pengambilan</span><span>Rp {{ number_format($summary['total'] - $summary['pay_now'], 0, ',', '.') }}</span></div>
      @endif
    </div>

    <div class="payment-info-box">
      @if($type === 'cash')
        <b><i class="bi bi-cash-coin"></i> Bayar tunai</b>
        <div>Bayar langsung ke mitra saat pengambilan alat.</div>
      @elseif($type === 'qris')
        <div class="qris-box">
          <div class="qris-visual" aria-hidden="true"></div>
          <div>
            <b><i class="bi bi-qr-code"></i> Scan QRIS CamRent</b>
            <div>Bayar sebesar nominal di samping lewat aplikasi bank atau e-wallet apa pun.</div>
          </div>
        </div>
      @else
        <b><i class="bi {{ $method['icon'] }}"></i>
          {{ $type === 'virtual_account' ? 'Virtual Account '.$method['label'] : 'Transfer ke '.$method['label'] }}</b>
        <div class="pay-number-row">
          <div class="pay-number">{{ $number }}</div>
          <button type="button" class="copy-btn" data-copy="{{ str_replace(' ', '', $number) }}"><i class="bi bi-copy"></i> Salin</button>
        </div>
        <div>a.n. {{ $method['account_name'] }}</div>
        <div class="hint">Sertakan kode pesanan pada berita transfer bila tersedia.</div>
      @endif
    </div>
  </div>
</div>
