<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Upload cover image for a track. Admin only.
     * Returns public URL path for use in track image_url.
     */
    public function cover(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
        ]);

        $file = $request->file('image');
        $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('covers', $name, 'public');

        return response()->json([
            'url' => '/storage/' . $path,
        ]);
    }

    /**
     * Upload audio file for a track. Admin only.
     * Returns public URL path for use in track audio_url.
     */
    public function audio(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,ogg,m4a,aac,flac|max:25600', // 25MB
        ]);

        $file = $request->file('audio');
        $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('audio', $name, 'public');

        return response()->json([
            'url' => '/storage/' . $path,
        ]);
    }
}
