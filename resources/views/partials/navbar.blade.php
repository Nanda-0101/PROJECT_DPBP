<nav class="nav-glass">
  <div class="nav-inner">
    <a href="{{ route('home') }}" class="brand">
      <span class="brand-mark"><i class="bi bi-camera-reels-fill"></i></span>
      <span>CamRent</span>
    </a>
    <div class="nav-links" id="navLinks">
      <a href="{{ route('home') }}"        class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
      <a href="{{ route('marketplace') }}" class="{{ request()->routeIs('marketplace*') ? 'active' : '' }}">Catalog</a>
      <a href="{{ route('mitra.index') }}" class="{{ request()->routeIs('mitra*') ? 'active' : '' }}">Jadi Mitra</a>
      <a href="{{ route('cs') }}"          class="{{ request()->routeIs('cs*') ? 'active' : '' }}">Bantuan (CS)</a>
      @auth
        <a href="{{ route('dashboard') }}" class="nav-mobile-only {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('account.settings') }}" class="nav-mobile-only {{ request()->routeIs('account.settings') ? 'active' : '' }}">
          <i class="bi bi-person-gear"></i> Pengaturan akun
        </a>
        <form method="POST" action="{{ route('logout') }}" class="nav-mobile-only">
          @csrf
          <button class="nav-mobile-logout" type="submit"><i class="bi bi-box-arrow-right"></i> Keluar</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="nav-mobile-only"><i class="bi bi-person-circle"></i> Masuk / Daftar</a>
      @endauth
    </div>
    <div class="nav-actions">
      @php $navCartCount = app(\App\Services\CartService::class)->count(); @endphp
      <a href="{{ route('cart.index') }}" class="nav-cart {{ request()->routeIs('cart.*', 'booking*') ? 'active' : '' }}"
         aria-label="Keranjang sewa" title="Keranjang sewa">
        <i class="bi bi-cart3"></i>
        <span class="nav-cart-badge" id="cartBadge" @if(!$navCartCount) hidden @endif>{{ $navCartCount > 99 ? '99+' : $navCartCount }}</span>
      </a>
      @auth
        @php
          $navAvatar = \App\Helpers\ImageHelper::url(auth()->user()->avatar);
          $navInitials = collect(explode(' ', trim(auth()->user()->name)))
              ->filter()
              ->take(2)
              ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
              ->implode('');
        @endphp
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
          <i class="bi bi-speedometer2"></i><span class="nav-btn-text">Dashboard</span>
        </a>
        <a href="{{ route('account.settings') }}" class="nav-profile" aria-label="Pengaturan akun" title="Pengaturan akun">
          @if($navAvatar)
            <img src="{{ $navAvatar }}" alt="" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
            <span hidden>{{ $navInitials }}</span>
          @else
            <span>{{ $navInitials }}</span>
          @endif
        </a>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
          @csrf
          <button class="btn btn-ghost btn-sm" type="submit">
            <i class="bi bi-box-arrow-right"></i> Keluar
          </button>
        </form>
      @else
        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
          <i class="bi bi-person-circle"></i><span class="nav-btn-text">Masuk / Daftar</span>
        </a>
      @endauth
    </div>
    <button class="nav-toggle" id="navToggle" aria-label="Buka menu" aria-controls="navLinks" aria-expanded="false">
      <i class="bi bi-list"></i>
    </button>
  </div>
</nav>