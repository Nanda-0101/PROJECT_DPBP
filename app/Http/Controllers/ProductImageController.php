<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function show(string $filename)
    {
        return $this->respondWithImage('products', $filename);
    }

    public function showAvatar(Request $request, string $filename)
    {
        abort_unless(
            $request->user()->avatar === 'avatars/'.$filename,
            404
        );

        return $this->respondWithImage('avatars', $filename);
    }

    private function respondWithImage(string $directory, string $filename)
    {
        abort_unless(
            preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9._-]*\.(?:jpg|jpeg|png|webp)\z/i', $filename) === 1,
            404
        );

        $path = Storage::disk('public')->path($directory.'/'.$filename);
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
