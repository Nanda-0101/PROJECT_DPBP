<?php

namespace Tests\Feature;

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
            ->assertSee('per hari')
            ->assertDontSee('Beli')
            ->assertDontSee('Jual')
            ->assertDontSee('harga jual')
            ->assertDontSee('Pembelian');
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
