<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Jelajah Event — temukan event kuliner, musik, olahraga, dan budaya di Daerah Istimewa Yogyakarta.">
    <title>@yield('title', 'Jelajah Event') · WebGIS Yogyakarta</title>
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar" aria-label="Navigasi utama">
        <div class="container nav-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Jelajah Event - Beranda">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 40 40"><path d="M20 4c-6.1 0-11 4.8-11 10.8C9 23 20 35 20 35s11-12 11-20.2C31 8.8 26.1 4 20 4Z"/><circle cx="20" cy="15" r="4.2"/></svg>
                </span>
                <span><strong>Jelajah</strong> Event</span>
            </a>

            <button class="nav-toggle" type="button" aria-label="Buka menu" aria-expanded="false" data-nav-toggle>
                <span></span><span></span><span></span>
            </button>

            <div class="nav-menu" data-nav-menu>
                <div class="nav-links">
                    <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a>
                    <a class="{{ request()->routeIs('events.map') ? 'active' : '' }}" href="{{ route('events.map') }}">Peta Event</a>
                    <a class="{{ request()->routeIs('events.index') ? 'active' : '' }}" href="{{ route('events.index') }}">Daftar Event</a>
                </div>

                <div class="nav-auth">
                    @auth
                        <span class="user-chip">
                            <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->isAdmin() ? 'Administrator' : 'Kontributor' }}</small></span>
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-ghost btn-sm" type="submit">Keluar</button>
                        </form>
                    @else
                        <a class="btn btn-primary btn-sm" href="{{ route('login') }}">Masuk</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    @if (session('success'))
        <div class="container flash-wrap">
            <div class="flash flash-success" role="status">
                <span>✓</span>{{ session('success') }}
                <button type="button" data-dismiss aria-label="Tutup">×</button>
            </div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    @unless(request()->routeIs('events.map'))
        <footer class="site-footer">
            <div class="container footer-inner">
                <a class="brand brand-footer" href="{{ route('home') }}">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 40 40"><path d="M20 4c-6.1 0-11 4.8-11 10.8C9 23 20 35 20 35s11-12 11-20.2C31 8.8 26.1 4 20 4Z"/><circle cx="20" cy="15" r="4.2"/></svg>
                    </span>
                    <span><strong>Jelajah</strong> Event</span>
                </a>
                <p>Menemukan cerita, rasa, dan perayaan di setiap sudut Yogyakarta.</p>
                <p class="footer-copy">© {{ date('Y') }} Jelajah Event</p>
            </div>
        </footer>
    @endunless

    @stack('scripts')
</body>
</html>
