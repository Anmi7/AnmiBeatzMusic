<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Track extends Model
{
    use SoftDeletes;

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
        'deleted_at' => 'datetime',
    ];
}
