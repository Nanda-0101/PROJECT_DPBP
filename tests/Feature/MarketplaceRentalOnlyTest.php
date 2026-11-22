<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\CommerceTestCase;

class MarketplaceRentalOnlyTest extends CommerceTestCase
{
    public function test_marketplace_uses_rental_wording_only(): void
    {
        $this->createProduct(['name' => 'Kamera Sewa', 'price_per_day' => 250000, 'price_sale' => 9000000]);

        $this->get(route('marketplace'))
            ->assertOk()
            ->assertSee('Kamera Sewa')
            ->assertSee('Sewa peralatan kreatif')
            ->assertSee('Sewakan Barang Saya')
            ->assertSee('/hari')
            ->assertDontSee('Beli')
            ->assertDontSee('Jual')
            ->assertDontSee('harga jual')
            ->assertDontSee('Pembelian');
    }

    public function test_floating_cart_bar_shows_item_count_on_marketplace_but_not_on_cart_page(): void
    {
        $product = $this->createProduct();

        $this->withSession(['cart' => [$product->id => 2]])
            ->get(route('marketplace'))
            ->assertSee('id="cartBar"', false)
            ->assertSee('class="cart-bar show"', false);

        $this->get(route('cart.index'))->assertDontSee('id="cartBar"', false);
    }

    public function test_each_product_card_is_a_single_card_with_equal_cart_and_rent_buttons(): void
    {
        $this->createProduct(['name' => 'Kartu Tunggal']);

        $html = $this->get(route('marketplace'))->getContent();

        $this->assertSame(1, substr_count($html, 'class="product-card'));
        $this->assertStringContainsString('Keranjang', $html);
        $this->assertStringContainsString('<span class="btn-text">Sewa</span>', $html);
    }

    public function test_sale_only_products_are_not_listed(): void
    {
        $this->createProduct(['name' => 'Barang Jual Saja', 'price_per_day' => 0, 'price_sale' => 1000000]);

        $this->get(route('marketplace'))->assertOk()->assertDontSee('Barang Jual Saja');
    }

    public function test_product_detail_has_no_sale_price_and_provides_cart_url(): void
    {
        $product = $this->createProduct(['price_sale' => 5000000]);

        $this->getJson(route('marketplace.product.show', $product))
            ->assertOk()
            ->assertJsonMissingPath('price_sale')
            ->assertJsonPath('cart_add_url', route('cart.add', $product));
    }

    public function test_buy_endpoint_no_longer_exists(): void
    {
        $product = $this->createProduct();

        $this->post('/marketplace/buy/'.$product->id)->assertNotFound();
    }

    public function test_listing_an_item_requires_only_a_rental_price(): void
    {
        $user = $this->createUser(['role' => 'mitra']);

        $this->actingAs($user)->from('/marketplace')
            ->post(route('marketplace.rent-out'), ['name' => 'Alat', 'category' => 'Kamera'])
            ->assertSessionHasErrors(['price_rent', 'photos']);
    }

    public function test_mitra_listing_is_active_and_visible_immediately(): void
    {
        Storage::fake('public');
        $user = $this->createUser(['role' => 'mitra']);

        $this->actingAs($user)->post(route('marketplace.rent-out'), [
            'name' => 'Alat Langsung Tayang',
            'category' => 'Kamera',
            'price_rent' => 100000,
            'stock' => 1,
            'photos' => [UploadedFile::fake()->createWithContent(
                'alat.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lS8AAAAASUVORK5CYII=')
            )],
        ])->assertRedirect(route('marketplace'))
            ->assertSessionHas('success', 'Barang berhasil ditayangkan di marketplace dan siap disewa.');

