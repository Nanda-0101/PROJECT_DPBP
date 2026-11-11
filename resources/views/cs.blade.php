@extends('layouts.app')

@section('page', 'cs')
@section('title', 'Pusat Bantuan (CS) · CamRent')
@section('breadcrumb', 'Pusat Bantuan')

@section('content')
<header class="page-head">
  <div class="app-container">
    <h1>Ada kendala? Kami bantu selesaikan.</h1>
    <p>Salah booking, salah tanggal, kendala pembayaran, atau alat bermasalah — laporkan di sini.</p>
  </div>
</header>

<section class="app-container cs-layout">
  {{-- Form --}}
  <div class="glass card cs-form-card">
    <h3 style="margin-bottom:4px;"><i class="bi bi-flag gradient-text"></i> Ajukan Pengaduan</h3>
    <p style="margin-bottom:18px;">Ceritakan kendalamu, sertakan nomor pesanan jika ada.</p>
    <form method="POST" action="{{ route('cs.store') }}">
      @csrf
      <div class="field">
        <label>Kategori pengaduan</label>
        <select name="category" class="input" required>
          <option>Salah booking / tanggal</option>
          <option>Kendala pembayaran</option>
          <option>Alat rusak / tidak sesuai</option>
          <option>Keterlambatan pengiriman</option>
          <option>Lainnya</option>
        </select>
      </div>
      <div class="field">
        <label>Pesanan terkait (opsional)</label>
        <select name="booking_id" class="input">
          <option value="">-- Tidak terkait pesanan tertentu --</option>
          @foreach($bookings as $b)
            <option value="{{ $b->id }}" @selected(request('booking') == $b->id)>
              {{ $b->code }} — {{ $b->items->first()->product_name_snap ?? '-' }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>Nama pelapor</label>
        <input required name="name" class="input" value="{{ old('name', $user->name ?? '') }}" placeholder="Nama lengkap">
      </div>
      <div class="field">
        <label>No. HP / Email</label>
        <input required name="contact" class="input" value="{{ old('contact', $user->phone ?? $user->email ?? '') }}" placeholder="08xxxxxxxxxx atau email">
      </div>
      <div class="field">
        <label>Deskripsi kendala</label>
        <textarea required name="description" class="input" rows="4" placeholder="Jelaskan detail masalah..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary btn-block">
        <i class="bi bi-send"></i> Kirim Pengaduan
      </button>
    </form>
  </div>

  {{-- Side --}}
  <div class="cs-side">
    <div class="glass card cs-chat-card">
      <h4 style="margin-bottom:10px;"><i class="bi bi-chat-dots-fill gradient-text"></i> Live Chat CS</h4>
      <div class="chat-window" id="chatWindow">
        <div class="chat-msg bot">Halo! Ada yang bisa saya bantu terkait booking atau pembayaran?</div>
      </div>
      <form id="chatForm" class="chat-input-row" onsubmit="return false;">
        <input class="input" id="chatInput" placeholder="Ketik pesan...">
        <button type="button" class="btn btn-primary btn-sm" onclick="sendChat()"><i class="bi bi-send"></i></button>
      </form>
    </div>

    <div class="glass card" style="margin-top:20px;">
      <h4 style="margin-bottom:14px;"><i class="bi bi-list-check gradient-text"></i> Riwayat Tiket Saya</h4>
      @forelse($tickets as $t)
        <div class="ticket-item">
          <div class="ti-top">
            <span>{{ $t->code }}</span>
            <span class="status-pill status-{{ strtolower($t->status) }}">{{ $t->status }}</span>
          </div>
          <div>{{ $t->category }}{{ $t->booking_id ? ' · '.$t->booking->code : '' }}</div>
          <div class="hint">{{ Str::limit($t->description, 70) }}</div>
        </div>
      @empty
        <div class="empty-state" style="padding:24px 10px;">
          <i class="bi bi-inbox" style="font-size:1.6rem;"></i>
          <p>Belum ada tiket pengaduan.</p>
        </div>
      @endforelse
    </div>
  </div>
</section>

{{-- FAQ --}}
<section class="app-container" id="faq" style="margin-top:20px;">
  <div class="section-head"><h2 style="font-size:1.4rem;"><i class="bi bi-question-circle-fill gradient-text"></i> Pertanyaan Umum</h2></div>
  <div class="faq-accordion">
    @foreach($faqs as $i => $f)
      <div class="faq-item" id="faq-{{ $i }}">
        <div class="faq-q" onclick="document.getElementById('faq-{{ $i }}').classList.toggle('open')">
          <span>{{ $f['q'] }}</span>
          <i class="bi bi-chevron-down"></i>
        </div>
        <div class="faq-a"><p>{{ $f['a'] }}</p></div>
      </div>
    @endforeach
  </div>
</section>
@endsection

@push('scripts')
<script>
function sendChat() {
    const input = document.getElementById('chatInput');
    const val = input.value.trim();
    if (!val) return;
    const win = document.getElementById('chatWindow');

    const me = document.createElement('div');
    me.className = 'chat-msg me';
    me.textContent = val;
    win.appendChild(me);
    input.value = '';

    setTimeout(() => {
        const bot = document.createElement('div');
        bot.className = 'chat-msg bot';
        const m = val.toLowerCase();
        if (m.includes('batal')) bot.textContent = 'Untuk pembatalan, silakan isi form pengaduan dengan kategori "Salah booking".';
        else if (m.includes('bayar') || m.includes('qris') || m.includes('transfer')) bot.textContent = 'Kami dukung Cash, transfer bank (BCA, BRI, BNI), virtual account/e-wallet (SeaBank, DANA, ShopeePay, GoPay), dan QRIS. Jika gagal, sertakan nomor pesanan di form.';
        else if (m.includes('rusak')) bot.textContent = 'Mohon maaf. Laporkan via form pengaduan kategori "Alat rusak".';
        else if (m.includes('mitra')) bot.textContent = 'Kunjungi halaman "Jadi Mitra" di menu atas.';
        else bot.textContent = 'Terima kasih, pesanmu sudah dicatat. Untuk respons cepat, isi form pengaduan.';
        win.appendChild(bot);
        win.scrollTop = win.scrollHeight;
    }, 400);
}
</script>
@endpush