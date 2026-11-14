<?php

namespace Tests\Feature;

use App\Models\Booking;
use Tests\CommerceTestCase;

class InventoryFlowsTest extends CommerceTestCase
{
    public function test_generated_booking_codes_have_a_collision_check_and_larger_range(): void
    {
        $code = Booking::generateCode('BKG');

        $this->assertMatchesRegularExpression('/^BKG-\d{8}$/', $code);

        Booking::create([
            'code' => $code,
            'user_id' => 1,
            'type' => 'Sewa',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'payment_type' => 'Bayar Penuh',
            'payment_method' => 'BCA',
        ]);

        $this->assertNotSame($code, Booking::generateCode('BKG'));
    }

    public function test_rental_is_rejected_when_all_units_are_reserved_for_the_dates(): void
    {
        $product = $this->createProduct(['stock' => 1]);
        $date = now()->addDays(2)->toDateString();
        $this->createRental($this->createUser(), $product, $date);

        $this->withSession(['cart' => [$product->id => 1]])
            ->from('/booking')
            ->post(route('booking.store'), $this->checkoutPayload([$product->id], [
                'start_date' => $date, 'end_date' => $date,
            ]))->assertRedirect('/booking')->assertSessionHas('error');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_rental_quantity_counts_against_remaining_units(): void
    {
        $product = $this->createProduct(['stock' => 3]);
        $date = now()->addDays(2)->toDateString();
        $this->createRental($this->createUser(), $product, $date, qty: 2); // sisa 1 unit

        $this->withSession(['cart' => [$product->id => 2]])
            ->from('/booking')
            ->post(route('booking.store'), $this->checkoutPayload([$product->id], [
                'start_date' => $date, 'end_date' => $date,
            ]))->assertRedirect('/booking')->assertSessionHas('error');
        $this->assertDatabaseCount('bookings', 1);

        $this->withSession(['cart' => [$product->id => 1]])
            ->post(route('booking.store'), $this->checkoutPayload([$product->id], [
                'start_date' => $date, 'end_date' => $date,
            ]))->assertRedirect(route('dashboard'));
        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_renting_does_not_reduce_stock_so_other_dates_stay_available(): void
    {
        $product = $this->createProduct(['stock' => 1]);

        $this->withSession(['cart' => [$product->id => 1]])
            ->post(route('booking.store'), $this->checkoutPayload([$product->id]))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1, 'status' => 'Aktif']);
    }
}
