<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel events sebagai penyimpanan utama data titik event.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            // ID utama dan relasi ke pengguna yang membuat event.
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Informasi utama event.
            $table->string('name');
            $table->enum('category', ['kuliner', 'musik', 'olahraga', 'budaya']);
            $table->text('description');
            // Kolom ini kemudian diubah menjadi start_date oleh migration berikutnya.
            $table->date('start_date');
            $table->date('end_date');
            $table->time('event_time')->nullable();
            $table->string('location_name');
            $table->string('address');
            // Koordinat digunakan untuk meletakkan marker pada peta Leaflet.
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('poster_path')->nullable();
            $table->timestamps();

            // Index mempercepat pencarian berdasarkan tanggal dan kategori.
            $table->index(['start_date', 'category']);
        });
    }

    /**
     * Menghapus tabel events jika migration dibatalkan.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
