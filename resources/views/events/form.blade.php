@extends('layouts.app')

@section('title', $formTitle)

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <section class="form-page">
        <div class="container narrow-container">
            <a class="back-link" href="{{ route('events.map') }}">← Kembali ke peta</a>
            <div class="form-heading">

                <h1>{{ $formTitle }}</h1>
                <p>Lengkapi informasi berikut. Klik peta untuk mengisi koordinat lokasi secara otomatis.</p>
            </div>

            @if ($errors->any())
                <div class="validation-alert" role="alert">
                    <strong>Periksa kembali data yang Anda masukkan.</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form class="event-form" method="POST" enctype="multipart/form-data" action="{{ $event->exists ? route('events.update', $event) : route('events.store') }}">
                @csrf
                @if($event->exists) @method('PUT') @endif

                <section class="form-card">
                    <div class="form-section-title"><span>01</span><div><h2>Informasi event</h2><p>Nama, kategori, dan cerita singkat event.</p></div></div>
                    <div class="form-grid">
                        <label class="field full"><span>Nama event <b>*</b></span><input type="text" name="name" value="{{ old('name', $event->name) }}" placeholder="Contoh: Festival Kuliner Nusantara" required></label>
                        <label class="field"><span>Kategori <b>*</b></span><select name="category" required><option value="">Pilih kategori</option>@foreach($categories as $value => $label)<option value="{{ $value }}" @selected(old('category', $event->category) === $value)>{{ $label }}</option>@endforeach</select></label>
                        <label class="field"><span>Poster <small>(JPG/PNG/WebP, maks. 2 MB)</small></span><input type="file" name="poster" accept="image/jpeg,image/png,image/webp"></label>
                        <label class="field full"><span>Deskripsi <b>*</b></span><textarea name="description" rows="5" placeholder="Ceritakan daya tarik utama event..." required>{{ old('description', $event->description) }}</textarea></label>
                    </div>
                    @if($event->poster_path)<div class="current-poster"><img src="{{ $event->poster_url }}" alt="Poster saat ini"><span>Poster saat ini</span></div>@endif
                </section>

                <section class="form-card">
                    <div class="form-section-title"><span>02</span><div><h2>Waktu pelaksanaan</h2><p>Tentukan tanggal dan jam mulainya acara.</p></div></div>
                    <div class="form-grid">
                        <label class="field"><span>Tanggal mulai <b>*</b></span><input type="date" name="start_date" value="{{ old('start_date', $event->start_date?->format('Y-m-d')) }}" required></label>
                        <label class="field"><span>Tanggal selesai <b>*</b></span><input type="date" name="end_date" min="{{ old('start_date', $event->start_date?->format('Y-m-d')) }}" value="{{ old('end_date', $event->end_date?->format('Y-m-d')) }}" required></label>
                        <label class="field"><span>Waktu mulai</span><input type="time" name="event_time" value="{{ old('event_time', $event->event_time ? substr($event->event_time, 0, 5) : '') }}"></label>
                    </div>
                </section>

                <section class="form-card">
                    <div class="form-section-title"><span>03</span><div><h2>Lokasi event</h2><p>Pastikan titik masih berada di wilayah Daerah Istimewa Yogyakarta.</p></div></div>
                    <div class="form-grid">
                        <label class="field"><span>Nama lokasi <b>*</b></span><input type="text" name="location_name" value="{{ old('location_name', $event->location_name) }}" placeholder="Contoh: Stadion Kridosono" required></label>
                        <label class="field"><span>Alamat lengkap <b>*</b></span><input type="text" name="address" value="{{ old('address', $event->address) }}" placeholder="Jalan, kelurahan, kabupaten/kota" required></label>
                        <label class="field"><span>Latitude <b>*</b></span><input id="latitude" type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $event->latitude) }}" placeholder="-7.7956000" required></label>
                        <label class="field"><span>Longitude <b>*</b></span><input id="longitude" type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $event->longitude) }}" placeholder="110.3695000" required></label>
                        <div class="field full"><span>Klik lokasi pada peta</span><div id="picker-map" class="picker-map"></div><small class="field-hint">Anda juga dapat menggeser penanda untuk memperbaiki posisi.</small></div>
                    </div>
                </section>

                <div class="form-actions">
                    <a class="btn btn-ghost" href="{{ route('events.index') }}">Batal</a>
                    <button class="btn btn-primary btn-lg" type="submit">{{ $event->exists ? 'Simpan perubahan' : 'Publikasikan event' }} <span>→</span></button>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof L === 'undefined' || !document.getElementById('picker-map')) return;
            const latitude = document.getElementById('latitude');
            const longitude = document.getElementById('longitude');
            const initial = [Number(latitude.value) || -7.7956, Number(longitude.value) || 110.3695];
            const map = L.map('picker-map').setView(initial, latitude.value ? 14 : 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'}).addTo(map);
            let marker = latitude.value ? L.marker(initial, {draggable: true}).addTo(map) : null;

            const updateFields = ({lat, lng}) => {
                latitude.value = lat.toFixed(7);
                longitude.value = lng.toFixed(7);
            };
            const placeMarker = (latlng) => {
                if (!marker) {
                    marker = L.marker(latlng, {draggable: true}).addTo(map);
                    marker.on('dragend', event => updateFields(event.target.getLatLng()));
                } else marker.setLatLng(latlng);
                updateFields(latlng);
            };
            if (marker) marker.on('dragend', event => updateFields(event.target.getLatLng()));
            map.on('click', event => placeMarker(event.latlng));
        });
    </script>
@endpush
