<?php

namespace App\Support;

/**
 * Resizes/recompresses an already-uploaded image in place using PHP's built-in
 * GD extension — deliberately no Intervention Image or other composer
 * dependency, since GD already ships with this server's PHP install. Keeps
 * the original format/extension (no WebP conversion) to avoid touching any
 * stored URL. GIFs (animation would be flattened by GD) and SVGs (not a
 * raster format) are left untouched.
 */
class ImageOptimizer
{
    /** Anything wider/taller than this gets scaled down, aspect ratio preserved. */
    protected const MAX_DIMENSION = 2000;

    protected const JPEG_QUALITY = 82;

    protected const PNG_COMPRESSION = 6; // 0 (none) - 9 (max), PNG compression is lossless either way

    protected const WEBP_QUALITY = 82;

    public static function optimize(string $absolutePath, string $mimeType): void
    {
        if (! extension_loaded('gd') || ! is_file($absolutePath)) {
            return;
        }

        $image = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($absolutePath),
            'image/png' => @imagecreatefrompng($absolutePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
            default => false, // gif, svg, anything else: leave as-is
        };

        if (! $image) {
            return;
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

        match ($mimeType) {
            'image/jpeg' => imagejpeg($image, $absolutePath, self::JPEG_QUALITY),
            'image/png' => imagepng($image, $absolutePath, self::PNG_COMPRESSION),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, $absolutePath, self::WEBP_QUALITY) : null,
            default => null,
        };

        imagedestroy($image);
    }
}
