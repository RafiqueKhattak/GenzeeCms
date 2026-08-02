<?php

namespace App\Support;

/**
 * Resizes and re-encodes an already-uploaded image using PHP's built-in GD
 * extension — deliberately no Intervention Image or other composer
 * dependency, since GD already ships with this server's PHP install.
 *
 * JPEG/PNG uploads are converted to WebP (smaller than JPEG at equivalent
 * quality, universally supported by browsers now, and what actually moves
 * the needle on page-load speed for a content-heavy site like this one).
 * Quality steps down from 82 in increments of 8 until the file is at or
 * under TARGET_MAX_BYTES, stopping at TARGET_MIN_QUALITY so a huge source
 * image can't be crushed into a blurry mess just to hit a byte target.
 *
 * GIFs (animation would be flattened by GD) and SVGs (not a raster format)
 * are left completely untouched, same as before.
 */
class ImageOptimizer
{
    /** Anything wider/taller than this gets scaled down, aspect ratio preserved. */
    protected const MAX_DIMENSION = 2000;

    protected const TARGET_MAX_BYTES = 500 * 1024;

    protected const STARTING_QUALITY = 82;

    protected const MIN_QUALITY = 40;

    protected const QUALITY_STEP = 8;

    /**
     * @return array{path: string, mime: string}|null The new absolute path
     *   and mime type if the file was converted/re-encoded, or null if it
     *   was left as-is (GD unavailable, unsupported format, or webp support
     *   missing).
     */
    public static function optimize(string $absolutePath, string $mimeType): ?array
    {
        if (! extension_loaded('gd') || ! is_file($absolutePath) || ! function_exists('imagewebp')) {
            return null;
        }

        $image = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($absolutePath),
            'image/png' => @imagecreatefrompng($absolutePath),
            'image/webp' => @imagecreatefromwebp($absolutePath),
            default => false, // gif, svg, anything else: leave as-is
        };

        if (! $image) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
            $ratio = min(self::MAX_DIMENSION / $width, self::MAX_DIMENSION / $height);
            $newWidth = max(1, (int) round($width * $ratio));
            $newHeight = max(1, (int) round($height * $ratio));

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            imagedestroy($image);
            $image = $resized;
        }

        $webpPath = preg_replace('/\.[a-zA-Z0-9]+$/', '.webp', $absolutePath);

        $quality = self::STARTING_QUALITY;
        do {
            imagewebp($image, $webpPath, $quality);
            $size = filesize($webpPath);
            $quality -= self::QUALITY_STEP;
        } while ($size > self::TARGET_MAX_BYTES && $quality >= self::MIN_QUALITY);

        imagedestroy($image);

        if ($webpPath !== $absolutePath) {
            @unlink($absolutePath);
        }

        return [
            'path' => $webpPath,
            'mime' => 'image/webp',
        ];
    }
}
