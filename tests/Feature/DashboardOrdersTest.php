<?php

namespace Tests\Feature;

use Tests\CommerceTestCase;

class DashboardOrdersTest extends CommerceTestCase
{
    public function test_admin_and_mitra_can_monitor_all_user_rentals(): void
    {
        $mitra = $this->createUser(['role' => 'mitra']);
        $otherMitra = $this->createUser(['role' => 'mitra']);
        $customer = $this->createUser(['name' => 'Penyewa']);
        $mitraProduct = $this->createProduct(['seller_id' => $mitra->id, 'name' => 'Kamera Mitra']);
        $otherProduct = $this->createProduct(['seller_id' => $otherMitra->id, 'name' => 'Lensa Mitra Lain']);
        $ownBooking = $this->createRental($customer, $mitraProduct, now()->addDays(3)->toDateString());
        $otherBooking = $this->createRental($customer, $otherProduct, now()->addDays(4)->toDateString());

        $this->actingAs($mitra)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pantau Pesanan')
            ->assertSee($ownBooking->code)
            ->assertSee($otherBooking->code)
            ->assertSee(route('dashboard.booking.status', [$ownBooking->id, 'Dikonfirmasi']))
            ->assertDontSee(route('dashboard.booking.status', [$otherBooking->id, 'Dikonfirmasi']))
            ->assertSee('Toko Saya')
            ->assertSee('Kamera Mitra');

        $admin = $this->createUser(['role' => 'admin']);
        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pantau Pesanan')
            ->assertSee($ownBooking->code)
            ->assertSee($otherBooking->code)
            ->assertDontSee(route('dashboard.booking.status', [$ownBooking->id, 'Dikonfirmasi']))
            ->assertDontSee(route('dashboard.booking.status', [$otherBooking->id, 'Dikonfirmasi']));

        $this->patch(route('dashboard.booking.status', [$ownBooking->id, 'Dikonfirmasi']))
            ->assertForbidden();
    }
}
