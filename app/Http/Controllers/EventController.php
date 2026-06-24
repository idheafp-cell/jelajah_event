<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;


//  Controller untuk menampilkan peta dan menjalankan proses CRUD event.
//  Alur CRUD terdiri dari menampilkan data, membuka form, menyimpan, mengubah, dan menghapus event dari PostgreSQL.

class EventController extends Controller
{
    // Objek model Event yang digunakan untuk membuat query Eloquent.

    private Event $events;

    // Menyiapkan model Event saat controller dibuat oleh Laravel.
    // Bentuk ini mengikuti pola constructor pada contoh PointsController, tetapi menggunakan nama model Event milik proyek Jelajah Event.

    public function __construct()
    {
        $this->events = new Event;
    }

    // Menampilkan halaman peta beserta event yang lolos filter.

    public function map(Request $request): View
    {
        // Ambil event berdasarkan kategori dan rentang tanggal pencarian.
        $events = $this->filteredQuery($request)
            ->orderBy('start_date')
            ->get();

        // Kirim event dan kategori ke resources/views/events/map.blade.php.
        return view('events.map', [
            'events' => $events,
            'categories' => Event::CATEGORIES,
        ]);
    }

    // Menampilkan daftar event dalam bentuk tabel dengan pagination.

    public function index(Request $request): View
    {
        // with('user') memuat data pemilik event dalam query yang efisien.
        $events = $this->filteredQuery($request)
            ->with('user')
            ->orderBy('start_date')
            ->paginate(10)
            ->withQueryString();

        // Kirim hasil pagination ke resources/views/events/index.blade.php.
        return view('events.index', [
            'events' => $events,
            'categories' => Event::CATEGORIES,
        ]);
    }


    //  Menampilkan form untuk menambahkan event baru.

    // Route method ini dilindungi middleware auth sehingga pengguna harus login.

    public function create(): View
    {
        // Model kosong membuat satu view dapat dipakai untuk tambah dan edit.
        return view('events.form', [
            'event' => new Event,
            'categories' => Event::CATEGORIES,
            'formTitle' => 'Tambahkan event baru',
        ]);
    }

    // Memvalidasi dan menyimpan event baru ke database.

    public function store(Request $request): RedirectResponse
    {
        // Validasi seluruh data yang dikirim dari form event.
        $data = $this->validateEvent($request);

        // Catat ID pengguna yang menambahkan event.
        $data['user_id'] = $request->user()->id;

        // Simpan poster ke storage/app/public/posters jika ada file upload.
        if ($request->hasFile('poster')) {
            $data['poster_path'] = $request->file('poster')->store('posters', 'public');
        }

        // Simpan data melalui objek model yang disiapkan pada constructor.
        $this->events->newQuery()->create($data);

        // Kembali ke peta dan tampilkan pesan berhasil.
        return redirect()->route('events.map')
            ->with('success', 'Titik event berhasil ditambahkan.');
    }

    // Menampilkan form edit untuk event yang dipilih.

    // Parameter $event ditemukan otomatis melalui route model binding.

    public function edit(Event $event): View
    {
        return view('events.form', [
            'event' => $event,
            'categories' => Event::CATEGORIES,
            'formTitle' => 'Edit event',
        ]);
    }

    // Memvalidasi dan memperbarui event yang sudah ada.
    public function update(Request $request, Event $event): RedirectResponse
    {
        // Gunakan aturan validasi yang sama seperti proses tambah event.
        $data = $this->validateEvent($request);

        // Jika poster diganti, hapus poster upload lama lalu simpan yang baru.
        if ($request->hasFile('poster')) {
            $this->deleteUploadedPoster($event);
            $data['poster_path'] = $request->file('poster')->store('posters', 'public');
        }

        // Perbarui baris event pada database.
        $event->update($data);

        // Kembali ke tabel event setelah update selesai.
        return redirect()->route('events.map')
            ->with('success', 'Event berhasil diperbarui.');
    }

    // Menghapus event beserta file poster upload yang terkait.

    public function destroy(Event $event): RedirectResponse
    {
        // Hapus poster terlebih dahulu agar tidak menjadi file sampah.
        $this->deleteUploadedPoster($event);

        // Hapus data event dari database.
        $event->delete();

        return redirect()->route('events.map')
            ->with('success', 'Event berhasil dihapus.');
    }


    // Membuat query filter kategori dan irisan rentang tanggal.

    // Event ditampilkan jika start_date <= tanggal akhir pencarian DAN end_date >= tanggal awal pencarian.

    private function filteredQuery(Request $request): Builder
    {
        // Validasi parameter pencarian sebelum digunakan dalam query.
        $request->validate([
            'date_from' => ['nullable', 'required_with:date_to', 'date'],
            'date_to' => ['nullable', 'required_with:date_from', 'date', 'after_or_equal:date_from'],
            'category' => ['nullable', Rule::in(array_keys(Event::CATEGORIES))],
        ], [
            'date_from.required_with' => 'Tanggal awal harus diisi untuk menggunakan rentang waktu.',
            'date_to.required_with' => 'Tanggal akhir harus diisi untuk menggunakan rentang waktu.',
            'date_to.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
            'category.in' => 'Kategori yang dipilih tidak valid.',
        ]);

        // when() hanya menjalankan kondisi query jika form filternya terisi.
        return $this->events->newQuery()
            ->when(
                $request->filled('date_from') && $request->filled('date_to'),
                fn (Builder $query) => $query
                    ->whereDate('start_date', '<=', $request->date_to)
                    ->whereDate('end_date', '>=', $request->date_from)
            )
            ->when(
                $request->filled('category')
                    && array_key_exists($request->category, Event::CATEGORIES),
                fn (Builder $query) => $query->where('category', $request->category)
            );
    }

    // Menyimpan semua aturan validasi form event pada satu tempat.

    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::in(array_keys(Event::CATEGORIES))],
            'description' => ['required', 'string', 'max:2000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'location_name' => ['required', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-8.2,-7.4'],
            'longitude' => ['required', 'numeric', 'between:110.0,110.9'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'end_date.after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai.',
        ]);
    }

    // Menghapus poster upload tanpa menghapus poster bawaan public/images.

    private function deleteUploadedPoster(Event $event): void
    {
        // Path "images/" adalah aset bawaan dan tidak boleh ikut dihapus.
        if ($event->poster_path && ! str_starts_with($event->poster_path, 'images/')) {
            Storage::disk('public')->delete($event->poster_path);
        }
    }
}
