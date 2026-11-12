<?php

namespace App\Support;

use App\Helpers\ImageHelper;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Daftar foto (berbagai sudut) untuk specsheet produk.
 *
 * Urutan: foto utama (products.image) → foto tambahan di tabel product_images
 * → foto "saudara" yang ditemukan otomatis di storage dengan pola nama
 *   {nama-foto-utama}-2.jpg, -3.jpg, ... (jpg/jpeg/png/webp).
 *
 * Pola nama itu memudahkan menambah sudut baru tanpa menyentuh database:
 * cukup letakkan sony-a7iii-2.jpg di samping sony-a7iii.jpg.
 */
class ProductGallery
{
    public const MAX_EXTRA = 6;

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * @return list<array{url: string, label: string}>
     */
    public static function for(Product $product): array
    {
        $images = [];
        $seen = [];

        $add = function (?string $path, ?string $label) use (&$images, &$seen): void {
            $url = ImageHelper::url($path);
            if (!$url || isset($seen[$path])) {
                return;
            }
            $seen[$path] = true;
            $images[] = [
                'url'   => $url,
                'label' => $label ?: (count($images) === 0 ? 'Tampak utama' : 'Sudut '.(count($images) + 1)),
            ];
        };

        $add($product->image, 'Tampak utama');

        foreach (self::stored($product) as $row) {
            $add($row->path, $row->label);
        }

        foreach (self::discoverSiblings($product->image) as $path) {
            $add($path, null);
        }

        return $images;
    }

    /** Simpan foto tambahan (path relatif di disk public) untuk produk. */
    public static function storeExtras(Product $product, array $paths): void
    {
        if (!self::tableExists()) {
            return;
        }

        foreach (array_values($paths) as $i => $path) {
            $product->images()->create([
                'path'       => $path,
                'label'      => 'Sudut '.($i + 2),
                'sort_order' => $i + 1,
            ]);
        }
    }

    /** @return iterable<ProductImage> */
    private static function stored(Product $product): iterable
    {
        // Tabel baru dari patch SQL; specsheet tetap jalan walau patch belum diimpor.
        return self::tableExists() ? $product->images : [];
    }

    private static function tableExists(): bool
    {
        return Schema::hasTable('product_images');
    }

    /** @return list<string> */
    private static function discoverSiblings(?string $mainPath): array
    {
        if (!$mainPath || preg_match('#^https?://#i', $mainPath)) {
            return [];
        }

        $mainPath = ltrim(str_replace('\\', '/', $mainPath), '/');
        $info = pathinfo($mainPath);
        $disk = Storage::disk('public');
        $found = [];

        for ($n = 2; $n <= self::MAX_EXTRA + 1; $n++) {
            foreach (self::EXTENSIONS as $ext) {
                $candidate = "{$info['dirname']}/{$info['filename']}-{$n}.{$ext}";
                if ($disk->exists($candidate)) {
                    $found[] = $candidate;
                    break;
                }
            }
        }

        return $found;
    }
}
