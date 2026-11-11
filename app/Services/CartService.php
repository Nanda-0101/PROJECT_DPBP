<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Keranjang sewa berbasis session (jalan untuk tamu maupun akun login).
 *
 * Session menyimpan:
 *   cart           => [product_id => qty]
 *   cart_selection => [product_id, ...]   item yang dipilih untuk checkout
 *   viewed_products=> [product_id => category_id]  (urut terbaru di akhir)
 */
class CartService
{
    private const CART = 'cart';
    private const SELECTION = 'cart_selection';
    private const VIEWED = 'viewed_products';
    private const VIEWED_LIMIT = 30;

    public function __construct(private Session $session)
    {
    }

    // ==== Baca ====

    /** Jumlah baris produk (bukan total qty) — dipakai badge navbar, tanpa query DB. */
    public function count(): int
    {
        return count($this->raw());
    }

    /** [product_id => qty] mentah dari session. */
    public function raw(): array
    {
        return array_map('intval', (array) $this->session->get(self::CART, []));
    }

    /**
     * Baris keranjang dengan data produk terbaru. Produk yang sudah tidak
     * aktif / tidak bisa disewa otomatis dibuang, qty dijepit ke stok.
     *
     * @return Collection<int, array{product: Product, qty: int, line_per_day: float}>
     */
    public function lines(): Collection
    {
        $raw = $this->raw();
        if (!$raw) {
            return collect();
        }

        $products = Product::active()->rentable()
            ->with(['category', 'seller'])
            ->whereIn('id', array_keys($raw))
            ->get()
            ->keyBy('id');

        $clean = [];
        $lines = collect();
        foreach ($raw as $id => $qty) {
            $product = $products->get($id);
            if (!$product) {
                continue;
            }
            $qty = max(1, min($qty, (int) $product->stock));
            $clean[$id] = $qty;
            $lines->push([
                'product'      => $product,
                'qty'          => $qty,
                'line_per_day' => (float) $product->price_per_day * $qty,
            ]);
        }

        if ($clean !== $raw) {
            $this->session->put(self::CART, $clean);
        }
        $this->session->put(
            self::SELECTION,
            array_values(array_intersect((array) $this->session->get(self::SELECTION, []), array_keys($clean)))
        );

        return $lines;
    }

    // ==== Ubah ====

    /** Tambah produk. Mengembalikan qty akhir di keranjang. */
    public function add(Product $product, int $qty = 1): int
    {
        $cart = $this->raw();
        $current = $cart[$product->id] ?? 0;
        $cart[$product->id] = $this->clamp($product, $current + max(1, $qty));
        $this->session->put(self::CART, $cart);

        return $cart[$product->id];
    }

    /** Atur qty. qty < 1 menghapus item. Mengembalikan qty akhir (0 = terhapus). */
    public function setQuantity(Product $product, int $qty): int
    {
        if ($qty < 1) {
            $this->remove($product->id);

            return 0;
        }

        $cart = $this->raw();
        if (!isset($cart[$product->id])) {
            return 0;
        }
        $cart[$product->id] = $this->clamp($product, $qty);
        $this->session->put(self::CART, $cart);

        return $cart[$product->id];
    }

    public function remove(int $productId): void
    {
        $cart = $this->raw();
        unset($cart[$productId]);
        $this->session->put(self::CART, $cart);
        $this->session->put(
            self::SELECTION,
            array_values(array_diff((array) $this->session->get(self::SELECTION, []), [$productId]))
        );
    }

    public function removeMany(array $productIds): void
    {
        foreach ($productIds as $id) {
            $this->remove((int) $id);
        }
    }

    // ==== Pilihan checkout ====

    public function select(array $productIds): void
    {
        $valid = array_keys($this->raw());
        $this->session->put(
            self::SELECTION,
            array_values(array_intersect(array_map('intval', $productIds), $valid))
        );
    }

    public function addToSelection(int $productId): void
    {
        if (isset($this->raw()[$productId])) {
            $selection = (array) $this->session->get(self::SELECTION, []);
            $selection[] = $productId;
            $this->session->put(self::SELECTION, array_values(array_unique($selection)));
        }
    }

    /** Baris yang dipilih untuk checkout; kosong → seluruh isi keranjang. */
    public function selectedLines(): Collection
    {
        $lines = $this->lines();
        $selection = (array) $this->session->get(self::SELECTION, []);
        if (!$selection) {
            return $lines->values();
        }

        return $lines->filter(fn ($line) => in_array($line['product']->id, $selection, true))->values();
    }

    // ==== Produk yang dilihat (sinyal rekomendasi) ====

    public function recordView(Product $product): void
    {
        if (!$product->category_id) {
            return;
        }
        $viewed = (array) $this->session->get(self::VIEWED, []);
        unset($viewed[$product->id]);
        $viewed[$product->id] = (int) $product->category_id;
        $this->session->put(self::VIEWED, array_slice($viewed, -self::VIEWED_LIMIT, null, true));
    }

    /** [product_id => category_id] */
    public function viewed(): array
    {
        return (array) $this->session->get(self::VIEWED, []);
    }

    private function clamp(Product $product, int $qty): int
    {
        return max(1, min($qty, max(1, (int) $product->stock)));
    }
}
