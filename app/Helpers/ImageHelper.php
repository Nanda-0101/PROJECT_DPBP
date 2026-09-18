<?php

namespace App\Helpers;

class ImageHelper
{
    /**
     * Render URL image dari path lokal atau URL eksternal.
     * - Kalau path mulai http:// atau https:// → return apa adanya
     * - Kalau path kosong → return null
     * - Kalau path lokal (products/xxx.jpg) → return asset('storage/'.$path)
     */
    public static function url(?string $path): ?string
    {
        if (empty($path)) return null;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}