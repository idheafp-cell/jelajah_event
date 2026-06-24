<?php

use App\Models\User;
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
        'location_name', 'address', 'geom',
        'poster_path',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'geom' => 'geometry',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
