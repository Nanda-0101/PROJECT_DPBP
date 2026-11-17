<?php

namespace Tests\Feature;

use App\Services\RecommendationService;
use Tests\CommerceTestCase;

class RecommendationTest extends CommerceTestCase
{
    private function names($recommendations): array
    {
        return $recommendations->pluck('name')->all();
    }

    public function test_recommends_from_the_majority_category_in_the_cart(): void
    {
        $camera = $this->createCategory('Kamera');
        $light = $this->createCategory('Lighting');
        $inCart = $this->createProduct(['name' => 'Light di keranjang', 'category_id' => $light->id]);
        $this->createProduct(['name' => 'Light lain', 'category_id' => $light->id]);
        $this->createProduct(['name' => 'Kamera lain', 'category_id' => $camera->id]);

        $this->withSession(['cart' => [$inCart->id => 2]]);
        $result = app(RecommendationService::class)->forCart(5);

        $this->assertSame('Light lain', $result->first()->name);
        $this->assertSame('Sesuai isi keranjangmu', $result->first()->reason);
        $this->assertNotContains('Light di keranjang', $this->names($result));
    }

    public function test_mitra_recommendations_exclude_their_own_products(): void
    {
        $mitra = $this->createUser(['role' => 'mitra']);
        $this->createProduct(['name' => 'Barang sendiri', 'seller_id' => $mitra->id]);
        $this->createProduct(['name' => 'Barang CamRent']);

        $this->actingAs($mitra);
        $result = app(RecommendationService::class)->forCart(5);

        $this->assertNotContains('Barang sendiri', $this->names($result));
        $this->assertContains('Barang CamRent', $this->names($result));
    }

    public function test_most_rented_category_boosts_recommendations(): void
    {
        $camera = $this->createCategory('Kamera');
        $audio = $this->createCategory('Audio');
        $user = $this->createUser();
        $rented = $this->createProduct(['name' => 'Mic lama', 'category_id' => $audio->id]);
        $this->createRental($user, $rented, now()->subDays(5)->toDateString(), qty: 3, status: 'Selesai');
        $this->createProduct(['name' => 'Mic baru', 'category_id' => $audio->id]);
        $this->createProduct(['name' => 'Kamera baru', 'category_id' => $camera->id, 'rating' => 5.0]);

        $this->actingAs($user);
        $result = app(RecommendationService::class)->forCart(3);

        $this->assertSame('Mic baru', $result->first()->name);
        $this->assertSame('Sering kamu sewa', $result->first()->reason);
    }

    public function test_recently_viewed_category_is_a_signal(): void
    {
        $camera = $this->createCategory('Kamera');
        $tripod = $this->createCategory('Tripod');
        $seen = $this->createProduct(['name' => 'Tripod dilihat', 'category_id' => $tripod->id]);
        $this->createProduct(['name' => 'Tripod lain', 'category_id' => $tripod->id]);
        $this->createProduct(['name' => 'Kamera', 'category_id' => $camera->id, 'rating' => 5.0]);

        $this->getJson(route('marketplace.product.show', $seen))->assertOk();

        $result = app(RecommendationService::class)->forCart(3);
        $this->assertSame('Tripod lain', $result->first()->name);
        $this->assertSame('Baru kamu lihat', $result->first()->reason);
    }

    public function test_cancelled_and_rejected_rentals_do_not_count(): void
    {
        $camera = $this->createCategory('Kamera');
        $audio = $this->createCategory('Audio');
        $user = $this->createUser();
        $mic = $this->createProduct(['name' => 'Mic', 'category_id' => $audio->id]);
        $this->createRental($user, $mic, now()->toDateString(), qty: 4, status: 'Ditolak');
        $this->createProduct(['name' => 'Mic 2', 'category_id' => $audio->id]);
        $this->createProduct(['name' => 'Kamera top', 'category_id' => $camera->id, 'rating' => 5.0]);

        $this->actingAs($user);
        $result = app(RecommendationService::class)->forCart(2);
        $this->assertSame('Kamera top', $result->first()->name);
    }

    public function test_falls_back_to_trending_when_there_are_no_signals(): void
    {
        $this->createProduct(['name' => 'Biasa']);
        $this->createProduct(['name' => 'Trending', 'is_trending' => true]);

        $result = app(RecommendationService::class)->forCart(2);
        $this->assertSame('Trending', $result->first()->name);
        $this->assertSame('Lagi trending', $result->first()->reason);
    }

    public function test_checkout_and_cart_pages_render_recommendations(): void
    {
        $cat = $this->createCategory('Lensa');
        $inCart = $this->createProduct(['name' => 'Lensa A', 'category_id' => $cat->id]);
        $this->createProduct(['name' => 'Lensa B', 'category_id' => $cat->id]);

        $session = ['cart' => [$inCart->id => 1]];
        $this->withSession($session)->get(route('cart.index'))
            ->assertSee('Rekomendasi untuk kamu')->assertSee('Lensa B');
        $this->withSession($session)->get(route('booking'))
            ->assertSee('Rekomendasi untuk kamu')->assertSee('Lensa B');
    }

    public function test_recommending_from_checkout_adds_item_to_cart_and_selection(): void
    {
        $inCart = $this->createProduct(['name' => 'Kamera']);
        $rec = $this->createProduct(['name' => 'Lensa']);

        $this->withSession(['cart' => [$inCart->id => 1]])
            ->postJson(route('cart.add', $rec), ['from' => 'checkout'])
            ->assertOk()
            ->assertJsonPath('item.id', $rec->id);

        $this->assertSame([$inCart->id => 1, $rec->id => 1], session('cart'));
    }
}
