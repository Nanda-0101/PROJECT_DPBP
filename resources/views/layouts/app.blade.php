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
    // Toggle navbar mobile
    document.addEventListener('DOMContentLoaded', () => {
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
    });
</script>
</body>
</html>