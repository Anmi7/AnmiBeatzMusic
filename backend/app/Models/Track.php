<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'description',
        'genre',
        'image_url',
        'audio_url',
        'youtube_link',
        'spotify_link',
        'plays',
        'duration',
        'release_date',
        'featured'
    ];

    protected $casts = [
        'release_date' => 'date',
        'featured' => 'boolean',
    ];
}
