<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat akun kontributor. updateOrCreate mencegah email ganda.
        $user = User::updateOrCreate(
            ['email' => 'user@jelajahevent.test'],
            [
                'name' => 'Kontributor Event',
                'role' => 'user',
                'password' => Hash::make('password'),
            ]
        );

        // Buat akun admin yang memiliki izin tambah, edit, dan delete.
        $admin = User::updateOrCreate(
            ['email' => 'admin@jelajahevent.test'],
            [
                'name' => 'Admin Jelajah Event',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
