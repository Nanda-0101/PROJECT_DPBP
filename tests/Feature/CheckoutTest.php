<?php

namespace Tests\Feature;

use App\Models\Booking;
use Tests\CommerceTestCase;

class CheckoutTest extends CommerceTestCase
{
    public function test_checkout_creates_one_booking_with_many_items_and_quantities(): void
    {
        $camera = $this->createProduct(['name' => 'Kamera', 'price_per_day' => 480000, 'stock' => 2]);
        $lens = $this->createProduct(['name' => 'Lensa', 'price_per_day' => 260000, 'stock' => 5]);
        $light = $this->createProduct(['name' => 'Lighting', 'price_per_day' => 160000, 'stock' => 8]);
        $user = $this->createUser();

        $this->actingAs($user)->withSession(['cart' => [$camera->id => 1, $lens->id => 2, $light->id => 3]])
            ->post(route('booking.store'), $this->checkoutPayload(
                [$camera->id, $lens->id, $light->id],
                ['payment_method' => 'BCA']
            ))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('payment_summary');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('order_items', 3);

        // 480.000 + 2×260.000 + 3×160.000 = 1.480.000 ; DP 30% = 444.000
        $this->assertDatabaseHas('bookings', [
            'type' => 'Sewa',
            'payment_method' => 'BCA',
            'total_amount' => 1480000,
            'paid_amount' => 444000,
        ]);
        $this->assertDatabaseHas('order_items', ['product_id' => $lens->id, 'qty' => 2, 'subtotal' => 520000]);
        $this->assertDatabaseHas('order_items', ['product_id' => $light->id, 'qty' => 3, 'subtotal' => 480000]);
        $this->assertSame([], session('cart'), 'Item yang di-checkout keluar dari keranjang.');
    }

