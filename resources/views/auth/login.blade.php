@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <section class="login-page">
        <div class="login-shell">
            <div class="login-visual">
                <div class="login-visual-content">
                    <span class="eyebrow light"><span>✦</span> Ruang kontributor</span>
                    <h1>Satu titik baru,<br><em>satu cerita baru.</em></h1>
                    <p>Masuk untuk menambahkan event pilihan ke peta Jelajah Event.</p>
                </div>
                <div class="login-contours" aria-hidden="true"></div>
                <div class="login-pin" aria-hidden="true"><span>⌖</span></div>
            </div>
            <div class="login-form-panel">
                <div class="login-form-wrap">
                    <span class="kicker">SELAMAT DATANG KEMBALI</span>
                    <h2>Masuk ke akun</h2>
                    <p>Gunakan akun kontributor atau administrator.</p>

                    @if ($errors->any())<div class="validation-alert compact">{{ $errors->first() }}</div>@endif

                    <form method="POST" action="{{ route('login.store') }}" class="login-form">
                        @csrf
                        <label class="field"><span>Alamat email</span><input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required autofocus></label>
                        <label class="field"><span>Kata sandi</span><input type="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required></label>
                        <label class="check-field"><input type="checkbox" name="remember" value="1"><span>Ingat saya</span></label>
                        <button class="btn btn-primary btn-lg btn-block" type="submit">Masuk <span>→</span></button>
                    </form>

                    <div class="demo-accounts">
                        <strong>Akun demo</strong>
                        <button type="button" data-fill-login data-email="user@jelajahevent.test"><span>Kontributor</span><small>user@jelajahevent.test</small></button>
                        <button type="button" data-fill-login data-email="admin@jelajahevent.test"><span>Administrator</span><small>admin@jelajahevent.test</small></button>
                        <p>Kata sandi kedua akun: <code>password</code></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
