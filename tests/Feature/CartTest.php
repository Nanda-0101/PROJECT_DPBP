<?php

namespace Tests\Feature;

use Tests\CommerceTestCase;

class CartTest extends CommerceTestCase
{
    public function test_user_can_add_multiple_different_products_to_cart(): void
    {
        $camera = $this->createProduct(['name' => 'Sony A7III']);
        $lens = $this->createProduct(['name' => 'Sigma 24-70']);
        $light = $this->createProduct(['name' => 'Aputure 120D']);
        $user = $this->createUser();

        foreach ([$camera, $lens, $light] as $product) {
            $this->actingAs($user)->postJson(route('cart.add', $product))->assertOk();
        }

        $this->assertSame(
            [$camera->id => 1, $lens->id => 1, $light->id => 1],
            session('cart')
        );
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Sony A7III')
            ->assertSee('Sigma 24-70')
            ->assertSee('Aputure 120D');
    }

    public function test_guest_cannot_add_a_rental_and_gets_role_message(): void
    {
        $product = $this->createProduct();

        $this->postJson(route('cart.add', $product))
            ->assertForbidden()
            ->assertJsonPath('message', 'Hanya akun user atau mitra yang dapat menyewa alat.');
        $this->assertSame([], session('cart', []));
    }

    public function test_mitra_can_add_another_mitras_rental(): void
    {
        $owner = $this->createUser(['role' => 'mitra']);
        $renter = $this->createUser(['role' => 'mitra']);
        $product = $this->createProduct(['seller_id' => $owner->id]);

        $this->actingAs($renter)->postJson(route('cart.add', $product))
            ->assertOk()
            ->assertJsonPath('item.id', $product->id);

        $this->assertSame([$product->id => 1], session('cart'));
    }

    public function test_mitra_cannot_add_own_rental(): void
    {
        $mitra = $this->createUser(['role' => 'mitra']);
        $product = $this->createProduct(['seller_id' => $mitra->id]);

        $this->actingAs($mitra)->postJson(route('cart.add', $product))
            ->assertForbidden()
            ->assertJsonPath('message', 'Mitra tidak dapat menyewa barang milik sendiri.');
        $this->assertSame([], session('cart', []));
    }

    public function test_marketplace_detail_disables_renting_own_products_for_mitra(): void
    {
        $mitra = $this->createUser(['role' => 'mitra']);
        $otherMitra = $this->createUser(['role' => 'mitra']);
        $ownProduct = $this->createProduct(['seller_id' => $mitra->id]);
        $otherProduct = $this->createProduct(['seller_id' => $otherMitra->id]);

        $this->actingAs($mitra)->getJson(route('marketplace.product.show', $ownProduct))
            ->assertOk()
            ->assertJsonPath('can_rent', false);
        $this->actingAs($mitra)->getJson(route('marketplace.product.show', $otherProduct))
            ->assertOk()
            ->assertJsonPath('can_rent', true);
    }

    public function test_adding_same_product_again_increases_quantity_up_to_stock(): void
    {
        $product = $this->createProduct(['stock' => 2]);
        $user = $this->createUser();

        $this->actingAs($user)->postJson(route('cart.add', $product))->assertOk()->assertJsonPath('item.qty', 1);
        $this->actingAs($user)->postJson(route('cart.add', $product))->assertOk()->assertJsonPath('item.qty', 2);
        $this->actingAs($user)->postJson(route('cart.add', $product))
            ->assertOk()
            ->assertJsonPath('item.qty', 2)
            ->assertJsonPath('at_limit', true);
    }

    public function test_plus_and_minus_change_quantity_and_zero_removes_item(): void
    {
        $product = $this->createProduct(['stock' => 3, 'price_per_day' => 100000]);

        $this->withSession(['cart' => [$product->id => 1]])
            ->patchJson(route('cart.update', $product), ['qty' => 2])
            ->assertOk()
            ->assertJsonPath('qty', 2)
            ->assertJsonPath('line_per_day', 200000);

        $this->patchJson(route('cart.update', $product), ['qty' => 9])
            ->assertOk()
            ->assertJsonPath('qty', 3)
            ->assertJsonPath('at_limit', true);

        $this->patchJson(route('cart.update', $product), ['qty' => 0])
            ->assertOk()
            ->assertJsonPath('removed', true)
            ->assertJsonPath('cart_count', 0);
        $this->assertSame([], session('cart'));
    }

    public function test_item_can_be_removed_from_cart(): void
    {
        $keep = $this->createProduct(['name' => 'Tetap']);
        $drop = $this->createProduct(['name' => 'Dibuang']);

        $this->withSession(['cart' => [$keep->id => 1, $drop->id => 2]])
            ->deleteJson(route('cart.remove', $drop))
            ->assertOk()
            ->assertJsonPath('cart_count', 1);

        $this->assertSame([$keep->id => 1], session('cart'));
    }

    public function test_unavailable_products_cannot_be_added_and_stale_items_are_dropped(): void
    {
        $inactive = $this->createProduct(['status' => 'Habis']);
        $saleOnly = $this->createProduct(['price_per_day' => 0, 'price_sale' => 100000]);
        $user = $this->createUser();

        $this->actingAs($user)->postJson(route('cart.add', $inactive))->assertStatus(422);
        $this->actingAs($user)->postJson(route('cart.add', $saleOnly))->assertStatus(422);

        $this->withSession(['cart' => [$inactive->id => 1]])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Keranjang sewamu masih kosong');
        $this->assertSame([], session('cart'));
    }

    public function test_cart_checkout_requires_a_selection_and_forwards_only_selected_items(): void
    {
        $a = $this->createProduct(['name' => 'Alat A']);
        $b = $this->createProduct(['name' => 'Alat B']);
        $user = $this->createUser();

        $this->actingAs($user)->withSession(['cart' => [$a->id => 1, $b->id => 1]])
            ->from('/keranjang')
            ->post(route('cart.checkout'), [])
            ->assertRedirect('/keranjang')
            ->assertSessionHasErrors('selected');

        $this->actingAs($user)->post(route('cart.checkout'), ['selected' => [$b->id]])
            ->assertRedirect(route('booking'));

        $this->actingAs($user)->get(route('booking'))
            ->assertOk()
            ->assertSee('Alat B')
            ->assertDontSee('Alat A');
    }

    public function test_rent_now_selects_only_that_product_and_goes_to_checkout(): void
    {
        $other = $this->createProduct(['name' => 'Sudah di keranjang']);
        $target = $this->createProduct(['name' => 'Sewa langsung']);
        $user = $this->createUser();

        $this->actingAs($user)->withSession(['cart' => [$other->id => 1]])
            ->post(route('cart.add', $target), ['rent_now' => 1])
            ->assertRedirect(route('booking'));

        $this->actingAs($user)->get(route('booking'))->assertSee('Sewa langsung')->assertDontSee('Sudah di keranjang');
    }

    public function test_checkout_redirects_to_cart_when_empty(): void
    {
        $this->get(route('booking'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Hanya akun user atau mitra yang dapat menyewa alat.');
    }

    public function test_navbar_shows_cart_badge_count(): void
    {
        $a = $this->createProduct();
        $b = $this->createProduct();

        $this->withSession(['cart' => [$a->id => 2, $b->id => 1]])
            ->get(route('cart.index'))
            ->assertSee('id="cartBadge"', false)
            ->assertSee('>2</span>', false);
    }
}
