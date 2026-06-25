@extends('layouts.app')

@section('title', 'Peta Event')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <section class="map-screen">
        <aside class="map-sidebar" aria-label="Daftar dan filter event" tabindex="0">
            <div class="map-sidebar-header">
                <div class="map-panel-heading">
                    <div>
                        <span class="kicker">PETA INTERAKTIF DIY</span>
                        <h1>Jelajahi event</h1>
                    </div>
                    @auth
                        <a class="btn btn-primary btn-sm" href="{{ route('events.create') }}">+ Titik</a>
                    @endauth
                </div>

                @if ($errors->any())
                    <div class="validation-alert compact">{{ $errors->first() }}</div>
                @endif

                <form class="map-filter-form" method="GET" action="{{ route('events.map') }}">
                    <div class="map-date-range">
                        <label class="filter-field">
                            <span>Dari tanggal</span>
                            <input type="date" name="date_from" value="{{ request('date_from') }}">
                        </label>
                        <label class="filter-field">
                            <span>Sampai tanggal</span>
                            <input type="date" name="date_to" min="{{ request('date_from') }}" value="{{ request('date_to') }}">
                        </label>
                    </div>
                    <label class="filter-field">
                        <span>Kategori</span>
                        <select name="category">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="map-filter-actions">
                        <button class="btn btn-dark" type="submit">Tampilkan event</button>
                        @if (request()->hasAny(['date_from', 'date_to', 'category']))
                            <a class="reset-link" href="{{ route('events.map') }}">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="map-list-heading">
                <div>
                    <strong>{{ $events->count() }} event ditemukan</strong>
                    <small>Klik event untuk melihat titiknya</small>
                </div>
                <div class="map-legend" aria-label="Legenda kategori">
                    <i class="legend-dot kuliner" title="Kuliner"></i>
                    <i class="legend-dot musik" title="Musik"></i>
                    <i class="legend-dot olahraga" title="Olahraga"></i>
                    <i class="legend-dot budaya" title="Budaya"></i>
                </div>
            </div>

            <div class="map-event-list" data-event-list>
                @forelse ($events as $event)
                    <div class="map-event-row">
                        <button class="map-event-item" type="button" data-event-index="{{ $loop->index }}">
                            <img src="{{ $event->poster_url }}" alt="Poster {{ $event->name }}">
                            <span class="map-event-copy">
                                <span class="map-event-meta">
                                    <span class="category-badge category-{{ $event->category }}">{{ $categories[$event->category] }}</span>
                                    <time datetime="{{ $event->start_date->format('Y-m-d') }}/{{ $event->end_date->format('Y-m-d') }}">{{ $event->date_range_label }}</time>
                                </span>
                                <strong>{{ $event->name }}</strong>
                                <small>⌖ {{ $event->location_name }}</small>
                            </span>
                            <span class="map-event-arrow" aria-hidden="true">›</span>
                        </button>

                    </div>
                @empty
                    <div class="map-list-empty">
                        <strong>Event tidak ditemukan</strong>
                        <p>Coba ubah rentang tanggal atau kategori.</p>
                    </div>
                @endforelse
            </div>
        </aside>

        <div class="map-canvas-wrap">
            <div id="event-map" class="event-map map-canvas" aria-label="Peta lokasi event Daerah Istimewa Yogyakarta"></div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @php
        $canManageEvents = auth()->user()?->isAdmin() ?? false;
        $mapEvents = $events->map(fn ($event) => [
            'name' => $event->name,
            'category' => $event->category,
            'category_label' => $categories[$event->category],
            'description' => $event->description,
            'date' => $event->date_range_label,
            'time' => $event->event_time ? substr($event->event_time, 0, 5).' WIB' : null,
            'location' => $event->location_name,
            'latitude' => $event->latitude,
            'longitude' => $event->longitude,
            'poster' => $event->poster_url,
            'edit_url' => $canManageEvents ? route('events.edit', $event) : null,
            'delete_url' => $canManageEvents ? route('events.destroy', $event) : null,
        ]);
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mapElement = document.getElementById('event-map');
            if (!mapElement || typeof L === 'undefined') return;

            const events = {{ Illuminate\Support\Js::from($mapEvents) }};
            const iconText = {kuliner: '\uD83C\uDF5C', musik: '\u266B', olahraga: '\u26A1', budaya: '\u2726'};
            const map = L.map('event-map', {zoomControl: false}).setView([-7.7956, 110.3695], 11);
            L.control.zoom({position: 'bottomright'}).addTo(map);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                updateWhenIdle: true,
                keepBuffer: 2,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, char => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
            })[char]);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const bounds = [];
            const markers = [];
            const eventItems = [...document.querySelectorAll('[data-event-index]')];

            const selectEvent = (index, focusMap = true) => {
                const marker = markers[index];
                const event = events[index];
                if (!marker || !event) return;

                eventItems.forEach(item => item.classList.toggle('active', Number(item.dataset.eventIndex) === index));
                if (focusMap) map.flyTo([event.latitude, event.longitude], 15, {duration: .7});
                marker.openPopup();
            };

            events.forEach(event => {
                const markerIcon = L.divIcon({
                    className: 'custom-map-icon',
                    html: `<span class="map-pin map-pin-${event.category}"><b>${iconText[event.category]}</b></span>`,
                    iconSize: [44, 52],
                    iconAnchor: [22, 48],
                    popupAnchor: [0, -42]
                });
                let managementActions = '';
                @if($canManageEvents)
                    managementActions = `
                        <div class="map-popup-actions">
                            <a class="popup-edit-button" href="${escapeHtml(event.edit_url)}">Edit</a>
                            <form class="popup-delete-form" method="POST" action="${escapeHtml(event.delete_url)}" data-event-name="${escapeHtml(event.name)}">
                                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit">Hapus</button>
                            </form>
                        </div>`;
                @endif

                const popup = `
                    <article class="map-popup-card">
                        <img src="${escapeHtml(event.poster)}" alt="Poster ${escapeHtml(event.name)}">
                        <div class="map-popup-body">
                            <span class="category-badge category-${event.category}">${escapeHtml(event.category_label)}</span>
                            <h3>${escapeHtml(event.name)}</h3>
                            <p class="popup-date">${escapeHtml(event.date)}${event.time ? ' &middot; ' + escapeHtml(event.time) : ''}</p>
                            <p>${escapeHtml(event.description)}</p>
                            <small>&#8982; ${escapeHtml(event.location)}</small>
                            ${managementActions}
                        </div>
                    </article>`;

                const marker = L.marker([event.latitude, event.longitude], {icon: markerIcon})
                    .addTo(map)
                    .bindPopup(popup, {maxWidth: 330, minWidth: 280});
                marker.on('click', () => selectEvent(markers.indexOf(marker), false));
                markers.push(marker);
                bounds.push([event.latitude, event.longitude]);
            });

            eventItems.forEach(item => {
                item.addEventListener('click', () => selectEvent(Number(item.dataset.eventIndex)));
            });

            document.addEventListener('submit', event => {
                const form = event.target.closest('.popup-delete-form');
                if (form && !window.confirm(`Hapus event ${form.dataset.eventName}?`)) {
                    event.preventDefault();
                }
            });

            if (bounds.length > 1) map.fitBounds(bounds, {padding: [50, 50], maxZoom: 13});
            if (bounds.length === 1) map.setView(bounds[0], 14);
        });
    </script>
@endpush
