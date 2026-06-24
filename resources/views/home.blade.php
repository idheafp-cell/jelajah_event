@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <section class="hero">
        <h1>Temukan event Yogyakarta</h1>
        <a href="{{ route('events.map') }}">Jelajahi peta</a>
    </section>

    <section class="category-grid">
        @foreach (\App\Models\Event::CATEGORIES as $key => $label)
            <article class="category-card">
                <h2>{{ $label }}</h2>
                <strong>{{ $counts[$key] ?? 0 }} event</strong>
            </article>
        @endforeach
    </section>

    <section class="event-card-grid">
        @foreach ($upcomingEvents as $event)
            <article class="event-card">
                <img src="{{ $event->poster_url }}" alt="Poster {{ $event->name }}">
                <h2>{{ $event->name }}</h2>
                <p>{{ $event->date_range_label }}</p>
            </article>
        @endforeach
    </section>
@endsection
