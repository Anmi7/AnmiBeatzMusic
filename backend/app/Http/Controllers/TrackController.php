<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    /**
     * Display a listing of the resource.
     * Query params:
     * - title, artist, genre, release_date_from, release_date_to
     * - search (title/artist/genre)
     * - sort_by (title|artist|genre|release_date|created_at|id)
     * - sort_dir (asc|desc)
     * - paginate=1 or page/per_page for paginated response
     */
    public function index(Request $request)
    {
        $query = $this->buildTrackListQuery($request, Track::query());

        return $this->respondWithTrackList($request, $query);
    }

    /**
     * List only soft-deleted tracks (admin use).
     */
    public function deleted(Request $request)
    {
        $query = $this->buildTrackListQuery($request, Track::onlyTrashed());

        return $this->respondWithTrackList($request, $query);
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

        $validated['artist'] = $this->resolveCanonicalArtist($validated['artist']);
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

        if (array_key_exists('artist', $validated)) {
            $validated['artist'] = $this->resolveCanonicalArtist($validated['artist'], (int) $track->id);
        }

        $track->update($validated);
        return $track;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Track::findOrFail($id)->delete();
        return response()->json(['message' => 'Track moved to recovery successfully']);
    }

    /**
     * Restore a soft-deleted track.
     */
    public function restore(string $id)
    {
        $track = Track::onlyTrashed()->findOrFail($id);
        $track->restore();

        return response()->json([
            'message' => 'Track restored successfully',
            'track' => $track->fresh(),
        ]);
    }

    /**
     * Keep the first artist casing ever stored, based on case-insensitive match.
     * Example: "SAJKA" exists -> later "sajka" saves as "SAJKA".
     */
    private function resolveCanonicalArtist(string $artist, ?int $excludeTrackId = null): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $artist) ?? $artist);
        if ($normalized === '') {
            return $artist;
        }

        $query = Track::withTrashed()
            ->whereRaw('LOWER(artist) = ?', [strtolower($normalized)]);

        if ($excludeTrackId !== null) {
            $query->where('id', '!=', $excludeTrackId);
        }

        $existing = $query
            ->orderBy('id', 'asc')
            ->value('artist');

        return is_string($existing) && trim($existing) !== ''
            ? trim($existing)
            : $normalized;
    }

    private function buildTrackListQuery(Request $request, Builder $query): Builder
    {
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

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('artist', 'like', '%' . $search . '%')
                    ->orWhere('genre', 'like', '%' . $search . '%');
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $search)) {
                    $q->orWhereDate('release_date', '=', $search);
                }
            });
        }

        $allowedSort = ['id', 'title', 'artist', 'genre', 'release_date', 'created_at', 'deleted_at'];
        $sortBy = (string) $request->input('sort_by', 'release_date');
        if (!in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'release_date';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'desc'));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        return $query->orderBy($sortBy, $sortDir)->orderBy('id', 'desc');
    }

    private function respondWithTrackList(Request $request, Builder $query)
    {
        $shouldPaginate = $request->boolean('paginate')
            || $request->has('page')
            || $request->has('per_page');

        if ($shouldPaginate) {
            $perPage = (int) $request->input('per_page', 10);
            $perPage = max(1, min(100, $perPage));
            return $query->paginate($perPage)->withQueryString();
        }

        return $query->get();
    }
}
