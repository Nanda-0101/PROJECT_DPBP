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
      <form id="chatForm" class="chat-input-row">
        <input class="input" id="chatInput" name="message" maxlength="2000" placeholder="Ketik pesan..." autocomplete="off">
        <button type="submit" id="chatSend" class="btn btn-primary btn-sm"><i class="bi bi-send"></i></button>
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
          <i class="bi bi-inbox" style="font-size:1.5rem;"></i>
          <p>Belum ada tiket pengaduan.</p>
        </div>
      @endforelse
    </div>
  </div>
</section>

{{-- FAQ --}}
<section class="app-container faq-section" id="faq">
  <div class="section-head"><h2 class="faq-title"><i class="bi bi-question-circle-fill gradient-text"></i> Pertanyaan Umum</h2></div>
  <div class="faq-accordion">
    @foreach($faqs as $i => $f)
      <div class="faq-item" id="faq-{{ $i }}">
        <button type="button" class="faq-q" aria-expanded="false" aria-controls="faq-a-{{ $i }}"
                onclick="const it=this.closest('.faq-item');this.setAttribute('aria-expanded',it.classList.toggle('open'))">
          <span>{{ $f['q'] }}</span>
          <i class="bi bi-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-a" id="faq-a-{{ $i }}" role="region">
          <div class="faq-a-inner"><p>{{ $f['a'] }}</p></div>
        </div>
      </div>
    @endforeach
  </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
  const win = document.getElementById('chatWindow');
  const form = document.getElementById('chatForm');
  const input = document.getElementById('chatInput');
  const sendButton = document.getElementById('chatSend');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  function scrollChat() {
    win.scrollTop = win.scrollHeight;
  }

  function appendMessage(sender, text) {
    const bubble = document.createElement('div');
    bubble.className = sender === 'user' ? 'chat-msg me' : 'chat-msg bot';
    bubble.textContent = text;
    win.appendChild(bubble);
    scrollChat();
    return bubble;
  }

  async function loadHistory() {
    try {
      const response = await fetch(@json(route('cs.chat.history')), {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
      });
      if (!response.ok) return;
      const data = await response.json();
      if (Array.isArray(data.messages) && data.messages.length) {
        win.replaceChildren();
        data.messages.forEach(item => appendMessage(item.sender, item.message));
      }
    } catch (_) {
      // Riwayat tetap dapat dibuka ulang; pesan awal tetap ditampilkan jika gagal.
    }
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const message = input.value.trim();
    if (!message) return;
    if (message.length > 2000) {
      appendMessage('assistant', 'Pesan terlalu panjang. Batasi pesan hingga 2.000 karakter.');
      return;
    }

    appendMessage('user', message);
    input.value = '';
    input.disabled = true;
    sendButton.disabled = true;
    const typing = document.createElement('div');
    typing.className = 'chat-msg bot';
    typing.textContent = 'CAMRENT AI sedang memproses...';
    win.appendChild(typing);
    scrollChat();

    try {
      const response = await fetch(@json(route('cs.chat.send')), {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
        },
        credentials: 'same-origin',
        body: JSON.stringify({ message }),
      });

      const data = await response.json().catch(() => null);
      typing.remove();
      if (!response.ok) {
        appendMessage('assistant', data?.message
          || 'Maaf, layanan AI CAMRENT sedang tidak dapat dihubungi. Silakan coba beberapa saat lagi atau gunakan Form Pengaduan.');
        return;
      }
      appendMessage('assistant', data.message);
    } catch (_) {
      typing.remove();
      appendMessage('assistant', 'Maaf, koneksi ke layanan CAMRENT terputus. Silakan coba lagi atau gunakan Form Pengaduan.');
    } finally {
      input.disabled = false;
      sendButton.disabled = false;
      input.focus();
    }
  });

  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      form.requestSubmit();
    }
  });

  loadHistory();
})();
</script>
@endpush