@extends('layouts.app')

@section('title', 'Daftar Event')

@section('content')
    <section class="page-hero list-page-hero">
        <div class="container page-heading-row">
            <div>
                <h1>Daftar event<br><em>pilihan.</em></h1>
                <p>Lihat jadwal, kategori, dan lokasi event.</p>
            </div>
            @auth
                <a class="btn btn-primary" href="{{ route('events.create') }}">＋ Tambah event</a>
            @endauth
        </div>
    </section>

    <section class="section table-section">
        <div class="container">
            @if ($errors->any())
                <div class="validation-alert compact">{{ $errors->first() }}</div>
            @endif
            <form class="list-filter" method="GET" action="{{ route('events.index') }}">
                <div class="category-tabs">
                    <a class="{{ !request('category') ? 'active' : '' }}" href="{{ route('events.index', array_filter(['date_from' => request('date_from'), 'date_to' => request('date_to')])) }}">Semua</a>
                    @foreach ($categories as $value => $label)
                        <a class="{{ request('category') === $value ? 'active' : '' }}" href="{{ route('events.index', array_filter(['category' => $value, 'date_from' => request('date_from'), 'date_to' => request('date_to')])) }}">{{ $label }}</a>
                    @endforeach
                </div>
                <div class="date-range-filter">
                    <label class="date-quick-filter"><span>Dari</span><input type="date" name="date_from" value="{{ request('date_from') }}"></label>
                    <label class="date-quick-filter"><span>Sampai</span><input type="date" name="date_to" min="{{ request('date_from') }}" value="{{ request('date_to') }}"></label>
                    @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                    <button class="btn btn-dark btn-sm" type="submit">Terapkan</button>
                    @if(request()->hasAny(['date_from', 'date_to']))<a class="reset-link inline" href="{{ route('events.index', array_filter(['category' => request('category')])) }}">Reset</a>@endif
                </div>
            </form>

            <div class="table-card">
                <div class="table-summary"><strong>{{ $events->total() }} event</strong><span>Diurutkan berdasarkan tanggal terdekat</span></div>
                <div class="table-scroll">
                    <table class="event-table">
                        <thead><tr><th>Event</th><th>Kategori</th><th>Waktu</th><th>Lokasi</th>@if(auth()->user()?->isAdmin())<th>Aksi</th>@endif</tr></thead>
                        <tbody>
                            @forelse ($events as $event)
                                <tr>
                                    <td>
                                        <div class="table-event">
                                            <img src="{{ $event->poster_url }}" alt="Poster {{ $event->name }}">
                                            <div><strong>{{ $event->name }}</strong><small>{{ \Illuminate\Support\Str::limit($event->description, 70) }}</small></div>
                                        </div>
                                    </td>
                                    <td><span class="category-badge category-{{ $event->category }}">{{ $categories[$event->category] }}</span></td>
                                    <td><strong>{{ $event->date_range_label }}</strong><small class="cell-sub">{{ $event->event_time ? substr($event->event_time, 0, 5).' WIB' : 'Waktu tentatif' }}</small></td>
                                    <td><strong>{{ $event->location_name }}</strong><small class="cell-sub">{{ \Illuminate\Support\Str::limit($event->address, 46) }}</small></td>
                                    @if(auth()->user()?->isAdmin())
                                        <td>
                                            <div class="action-buttons">
                                                <a class="icon-btn" href="{{ route('events.edit', $event) }}" title="Edit event" aria-label="Edit {{ $event->name }}">✎</a>
                                                <form method="POST" action="{{ route('events.destroy', $event) }}" data-confirm="Hapus event {{ $event->name }}?">
                                                    @csrf @method('DELETE')
                                                    <button class="icon-btn danger" type="submit" title="Hapus event" aria-label="Hapus {{ $event->name }}">×</button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ auth()->user()?->isAdmin() ? 5 : 4 }}"><div class="empty-state">Tidak ada event yang sesuai dengan filter.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($events->hasPages())
                    <nav class="pagination" aria-label="Navigasi halaman">
                        @if ($events->onFirstPage())<span class="disabled">← Sebelumnya</span>@else<a href="{{ $events->previousPageUrl() }}">← Sebelumnya</a>@endif
                        <span>Halaman {{ $events->currentPage() }} dari {{ $events->lastPage() }}</span>
                        @if ($events->hasMorePages())<a href="{{ $events->nextPageUrl() }}">Berikutnya →</a>@else<span class="disabled">Berikutnya →</span>@endif
                    </nav>
                @endif
            </div>
        </div>
    </section>
@endsection
