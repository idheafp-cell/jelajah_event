<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

// Controller untuk menyiapkan data pada landing page Jelajah Event.

class HomeController extends Controller
{
    // Menampilkan landing page beserta jumlah kategori dan event mendatang.
    public function index(): View
    {
        // Hitung jumlah event untuk setiap kategori yang tersedia.
        $counts = collect(Event::CATEGORIES)->mapWithKeys(
            fn (string $label, string $category) => [
                $category => Event::where('category', $category)->count(),
            ]
        );

        // Ambil maksimal tiga event yang tanggal akhirnya belum berlalu.
        $upcomingEvents = Event::query()
            ->whereDate('end_date', '>=', today())
            ->orderBy('start_date')
            ->paginate(10);

        // compact() mengirim kedua variabel tersebut ke home.blade.php.
        return view('home', compact('counts', 'upcomingEvents'));
    }
}
