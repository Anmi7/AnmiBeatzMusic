<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    /**
     * Display a listing of the resource.
     * Query params: title, artist, genre, release_date_from, release_date_to
     */
    public function index(Request $request)
    {
        $query = Track::query();

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }
        if ($request->filled('artist')) {
            $query->where('artist', 'like', '%' . $request->input('artist') . '%');
        }
        if ($request->filled('genre')) {
            $genre = strtolower(trim((string) $request->input('genre')));
            $query->whereRaw('LOWER(genre) LIKE ?', ['%' . $genre . '%']);
        }
        if ($request->filled('release_date_from')) {
            $query->whereDate('release_date', '>=', $request->input('release_date_from'));
        }
        if ($request->filled('release_date_to')) {
            $query->whereDate('release_date', '<=', $request->input('release_date_to'));
        }

        return $query->orderBy('release_date', 'desc')->get();
    }

    /**
     * Get featured tracks
     */
    public function featured()
    {
        return Track::where('featured', true)->get();
    }

    /**
     * Get tracks by genre
     */
    public function byGenre($genre)
    {
        $genre = strtolower(trim((string) $genre));
        return Track::whereRaw('LOWER(genre) LIKE ?', ['%' . $genre . '%'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'description' => 'nullable|string',
            'genre' => 'required|string|max:500',
            'image_url' => 'required|string',
            'audio_url' => 'nullable|string',
            'youtube_link' => 'nullable|url|string',
            'spotify_link' => 'nullable|url|string',
            'duration' => 'nullable|string|max:20',
            'release_date' => 'required|date',
            'featured' => 'boolean',
        ]);

        $validated['audio_url'] = $validated['audio_url'] ?? null;
        $validated['duration'] = $validated['duration'] ?? null;

        return Track::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Track::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $track = Track::findOrFail($id);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'artist' => 'string|max:255',
            'description' => 'nullable|string',
            'genre' => 'string|max:500',
            'image_url' => 'string',
            'audio_url' => 'nullable|string',
            'youtube_link' => 'nullable|string',
            'spotify_link' => 'nullable|string',
            'duration' => 'nullable|string|max:20',
            'release_date' => 'date',
            'featured' => 'boolean',
            'plays' => 'integer',
        ]);

        $track->update($validated);
        return $track;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Track::findOrFail($id)->delete();
        return response()->json(['message' => 'Track deleted successfully']);
    }
}
