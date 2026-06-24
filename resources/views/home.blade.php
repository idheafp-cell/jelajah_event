@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <section class="hero">
        <div class="hero-orb hero-orb-one"></div>
        <div class="hero-orb hero-orb-two"></div>
        <div class="container hero-grid">
            <div class="hero-copy">

                <h1>WebGIS<br><em>Event Yogyakarta.</em></h1>
                <p>Jelajah Event membantu menemukan pengalaman terbaik dari panggung musik, cita rasa lokal, semangat olahraga, hingga tradisi yang terus hidup.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary btn-lg" href="{{ route('events.map') }}">
                        Jelajahi peta
                        <span aria-hidden="true">→</span>
                    </a>
                    <a class="text-link" href="{{ route('events.index') }}">Lihat semua event <span>↗</span></a>
                </div>
            </div>
        </div>
    </section>

    <section class="section categories-section">
        <div class="container">
            <div class="section-heading">
                <div><span class="kicker">PILIH EVENT KAMU</span><h2>Ada cerita untuk<br>setiap rasa penasaran.</h2></div>

            </div>
            <div class="category-grid">
                @php
                    $categoryMeta = [
                        'kuliner' => ['icon' => '🍜', 'text' => 'Cicipi rasa otentik dari dapur dan pasar pilihan.', 'class' => 'food'],
                        'musik' => ['icon' => '♫', 'text' => 'Dengarkan nada dari panggung intim sampai festival.', 'class' => 'music'],
                        'olahraga' => ['icon' => '⚡', 'text' => 'Bergerak bersama komunitas di rute istimewa.', 'class' => 'sport'],
                        'budaya' => ['icon' => '✦', 'text' => 'Rayakan tradisi dan karya yang menjaga cerita.', 'class' => 'culture'],
                    ];
                @endphp
                @foreach ($categoryMeta as $key => $meta)
                    <a class="category-card {{ $meta['class'] }}" href="{{ route('events.index', ['category' => $key]) }}">
                        <span class="category-icon">{{ $meta['icon'] }}</span>
                        <span class="category-count">{{ str_pad($counts[$key] ?? 0, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3>{{ \App\Models\Event::CATEGORIES[$key] }}</h3>
                        <p>{{ $meta['text'] }}</p>
                        <span class="category-arrow">↗</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container cta-inner">
            <div><span class="kicker light">PUNYA INFORMASI EVENT?</span><h2>Tambahkan titik baru<br>ke dalam cerita.</h2></div>
            @auth
                <a class="btn btn-light btn-lg" href="{{ route('events.create') }}">Tambah event <span>＋</span></a>
            @else
                <a class="btn btn-light btn-lg" href="{{ route('login') }}">Masuk untuk menambah <span>→</span></a>
            @endauth
        </div>
    </section>
@endsection
