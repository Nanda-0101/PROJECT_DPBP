<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Rekomendasi alat sewa berbasis kategori mayoritas dari tiga sinyal:
 *   1. isi keranjang          (kategori yang sedang dibutuhkan)
 *   2. riwayat paling sering disewa
 *   3. produk yang baru dilihat (specsheet dibuka)
 *
 * Tiap sinyal dinormalisasi ke 0..1 (kategori terbanyak = 1) supaya satu sinyal
 * tidak menenggelamkan yang lain, lalu dibobot. Bobot bisa diubah di WEIGHTS.
 */
class RecommendationService
{
    private const WEIGHTS = ['cart' => 0.45, 'rented' => 0.35, 'viewed' => 0.20];

    private const REASONS = [
        'cart'     => 'Sesuai isi keranjangmu',
        'rented'   => 'Sering kamu sewa',
        'viewed'   => 'Baru kamu lihat',
        'popular'  => 'Populer disewa',
        'trending' => 'Lagi trending',
    ];

    public function __construct(private CartService $cart)
    {
    }

    /**
     * @return Collection<int, Product>  tiap produk punya atribut `reason`
     */
    public function forCart(int $limit = 4): Collection
    {
        $cartLines = $this->cart->lines();
        $inCartIds = $cartLines->pluck('product.id')->all();

        $signals = [
            'cart'   => $this->normalize($this->cartCategories($cartLines)),
            'rented' => $this->normalize($this->rentedCategories()),
            'viewed' => $this->normalize($this->viewedCategories()),
        ];

        $categoryScores = [];
        $dominant = [];
        foreach ($signals as $signal => $values) {
            foreach ($values as $categoryId => $value) {
                $weighted = $value * self::WEIGHTS[$signal];
                $categoryScores[$categoryId] = ($categoryScores[$categoryId] ?? 0) + $weighted;
                if ($weighted > ($dominant[$categoryId]['value'] ?? 0)) {
                    $dominant[$categoryId] = ['signal' => $signal, 'value' => $weighted];
                }
            }
        }

        $candidateQuery = Product::active()->rentable()
            ->with(['category', 'seller'])
            ->whereNotIn('id', $inCartIds);
        if (Auth::user()?->isMitra()) {
            $candidateQuery->where(fn ($query) => $query
                ->whereNull('seller_id')
                ->orWhere('seller_id', '!=', Auth::id()));
        }
        $candidates = $candidateQuery->get();

        $ranked = $candidates->map(function (Product $product) use ($categoryScores, $dominant) {
            $categoryScore = $categoryScores[$product->category_id] ?? 0;
            $score = $categoryScore
                + ($product->is_trending ? 0.05 : 0)
                + ((float) $product->rating / 5) * 0.05;

            $product->setAttribute('rec_score', $score);
            $product->setAttribute('reason', $categoryScore > 0
                ? self::REASONS[$dominant[$product->category_id]['signal']]
                : ($product->is_trending ? self::REASONS['trending'] : self::REASONS['popular']));

            return $product;
        });

        // Skor sama (mis. belum ada sinyal) → rating tertinggi, lalu produk terbaru.
        return $ranked
            ->sortBy([['rec_score', 'desc'], ['rating', 'desc'], ['id', 'desc']])
            ->take($limit)
            ->values();
    }

    /** @return array<int,int> category_id => jumlah unit di keranjang */
    private function cartCategories(Collection $lines): array
    {
        $counts = [];
        foreach ($lines as $line) {
            $categoryId = $line['product']->category_id;
            $counts[$categoryId] = ($counts[$categoryId] ?? 0) + $line['qty'];
        }

        return $counts;
    }

    /** @return array<int,int> category_id => jumlah unit pernah disewa */
    private function rentedCategories(): array
    {
        $userIds = Auth::check()
            ? [Auth::id()]
            : (array) session('guest_user_ids', []);

        // Pengguna baru belum punya riwayat → pakai popularitas seluruh platform.
        $query = $this->rentalCounts();
        $own = $userIds ? (clone $query)->whereIn('bookings.user_id', $userIds)->pluck('total', 'category_id')->all() : [];

        return $own ?: $query->pluck('total', 'category_id')->all();
    }

    private function rentalCounts()
    {
        return DB::table('order_items')
            ->join('bookings', 'bookings.id', '=', 'order_items.booking_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('bookings.type', 'Sewa')
            ->whereNotIn('bookings.status', ['Ditolak', 'Batal'])
            ->selectRaw('products.category_id as category_id, SUM(order_items.qty) as total')
            ->groupBy('products.category_id');
    }

    /** @return array<int,int> category_id => jumlah produk dilihat */
    private function viewedCategories(): array
    {
        return array_count_values(array_values($this->cart->viewed()));
    }

    /** Skala 0..1 relatif terhadap kategori terbanyak. */
    private function normalize(array $counts): array
    {
        $max = $counts ? max($counts) : 0;
        if ($max <= 0) {
            return [];
        }

        return array_map(fn ($count) => $count / $max, $counts);
    }
}
