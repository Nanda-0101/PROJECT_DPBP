{{-- Instruksi pembayaran setelah checkout. Param: $summary (flash `payment_summary`) --}}
@php
  $method = \App\Support\PaymentMethods::find($summary['method']);
  $type = $method['type'] ?? 'cash';
@endphp
<div class="glass card pay-summary">
  <div class="pay-summary-head">
    <span class="bk-num"><i class="bi bi-wallet2"></i></span>
    <div>
      <h3 style="font-size:1.1rem;">Selesaikan pembayaranmu</h3>
      <div class="hint" style="margin:0;">Pesanan {{ implode(', ', $summary['codes']) }}</div>
    </div>
  </div>

  <div class="sum-row"><span>Metode</span><span>{{ $method['label'] ?? $summary['method'] }}</span></div>
  <div class="sum-row"><span>Total sewa</span><span>Rp {{ number_format($summary['total'], 0, ',', '.') }}</span></div>
  <div class="sum-row total"><span>Bayar sekarang ({{ $summary['type'] }})</span><span>Rp {{ number_format($summary['pay_now'], 0, ',', '.') }}</span></div>

  <div class="payment-info-box">
    @if($type === 'cash')
      <b><i class="bi bi-cash-coin"></i> Bayar tunai</b>
      <div>Bayar langsung ke mitra saat pengambilan alat.</div>
    @elseif($type === 'qris')
      <div class="qris-box">
        <div class="qris-visual" aria-hidden="true"><i class="bi bi-qr-code"></i></div>
        <div>
          <b><i class="bi bi-qr-code"></i> Scan QRIS CamRent</b>
          <div>Bayar sebesar nominal di atas lewat aplikasi bank atau e-wallet apa pun.</div>
        </div>
      </div>
    @else
      <b><i class="bi {{ $method['icon'] }}"></i>
        {{ $type === 'virtual_account' ? 'Virtual Account '.$method['label'] : 'Transfer ke '.$method['label'] }}</b>
      <div class="pay-number">{{ $method['account_number'] }}</div>
      <div>a.n. {{ $method['account_name'] }}</div>
      <div class="hint">Sertakan kode pesanan pada berita transfer bila tersedia.</div>
    @endif
  </div>
</div>
