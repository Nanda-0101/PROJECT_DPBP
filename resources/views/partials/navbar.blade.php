<nav class="nav-glass">
  <div class="nav-inner">
    <a href="{{ route('home') }}" class="brand">
      <span class="brand-mark"><i class="bi bi-camera-reels-fill"></i></span>
      <span>CamRent</span>
    </a>
    <div class="nav-links" id="navLinks">
      <a href="{{ route('home') }}"        class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
      <a href="{{ route('marketplace') }}" class="{{ request()->routeIs('marketplace*') ? 'active' : '' }}">Marketplace</a>
      <a href="{{ route('booking') }}"     class="{{ request()->routeIs('booking*') ? 'active' : '' }}">Booking</a>
      <a href="{{ route('mitra.index') }}" class="{{ request()->routeIs('mitra*') ? 'active' : '' }}">Jadi Mitra</a>
      <a href="{{ route('cs') }}"          class="{{ request()->routeIs('cs*') ? 'active' : '' }}">Bantuan (CS)</a>
    </div>
    <div class="nav-actions">
      @auth
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
          @csrf
          <button class="btn btn-ghost btn-sm" type="submit">
            <i class="bi bi-box-arrow-right"></i> Keluar
          </button>
        </form>
      @else
        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
          <i class="bi bi-person-circle"></i> Masuk / Daftar
        </a>
      @endauth
    </div>
    <button class="nav-toggle" id="navToggle" aria-label="Buka menu">
      <i class="bi bi-list"></i>
    </button>
  </div>
</nav>