<?php

namespace App\Support;

class ImageUrl
{
    public const PLACEHOLDER = 'images/placeholder.svg';

    /**
     * URL gambar siap tampil.
     * - Path lokal diarahkan ke storage publik.
     * - Gambar Cloudinary diberi transformasi f_auto,q_auto (+ lebar) supaya lebih ringan.
     */
    public static function make(?string $path, ?int $width = null): string
    {
        if (blank($path)) {
            return asset(self::PLACEHOLDER);
        }

        if (! str_starts_with($path, 'http')) {
            return asset('storage/'.$path);
        }

        if (str_contains($path, 'res.cloudinary.com') && str_contains($path, '/image/upload/')) {
            $transform = 'f_auto,q_auto'.($width ? ",c_limit,w_{$width}" : '');

            return str_replace('/image/upload/', "/image/upload/{$transform}/", $path);
        }

        return $path;
    }

    public static function placeholder(): string
    {
        return asset(self::PLACEHOLDER);
    }
}
