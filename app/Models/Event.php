<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'kuliner' => 'Kuliner',
        'musik' => 'Musik',
        'olahraga' => 'Olahraga',
        'budaya' => 'Budaya',
    ];

    protected $fillable = [
        'user_id', 'name', 'category', 'description',
        'start_date', 'end_date', 'event_time',
        'location_name', 'address', 'latitude',
        'longitude', 'poster_path',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPosterUrlAttribute(): string
{
    // poster default jika event belum memiliki poster
    if (! $this->poster_path) {
        return asset('images/posters/default.svg');
    }

    
    return str_starts_with($this->poster_path, 'images/')
        ? asset($this->poster_path)
        : asset('storage/'.$this->poster_path);
}
}
