<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CamRent')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body data-page="@yield('page', '')">

@include('partials.navbar')

<main>
    <div id="toast-wrap" aria-live="polite">
        @if(session('success'))
            <div class="toast success">
                <i class="bi bi-check-circle-fill" style="color:var(--success)"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="toast error">
                <i class="bi bi-x-circle-fill" style="color:var(--danger)"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="toast error">
                <i class="bi bi-exclamation-circle-fill" style="color:var(--danger)"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif
    </div>

    @unless(request()->routeIs('home'))
        <div class="breadcrumb-bar">
            <div class="app-container">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}">Home</a>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    <span aria-current="page">@yield('breadcrumb')</span>
                </nav>
            </div>
        </div>
    @endunless

    @yield('content')
</main>

@unless(request()->routeIs('cart.*', 'booking*', 'login*'))
    @php $barCount = app(\App\Services\CartService::class)->count(); @endphp
    <div class="cart-bar{{ $barCount ? ' show' : '' }}" id="cartBar" role="status" aria-live="polite">
        <div class="cart-bar-info">
            <i class="bi bi-cart3"></i>
            <span><b id="cartBarCount">{{ $barCount }}</b> alat di keranjang</span>
        </div>
        <a href="{{ route('cart.index') }}" class="btn btn-primary btn-sm">Lihat Keranjang <i class="bi bi-arrow-right"></i></a>
    </div>
@endunless

@include('partials.footer')

<script>
    // Helper global CamRent (toast, request JSON, badge keranjang)
    window.CamRent = {
        csrf: document.querySelector('meta[name="csrf-token"]')?.content ?? '',

        toast(message, type = 'success') {
            let wrap = document.getElementById('toast-wrap');
            if (!wrap) {
                wrap = document.createElement('div');
                wrap.id = 'toast-wrap';
                document.body.append(wrap);
            }
            const icons = {
                success: ['bi-check-circle-fill', 'var(--success)'],
                error:   ['bi-x-circle-fill', 'var(--danger)'],
                info:    ['bi-info-circle-fill', 'var(--info)'],
            };
            const [icon, color] = icons[type] ?? icons.info;
            const el = document.createElement('div');
            el.className = `toast ${type}`;
            const i = document.createElement('i');
            i.className = `bi ${icon}`;
            i.style.color = color;
            const text = document.createElement('span');
            text.textContent = message;
            el.append(i, text);
            wrap.append(el);
            setTimeout(() => {
                el.style.transition = 'opacity .4s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 400);
            }, 3500);
        },

        confirm(message, title = 'Konfirmasi') {
            return new Promise(resolve => {
                const dialog = document.createElement('dialog');
                dialog.className = 'confirm-dialog glass-strong';
                dialog.setAttribute('aria-label', title);

                const icon = document.createElement('i');
                icon.className = 'bi bi-exclamation-circle-fill confirm-dialog-icon';
                icon.setAttribute('aria-hidden', 'true');

                const heading = document.createElement('h2');
                heading.textContent = title;
                const text = document.createElement('p');
                text.className = 'confirm-dialog-message';
                text.textContent = message;

                const content = document.createElement('div');
                content.className = 'confirm-dialog-content';
                content.append(heading, text);

                const cancelButton = document.createElement('button');
                cancelButton.type = 'button';
                cancelButton.className = 'btn btn-secondary';
                cancelButton.textContent = 'Batal';
                cancelButton.addEventListener('click', () => dialog.close('cancel'));

                const confirmButton = document.createElement('button');
                confirmButton.type = 'button';
                confirmButton.className = 'btn btn-danger';
                confirmButton.textContent = 'Hapus';
                confirmButton.addEventListener('click', () => dialog.close('confirm'));

                const actions = document.createElement('div');
                actions.className = 'confirm-dialog-actions';
                actions.append(cancelButton, confirmButton);
                content.append(actions);
                dialog.append(icon, content);
                dialog.addEventListener('close', () => {
                    resolve(dialog.returnValue === 'confirm');
                    dialog.remove();
                }, { once: true });

                document.body.append(dialog);
                dialog.showModal();
            });
        },

        async request(url, method = 'POST', body = null) {
            const response = await fetch(url, {
                method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: body ? JSON.stringify(body) : null,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                throw new Error(firstError || data.message || 'Terjadi kesalahan. Coba lagi.');
            }
            return data;
        },

        setCartCount(count) {
            const badge = document.getElementById('cartBadge');
            if (!badge) return;
            badge.textContent = count > 99 ? '99+' : count;
            badge.hidden = !count;

            const bar = document.getElementById('cartBar');
            if (bar) {
                document.getElementById('cartBarCount').textContent = count;
                bar.classList.toggle('show', count > 0);
            }
        },

        formatRupiah(value) {
            return 'Rp ' + Number(value).toLocaleString('id-ID');
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        // Toggle navbar mobile
        const t = document.getElementById('navToggle');
        const links = document.getElementById('navLinks');
        if (t) {
            t.addEventListener('click', () => {
                const isOpen = links.classList.toggle('open');
                t.setAttribute('aria-expanded', String(isOpen));
                t.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
            });
            links?.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    links.classList.remove('open');
                    t.setAttribute('aria-expanded', 'false');
                    t.setAttribute('aria-label', 'Buka menu');
                });
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && links?.classList.contains('open')) {
                    links.classList.remove('open');
                    t.setAttribute('aria-expanded', 'false');
                    t.setAttribute('aria-label', 'Buka menu');
                    t.focus();
                }
            });
        }

        // Auto-hide toast
        document.querySelectorAll('.toast').forEach(el => {
            setTimeout(() => {
                el.style.transition = 'opacity .4s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 400);
            }, 4000);
        });

        // Tambah ke keranjang via AJAX, tanpa pindah halaman.
        async function addToCart(source, url, qty, from, button) {
            button && (button.disabled = true);
            try {
                const data = await window.CamRent.request(url, 'POST', { qty, from: from || null });
                window.CamRent.setCartCount(data.cart_count);
                window.CamRent.toast(data.message, data.at_limit ? 'error' : 'success');
                source.dispatchEvent(new CustomEvent('cart:added', { bubbles: true, detail: data }));
            } catch (error) {
                window.CamRent.toast(error.message, 'error');
            } finally {
                button && (button.disabled = false);
            }
        }

        // Form tombol "Keranjang" (kartu produk, specsheet)
        document.addEventListener('submit', event => {
            const form = event.target.closest('form.js-cart-add');
            if (!form || event.submitter?.dataset.noAjax !== undefined) return;
            event.preventDefault();
            const qty = form.querySelector('[name="qty"]')?.value;
            addToCart(form, form.action, qty ? Number(qty) : 1, form.dataset.from,
                event.submitter ?? form.querySelector('button[type="submit"]'));
        });

        // Salin nomor rekening / VA
        document.addEventListener('click', async event => {
            const button = event.target.closest('.copy-btn');
            if (!button) return;
            try {
                await navigator.clipboard.writeText(button.dataset.copy);
                window.CamRent.toast('Nomor berhasil disalin.');
            } catch (error) {
                window.CamRent.toast('Gagal menyalin. Salin manual ya.', 'error');
            }
        });

        // Tombol "Tambah" rekomendasi di dalam form checkout
        document.addEventListener('click', event => {
            const button = event.target.closest('.js-quick-add');
            if (button) addToCart(button, button.dataset.url, 1, button.dataset.from, button);
        });
    });
</script>
@stack('scripts')
</body>
</html>