    public function test_multi_day_and_half_day_prices_are_multiplied_by_quantity(): void
    {
        $product = $this->createProduct(['price_per_day' => 100000, 'price_per_half_day' => 60000, 'stock' => 5]);
        $start = now()->addDays(5);
        $user = $this->createUser();

        $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])
            ->post(route('booking.store'), $this->checkoutPayload([$product->id], [
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addDays(2)->toDateString(), // 3 hari
                'payment_type' => 'Bayar Penuh',
            ]));
        $this->assertDatabaseHas('bookings', ['duration_label' => '3 Hari', 'total_amount' => 600000, 'paid_amount' => 600000]);

        $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])
            ->post(route('booking.store'), $this->checkoutPayload([$product->id], [
                'start_date' => $start->copy()->addDays(10)->toDateString(),
                'end_date' => $start->copy()->addDays(10)->toDateString(),
                'duration_type' => 'half',
            ]));
        $this->assertDatabaseHas('bookings', ['duration_label' => '12 Jam', 'total_amount' => 120000]);
    }

    public function test_cart_is_split_into_one_booking_per_owner(): void
    {
        $mitraA = $this->createUser(['role' => 'mitra']);
        $mitraB = $this->createUser(['role' => 'mitra']);
        $a1 = $this->createProduct(['seller_id' => $mitraA->id, 'price_per_day' => 100000]);
        $a2 = $this->createProduct(['seller_id' => $mitraA->id, 'price_per_day' => 50000]);
        $b1 = $this->createProduct(['seller_id' => $mitraB->id, 'price_per_day' => 70000]);
        $renter = $this->createUser();

        $this->actingAs($renter)->withSession(['cart' => [$a1->id => 1, $a2->id => 1, $b1->id => 1]])
            ->post(route('booking.store'), $this->checkoutPayload([$a1->id, $a2->id, $b1->id]))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('bookings', 2);
        $this->assertDatabaseHas('bookings', ['total_amount' => 150000]);
        $this->assertDatabaseHas('bookings', ['total_amount' => 70000]);
        $this->assertSame(3, \App\Models\OrderItem::count());
    }

    public function test_unselected_cart_items_stay_in_cart_after_checkout(): void
    {
        $a = $this->createProduct();
        $b = $this->createProduct();
        $user = $this->createUser();

        $this->actingAs($user)->withSession(['cart' => [$a->id => 1, $b->id => 2]])
            ->post(route('booking.store'), $this->checkoutPayload([$a->id]));

        $this->assertSame([$b->id => 2], session('cart'));
    }

    public function test_quantity_is_taken_from_the_server_cart_not_the_form(): void
    {
        $product = $this->createProduct(['price_per_day' => 100000, 'stock' => 5]);
        $user = $this->createUser();

        $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])
            ->post(route('booking.store'), $this->checkoutPayload([$product->id], ['qty' => 5]));

        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'qty' => 1]);
    }

    public function test_item_not_in_cart_is_rejected(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();

        $this->actingAs($user)->post(route('booking.store'), $this->checkoutPayload([$product->id]))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_one_unavailable_item_blocks_the_whole_checkout(): void
    {
        $ok = $this->createProduct(['stock' => 5]);
        $full = $this->createProduct(['stock' => 1]);
        $date = now()->addDays(3)->toDateString();
        $this->createRental($this->createUser(), $full, $date);
        $user = $this->createUser();

        $this->actingAs($user)->withSession(['cart' => [$ok->id => 1, $full->id => 1]])
            ->from('/booking')
            ->post(route('booking.store'), $this->checkoutPayload([$ok->id, $full->id]))
            ->assertRedirect('/booking')
            ->assertSessionHas('error');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertSame([$ok->id => 1, $full->id => 1], session('cart'));
    }

    /** @dataProvider paymentMethods */
    public function test_every_configured_payment_method_is_accepted(string $method): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();

        $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])
            ->post(route('booking.store'), $this->checkoutPayload([$product->id], ['payment_method' => $method]))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('bookings', ['payment_method' => $method]);
    }

    public static function paymentMethods(): array
    {
        return array_map(
            fn ($m) => [$m],
            ['Cash', 'QRIS', 'BCA', 'BRI', 'BNI', 'SeaBank', 'DANA', 'ShopeePay', 'GoPay']
        );
    }

    public function test_unknown_payment_method_is_rejected(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();

        $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])
            ->from('/booking')
            ->post(route('booking.store'), $this->checkoutPayload([$product->id], ['payment_method' => 'Bitcoin']))
            ->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_checkout_page_lists_all_payment_methods(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();

        $page = $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])->get(route('booking'))->assertOk();
        foreach (['Bank BCA', 'Bank BRI', 'Bank BNI', 'SeaBank', 'DANA', 'ShopeePay', 'GoPay', 'QRIS', 'Cash'] as $label) {
            $page->assertSee($label);
        }
    }

    public function test_dashboard_shows_all_items_and_payment_instructions_after_checkout(): void
    {
        $camera = $this->createProduct(['name' => 'Kamera Uji']);
        $lens = $this->createProduct(['name' => 'Lensa Uji']);
        $user = $this->createUser();

        $this->actingAs($user)
            ->withSession(['cart' => [$camera->id => 1, $lens->id => 2]])
            ->post(route('booking.store'), $this->checkoutPayload([$camera->id, $lens->id], ['payment_method' => 'DANA']));

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Kamera Uji ×1')
            ->assertSee('Lensa Uji ×2')
            ->assertSee('Selesaikan pembayaranmu')
            ->assertSee('Virtual Account DANA');
    }

    public function test_guest_checkout_is_rejected_with_role_message(): void
    {
        $product = $this->createProduct(['name' => 'Kamera Tamu']);

        $this->withSession(['cart' => [$product->id => 1]])
            ->post(route('booking.store'), $this->checkoutPayload([$product->id]))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Hanya akun user atau mitra yang dapat menyewa alat.');

        $this->assertSame([], session('guest_user_ids', []));
        $this->assertSame(0, Booking::count());
    }

    public function test_mitra_can_checkout_another_mitras_rental(): void
    {
        $owner = $this->createUser(['role' => 'mitra']);
        $renter = $this->createUser(['role' => 'mitra']);
        $product = $this->createProduct(['seller_id' => $owner->id]);

        $this->actingAs($renter)->withSession(['cart' => [$product->id => 1]])
            ->get(route('booking'))
            ->assertOk();

        $this->actingAs($renter)->withSession(['cart' => [$product->id => 1]])
            ->post(route('booking.store'), $this->checkoutPayload([$product->id]))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('bookings', ['user_id' => $renter->id]);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id]);
    }

    public function test_mitra_cannot_open_or_submit_checkout_for_own_rental(): void
    {
        $mitra = $this->createUser(['role' => 'mitra']);
        $product = $this->createProduct(['seller_id' => $mitra->id]);

        $this->actingAs($mitra)->withSession(['cart' => [$product->id => 1]])
            ->get(route('booking'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Mitra tidak dapat menyewa barang milik sendiri.');

        $this->actingAs($mitra)->withSession(['cart' => [$product->id => 1]])
            ->from('/booking')
            ->post(route('booking.store'), $this->checkoutPayload([$product->id]))
            ->assertRedirect('/booking')
            ->assertSessionHas('error', 'Mitra tidak dapat menyewa barang milik sendiri.');

        $this->assertSame(0, Booking::count());
    }

    public function test_mitra_cannot_submit_own_product_from_a_stale_cart(): void
    {
        $mitra = $this->createUser(['role' => 'mitra']);
        $product = $this->createProduct(['seller_id' => $mitra->id]);

        $this->actingAs($mitra)->withSession(['cart' => [$product->id => 1]])
            ->from('/booking')
            ->post(route('booking.store'), $this->checkoutPayload([$product->id]))
            ->assertRedirect('/booking')
            ->assertSessionHas('error', 'Mitra tidak dapat menyewa barang milik sendiri.');

        $this->assertSame(0, Booking::count());
    }
}