        $this->assertDatabaseHas('products', [
            'name' => 'Alat Langsung Tayang',
            'seller_id' => $user->id,
            'status' => 'Aktif',
        ]);
        $this->get(route('marketplace'))->assertSee('Alat Langsung Tayang');
    }

    public function test_mitra_can_remove_all_stock_while_preserving_rental_history(): void
    {
        $mitra = $this->createUser(['role' => 'mitra']);
        $renter = $this->createUser();
        $product = $this->createProduct(['seller_id' => $mitra->id, 'name' => 'Listing untuk Dihapus']);
        $booking = $this->createRental($renter, $product, now()->addDays(3)->toDateString());

        $this->actingAs($mitra)
            ->get(route('marketplace'))
            ->assertSee('Jumlah stok dihapus')
            ->assertSee('Hapus stok')
            ->assertSee('Listing untuk Dihapus');

        $this->delete(route('marketplace.remove', $product), ['stock_to_remove' => 3])
            ->assertRedirect(route('marketplace'))
            ->assertSessionHas('success', 'Stok sebanyak 3 unit berhasil dihapus. Sisa stok: 0 unit.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'Habis',
            'stock' => 0,
        ]);
        $this->assertDatabaseHas('order_items', [
            'booking_id' => $booking->id,
            'product_id' => $product->id,
        ]);
        $this->get(route('marketplace'))->assertDontSee('Listing untuk Dihapus');
    }

    public function test_mitra_can_remove_part_of_the_stock_without_hiding_the_listing(): void
    {
        $mitra = $this->createUser(['role' => 'mitra']);
        $product = $this->createProduct(['seller_id' => $mitra->id, 'stock' => 5]);

        $this->actingAs($mitra)
            ->delete(route('marketplace.remove', $product), ['stock_to_remove' => 2])
            ->assertRedirect(route('marketplace'))
            ->assertSessionHas('success', 'Stok sebanyak 2 unit berhasil dihapus. Sisa stok: 3 unit.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'Aktif',
            'stock' => 3,
        ]);
        $this->get(route('marketplace'))->assertSee('Kamera Contoh')->assertSee('Stok 3');
    }

    public function test_mitra_cannot_remove_more_stock_than_is_available(): void
    {
        $mitra = $this->createUser(['role' => 'mitra']);
        $product = $this->createProduct(['seller_id' => $mitra->id, 'stock' => 2]);

        $this->actingAs($mitra)
            ->delete(route('marketplace.remove', $product), ['stock_to_remove' => 3])
            ->assertSessionHasErrors('stock_to_remove');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'Aktif',
            'stock' => 2,
        ]);
    }

    public function test_only_the_listing_mitra_can_remove_their_product(): void
    {
        $owner = $this->createUser(['role' => 'mitra']);
        $otherMitra = $this->createUser(['role' => 'mitra']);
        $product = $this->createProduct(['seller_id' => $owner->id]);

        $this->actingAs($otherMitra)
            ->delete(route('marketplace.remove', $product))
            ->assertForbidden();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'Aktif',
            'stock' => 3,
        ]);
    }

    public function test_only_mitra_can_open_listing_form_or_submit_a_listing(): void
    {
        $user = $this->createUser(['role' => 'user']);
        $mitra = $this->createUser(['role' => 'mitra']);

        $this->from(route('marketplace'))->post(route('marketplace.rent-out'))
            ->assertRedirect(route('marketplace'))
            ->assertSessionHas('error', 'Hanya mitra yang dapat menyewakan barang.');

        $this->actingAs($user)->get(route('marketplace'))
            ->assertOk()
            ->assertSee('Hanya mitra yang dapat menyewakan barang.')
            ->assertDontSee('id="modalSewakan"', false);

        $this->from(route('marketplace'))->actingAs($user)
            ->post(route('marketplace.rent-out'))
            ->assertRedirect(route('marketplace'))
            ->assertSessionHas('error', 'Hanya mitra yang dapat menyewakan barang.');

        $this->actingAs($mitra)->get(route('marketplace'))
            ->assertOk()
            ->assertSee('id="modalSewakan"', false);
    }

    public function test_home_page_and_footer_do_not_mention_buying(): void
    {
        $this->createProduct(['is_trending' => true]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('jual-beli')
            ->assertDontSee('Sewa &amp; beli', false)
            ->assertDontSee('Jual Beli');
    }
}
