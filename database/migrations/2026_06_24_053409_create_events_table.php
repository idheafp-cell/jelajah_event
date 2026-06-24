<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->enum('category', ['kuliner', 'musik', 'olahraga', 'budaya']);
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('event_time')->nullable();
            $table->string('location_name', 150);
            $table->string('address');
            $table->geometry('geom');
            $table->string('poster_path')->nullable();
            $table->timestamps();

            $table->index(['start_date', 'end_date', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
