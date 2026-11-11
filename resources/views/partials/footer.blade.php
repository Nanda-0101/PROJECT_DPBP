<footer class="footer">
  <div class="footer-main">
    <div class="app-container footer-content">
      <div class="footer-brand-block">
        <a href="{{ route('home') }}" class="brand footer-brand">
          <span class="brand-mark"><i class="bi bi-camera-reels-fill"></i></span>
          <span>CamRent</span>
        </a>
        <p class="footer-description">Platform sewa peralatan kreatif — kamera, lensa, lighting, audio, hingga gimbal, dengan keranjang sewa dan pembayaran online yang aman.</p>
      </div>

      <div class="footer-social" aria-label="Ikuti kami">
        <span class="footer-social-label">Ikuti kami</span>
        <div class="footer-social-links">
          <span aria-label="Instagram"><i class="bi bi-instagram"></i></span>
          <span aria-label="TikTok"><i class="bi bi-tiktok"></i></span>
          <span aria-label="YouTube"><i class="bi bi-youtube"></i></span>
        </div>
      </div>

      <nav class="footer-nav" aria-label="Navigasi footer">
        <a href="{{ route('marketplace') }}">Catalog</a>
        <a href="{{ route('cart.index') }}">Keranjang Sewa</a>
        <a href="{{ route('mitra.index') }}">Daftar Jadi Mitra</a>
        <a href="{{ route('cs') }}">Pusat Bantuan (CS)</a>
        <a href="{{ route('cs') }}#faq">FAQ</a>
        <a href="{{ route('dashboard') }}">Status Pesanan</a>
      </nav>

    </div>
  </div>
  <div class="footer-bottom">
    <div class="app-container footer-bottom-inner">
      <span>© 2026 CamRent. Seluruh hak cipta dilindungi.</span>
      <span>Dibuat untuk kreator Indonesia 🇮🇩</span>
    </div>
  </div>
</footer>