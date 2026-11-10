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
    @if(session('success'))
        <div class="toast success" style="position:fixed;top:20px;right:20px;z-index:999">
            <i class="bi bi-check-circle-fill" style="color:var(--success)"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="toast error" style="position:fixed;top:20px;right:20px;z-index:999">
            <i class="bi bi-x-circle-fill" style="color:var(--danger)"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="toast error" style="position:fixed;top:20px;right:20px;z-index:999">
            <i class="bi bi-exclamation-circle-fill" style="color:var(--danger)"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    @yield('content')
</main>

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
        },

        formatRupiah(value) {
            return 'Rp ' + Number(value).toLocaleString('id-ID');
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        // Toggle navbar mobile
        const t = document.getElementById('navToggle');
        if (t) {
            t.addEventListener('click', () => {
                document.getElementById('navLinks').classList.toggle('open');
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

        // Tombol "Keranjang" di mana pun: kirim via AJAX, tanpa pindah halaman.
        document.addEventListener('submit', async event => {
            const form = event.target.closest('form.js-cart-add');
            if (!form || event.submitter?.dataset.noAjax !== undefined) return;
            event.preventDefault();
            const button = event.submitter ?? form.querySelector('button[type="submit"]');
            button && (button.disabled = true);
            try {
                const qty = form.querySelector('[name="qty"]')?.value;
                const data = await window.CamRent.request(form.action, 'POST', {
                    qty: qty ? Number(qty) : 1,
                    from: form.dataset.from || null,
                });
                window.CamRent.setCartCount(data.cart_count);
                window.CamRent.toast(data.message, data.at_limit ? 'error' : 'success');
                form.dispatchEvent(new CustomEvent('cart:added', { bubbles: true, detail: data }));
            } catch (error) {
                window.CamRent.toast(error.message, 'error');
            } finally {
                button && (button.disabled = false);
            }
        });
    });
</script>
@stack('scripts')
</body>
</html>