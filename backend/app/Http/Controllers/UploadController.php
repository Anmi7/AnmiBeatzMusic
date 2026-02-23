<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class UploadController extends Controller
{
    /**
     * Upload cover image for a track. Admin only.
     * Returns public URL path for use in track image_url.
     */
    public function cover(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:20480', // 20MB
        ]);

        $file = $request->file('image');
        $processed = $this->prepareSquareCoverImage($file->getRealPath());
        $name = Str::uuid() . '.' . $processed['extension'];
        $path = 'covers/' . $name;

        $this->storePublicAsset($path, $processed['binary']);

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
        $audioBinary = @file_get_contents($file->getRealPath());
        if ($audioBinary === false) {
            throw ValidationException::withMessages([
                'audio' => 'Unable to read uploaded audio file.',
            ]);
        }
        $name = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
        $path = 'audio/' . $name;
        $this->storePublicAsset($path, $audioBinary);

        return response()->json([
            'url' => '/storage/' . $path,
        ]);
    }

    /**
     * Store file to Laravel's public disk and mirror to public/storage when that
     * path is a real directory (no symlink), which is common on some Windows setups.
     */
    private function storePublicAsset(string $relativePath, string $binary): void
    {
        Storage::disk('public')->put($relativePath, $binary);

        $publicStorage = public_path('storage');
        if (is_link($publicStorage)) {
            return;
        }

        $targetPath = $publicStorage . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Failed to create public storage directory: ' . $targetDir);
        }

        if (@file_put_contents($targetPath, $binary) === false) {
            throw new RuntimeException('Failed to mirror file into public storage.');
        }
    }

    /**
     * Center-crop to a square, resize to a fixed output size, then compress.
     * This guarantees uniform cover dimensions without stretching.
     */
    private function prepareSquareCoverImage(string $sourcePath): array
    {
        if (!function_exists('imagecreatefromstring')) {
            throw ValidationException::withMessages([
                'image' => 'Server image processing (GD) is not available.',
            ]);
        }

        $raw = @file_get_contents($sourcePath);
        if ($raw === false) {
            throw ValidationException::withMessages([
                'image' => 'Unable to read uploaded image.',
            ]);
        }

        $source = @imagecreatefromstring($raw);
        if (!$source) {
            throw ValidationException::withMessages([
                'image' => 'Unsupported or corrupted image file.',
            ]);
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $squareSize = min($sourceWidth, $sourceHeight);
        $srcX = (int) floor(($sourceWidth - $squareSize) / 2);
        $srcY = (int) floor(($sourceHeight - $squareSize) / 2);

        $outputSize = 1200;
        $canvas = imagecreatetruecolor($outputSize, $outputSize);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            $srcX,
            $srcY,
            $outputSize,
            $outputSize,
            $squareSize,
            $squareSize
        );

        $binary = null;
        $extension = null;
        ob_start();
        if (function_exists('imagewebp')) {
            imagewebp($canvas, null, 82);
            $binary = ob_get_clean();
            $extension = 'webp';
        } else {
            imagejpeg($canvas, null, 84);
            $binary = ob_get_clean();
            $extension = 'jpg';
        }

        imagedestroy($source);
        imagedestroy($canvas);

        if (!$binary) {
            throw ValidationException::withMessages([
                'image' => 'Failed to optimize image.',
            ]);
        }

        return [
            'binary' => $binary,
            'extension' => $extension,
        ];
    }
}
