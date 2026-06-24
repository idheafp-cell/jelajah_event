<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Controller yang menangani login dan logout pengguna.

class AuthController extends Controller
{
    //  Menampilkan halaman formulir login.

    public function create(): View
    {
        return view('auth.login');
    }

    // Memeriksa email dan password, lalu membuat sesi login.

    public function store(Request $request): RedirectResponse
    {
        // Pastikan email valid dan password tidak kosong.
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Auth::attempt() mencocokkan data form dengan tabel users.
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau kata sandi tidak sesuai.',
            ])->onlyInput('email');
        }

        // Buat ID sesi baru untuk mencegah serangan session fixation.
        $request->session()->regenerate();

        // Arahkan pengguna ke tujuan semula atau ke halaman peta.
        return redirect()->intended(route('events.map'))
            ->with('success', 'Selamat datang, '.Auth::user()->name.'!');
    }

    // Mengakhiri sesi login pengguna dengan aman.
    public function destroy(Request $request): RedirectResponse
    {
        // Keluarkan pengguna dari sistem autentikasi.
        Auth::logout();

        // Hapus data sesi lama dan buat token CSRF baru.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Anda berhasil keluar.');
    }
}
