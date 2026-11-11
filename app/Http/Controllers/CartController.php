<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\Product;
use App\Services\CartService;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function index(RecommendationService $recommendations)
    {
        $lines = $this->cart->lines();

        return view('cart', [
            'lines'           => $lines,
            'recommendations' => $recommendations->forCart(4),
        ]);
    }

    /**
     * Tambah ke keranjang.
     *  - rent_now=1   → hanya item ini dipilih, langsung ke checkout ("Sewa Sekarang")
     *  - from=checkout → item ikut dipilih (dipakai rekomendasi di halaman checkout)
     */
    public function add(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'qty'     => 'nullable|integer|min:1|max:99',
            'rent_now' => 'nullable|boolean',
            'from'    => 'nullable|in:checkout',
        ]);

        $product = $this->rentableProduct($product);
        if (!$product) {
            return $this->fail($request, 'Alat ini sedang tidak tersedia untuk disewa.');
        }

        $before = $this->cart->raw()[$product->id] ?? 0;
        $qty = $this->cart->add($product, (int) ($data['qty'] ?? 1));
        $atLimit = $before > 0 && $qty === $before;

        $message = $atLimit
            ? "Jumlah {$product->name} sudah mencapai stok tersedia ({$product->stock})."
            : "{$product->name} masuk ke keranjang.";

        if ($request->boolean('rent_now')) {
            $this->cart->select([$product->id]);

            return $request->expectsJson()
                ? response()->json(['message' => $message, 'redirect' => route('booking')])
                : redirect()->route('booking');
        }

        if (($data['from'] ?? null) === 'checkout') {
            $this->cart->addToSelection($product->id);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message'    => $message,
                'at_limit'   => $atLimit,
                'cart_count' => $this->cart->count(),
                'item'       => $this->itemPayload($product, $qty),
            ]);
        }

        return back()->with($atLimit ? 'error' : 'success', $message);
    }

    /** Ubah jumlah (tombol + / −). qty < 1 menghapus item. */
    public function update(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['qty' => 'required|integer|min:0|max:99']);

        if (!array_key_exists($product->id, $this->cart->raw())) {
            return $this->fail($request, 'Item tidak ada di keranjang.', 404);
        }

        $requested = (int) $data['qty'];
        $qty = $this->cart->setQuantity($product, $requested);
        $atLimit = $qty > 0 && $qty < $requested;
        $message = $atLimit ? "Stok {$product->name} hanya {$qty} unit." : null;

        if ($request->expectsJson()) {
            return response()->json([
                'qty'          => $qty,
                'removed'      => $qty === 0,
                'at_limit'     => $atLimit,
                'message'      => $message,
                'cart_count'   => $this->cart->count(),
                'line_per_day' => (float) $product->price_per_day * $qty,
            ]);
        }

        return back()->with($atLimit ? 'error' : 'success', $message ?? 'Keranjang diperbarui.');
    }

    public function remove(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $this->cart->remove($product->id);

        if ($request->expectsJson()) {
            return response()->json([
                'message'    => "{$product->name} dihapus dari keranjang.",
                'cart_count' => $this->cart->count(),
            ]);
        }

        return back()->with('success', "{$product->name} dihapus dari keranjang.");
    }

    /** Dari halaman keranjang: simpan item yang dicentang lalu lanjut ke checkout. */
    public function checkout(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'selected'   => 'required|array|min:1',
            'selected.*' => 'integer',
        ], [
            'selected.required' => 'Pilih minimal satu alat untuk di-checkout.',
            'selected.min'      => 'Pilih minimal satu alat untuk di-checkout.',
        ]);

        $this->cart->select($data['selected']);

        if ($this->cart->selectedLines()->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Pilih minimal satu alat untuk di-checkout.');
        }

        return redirect()->route('booking');
    }

    private function rentableProduct(Product $product): ?Product
    {
        return Product::active()->rentable()->with('category')->find($product->id);
    }

    private function itemPayload(Product $product, int $qty): array
    {
        return [
            'id'         => $product->id,
            'name'       => $product->name,
            'category'   => $product->category?->name ?? '-',
            'image'      => ImageHelper::url($product->image),
            'icon'       => $product->icon ?: 'bi-box-seam',
            'price_day'  => (float) $product->price_per_day,
            'price_half' => (float) $product->price_per_half_day,
            'stock'      => (int) $product->stock,
            'qty'        => $qty,
        ];
    }

    private function fail(Request $request, string $message, int $status = 422): JsonResponse|RedirectResponse
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message], $status)
            : back()->with('error', $message);
    }
}
