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
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim(str_replace('\\', '/', $path), '/');

        foreach (['products' => 'media.product-image', 'avatars' => 'media.account-avatar'] as $directory => $routeName) {
            if (!str_starts_with($path, $directory.'/')) {
                continue;
            }

            $filename = substr($path, strlen($directory.'/'));

            if (basename($filename) !== $filename || $filename === '') {
                return null;
            }

            return route($routeName, ['filename' => $filename]);
        }

        return asset('storage/'.$path);
    }
}