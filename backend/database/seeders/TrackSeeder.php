<?php

namespace Database\Seeders;

use App\Models\Track;
use Illuminate\Database\Seeder;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        // Sample tracks from your music-data.json
        $tracks = [
            [
                'title' => 'Dreams & Nightmares',
                'artist' => 'Anmi Beatz',
                'genre' => 'hip-hop',
                'image_url' => '/assets/images/music-covers/Cover2.jpg',
                'audio_url' => '/audio/Space Cake.wav',
                'youtube_link' => 'https://youtube.com/watch?v=YOUR_VIDEO_ID',
                'spotify_link' => 'https://open.spotify.com/track/YOUR_TRACK_ID',
                'release_date' => '2024-01-15',
                'featured' => true,
                'plays' => 125000,
                'duration' => '3:45'
            ],
            [
                'title' => 'Midnight Vibes',
                'artist' => 'Anmi Beatz',
                'genre' => 'trap',
                'image_url' => '/assets/images/music-covers/Cover3.jpg',
                'audio_url' => '/audio/Space Cake.wav',
                'youtube_link' => 'https://youtube.com/watch?v=YOUR_VIDEO_ID',
                'spotify_link' => 'https://open.spotify.com/track/YOUR_TRACK_ID',
                'release_date' => '2024-01-10',
                'featured' => true,
                'plays' => 98000,
                'duration' => '2:58'
            ],
            [
                'title' => 'Urban Pulse',
                'artist' => 'Anmi Beatz',
                'genre' => 'hip-hop',
                'image_url' => '/assets/images/music-covers/Cover4.jpg',
                'audio_url' => '/audio/Space Cake.wav',
                'youtube_link' => 'https://youtube.com/watch?v=YOUR_VIDEO_ID',
                'spotify_link' => 'https://open.spotify.com/track/YOUR_TRACK_ID',
                'release_date' => '2024-01-05',
                'featured' => false,
                'plays' => 75000,
                'duration' => '3:22'
            ],
            [
                'title' => 'Neon Nights',
                'artist' => 'Anmi Beatz',
                'genre' => 'electronic',
                'image_url' => '/assets/images/music-covers/Cover5.jpg',
                'audio_url' => '/audio/Space Cake.wav',
                'youtube_link' => 'https://youtube.com/watch?v=YOUR_VIDEO_ID',
                'spotify_link' => 'https://open.spotify.com/track/YOUR_TRACK_ID',
                'release_date' => '2024-01-01',
                'featured' => false,
                'plays' => 52000,
                'duration' => '4:15'
            ],
        ];

        foreach ($tracks as $track) {
            Track::create($track);
        }
    }
}
