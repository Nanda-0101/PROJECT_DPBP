<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventoryFlowsTest extends TestCase
{
    public function test_generated_booking_codes_have_a_collision_check_and_larger_range(): void
    {
        $this->createCommerceTables();
        $code = Booking::generateCode('ORD');

        $this->assertMatchesRegularExpression('/^ORD-\d{8}$/', $code);

        Booking::create([
            'code' => $code,
            'user_id' => 1,
            'type' => 'Pembelian',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'payment_type' => 'Bayar Penuh',
            'payment_method' => 'Transfer',
        ]);

        $this->assertNotSame($code, Booking::generateCode('ORD'));
    }

    public function test_guest_purchase_depletes_stock_and_rejects_a_second_purchase(): void
    {
        $this->createCommerceTables();
        $product = $this->createProduct(['price_sale' => 100000, 'stock' => 1]);
        $payload = [
            'name' => 'Pembeli Tamu',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 1',
            'payment' => 'Transfer',
        ];

        $this->from('/marketplace')
            ->post(route('marketplace.buy', $product), $payload)
            ->assertRedirect(route('marketplace'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 0,
            'status' => 'Habis',
        ]);
        $this->assertDatabaseCount('bookings', 1);

        $this->from('/marketplace')
            ->post(route('marketplace.buy', $product), $payload)
            ->assertRedirect('/marketplace')
            ->assertSessionHas('error');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_purchase_does_not_overwrite_signed_in_user_profile(): void
    {
        $this->createCommerceTables();
        $buyer = User::create([
            'name' => 'Nama Akun',
            'email' => 'buyer@example.test',
            'password' => 'unused',
            'phone' => '081111111111',
            'role' => 'user',
            'status' => 'Aktif',
        ]);
        $product = $this->createProduct(['price_sale' => 100000, 'stock' => 2]);

        $this->actingAs($buyer)->post(route('marketplace.buy', $product), [
            'name' => 'Nama Penerima',
            'phone' => '082222222222',
            'address' => 'Jl. Contoh No. 1',
            'payment' => 'Transfer',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'id' => $buyer->id,
            'name' => 'Nama Akun',
            'phone' => '081111111111',
        ]);
    }

    public function test_rental_booking_is_rejected_when_all_units_are_reserved_for_the_dates(): void
    {
        $this->createCommerceTables();
        $product = $this->createProduct(['price_per_day' => 50000, 'stock' => 1]);
        $user = User::create([
            'name' => 'Penyewa Sebelumnya',
            'email' => 'previous@example.test',
            'password' => 'unused',
            'phone' => '081234567890',
            'role' => 'user',
            'status' => 'Aktif',
        ]);
        $date = now()->addDays(2)->toDateString();
        $booking = Booking::create([
            'code' => 'BKG-1001',
            'user_id' => $user->id,
            'type' => 'Sewa',
            'start_date' => $date,
            'end_date' => $date,
            'duration_label' => '1 Hari',
            'payment_type' => 'DP',
            'payment_method' => 'Cash',
            'guarantee' => 'KTP',
            'total_amount' => 50000,
            'paid_amount' => 15000,
            'status' => 'Menunggu Konfirmasi',
        ]);
        OrderItem::create([
            'booking_id' => $booking->id,
            'product_id' => $product->id,
            'product_name_snap' => $product->name,
            'price_snap' => 50000,
            'qty' => 1,
            'subtotal' => 50000,
        ]);

        $this->from('/booking')->post(route('booking.store'), [
            'product_id' => $product->id,
            'renter_name' => 'Penyewa Baru',
            'renter_phone' => '081298765432',
            'start_date' => $date,
            'end_date' => $date,
            'duration_type' => 'full',
            'payment_type' => 'DP',
            'payment_method' => 'Cash',
            'guarantee' => 'KTP',
        ])->assertRedirect('/booking')->assertSessionHas('error');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_rejected_purchase_restores_stock_exactly_once(): void
    {
        $this->createCommerceTables();
        $seller = User::create([
            'name' => 'Mitra',
            'email' => 'seller@example.test',
            'password' => 'unused',
            'role' => 'mitra',
            'status' => 'Terverifikasi',
        ]);
        $buyer = User::create([
            'name' => 'Pembeli',
            'email' => 'buyer@example.test',
            'password' => 'unused',
            'role' => 'user',
            'status' => 'Aktif',
        ]);
        $product = $this->createProduct([
            'seller_id' => $seller->id,
            'price_sale' => 100000,
            'stock' => 0,
            'status' => 'Habis',
        ]);
        $booking = Booking::create([
            'code' => 'ORD-1001',
            'user_id' => $buyer->id,
            'type' => 'Pembelian',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'duration_label' => '-',
            'payment_type' => 'Bayar Penuh',
            'payment_method' => 'Transfer',
            'total_amount' => 100000,
            'paid_amount' => 100000,
            'status' => 'Menunggu Konfirmasi',
        ]);
        OrderItem::create([
            'booking_id' => $booking->id,
            'product_id' => $product->id,
            'product_name_snap' => $product->name,
            'price_snap' => 100000,
            'qty' => 1,
            'subtotal' => 100000,
        ]);

        $this->actingAs($seller)
            ->from('/dashboard')
            ->patch(route('dashboard.booking.status', [$booking, 'Ditolak']))
            ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 1,
            'status' => 'Aktif',
        ]);

        $this->actingAs($seller)
            ->from('/dashboard')
            ->patch(route('dashboard.booking.status', [$booking, 'Ditolak']))
            ->assertRedirect('/dashboard')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
    }

    public function test_purchase_does_not_reserve_remaining_rental_stock(): void
    {
        $this->createCommerceTables();
        $product = $this->createProduct([
            'price_per_day' => 50000,
            'price_sale' => 100000,
            'stock' => 2,
        ]);

        $this->from('/marketplace')
            ->post(route('marketplace.buy', $product), [
                'name' => 'Pembeli',
                'phone' => '081234567890',
                'address' => 'Jl. Contoh No. 1',
                'payment' => 'Transfer',
            ])->assertRedirect(route('marketplace'));

        $date = now()->toDateString();
        $this->from('/booking')->post(route('booking.store'), [
            'product_id' => $product->id,
            'renter_name' => 'Penyewa',
            'renter_phone' => '081298765432',
            'start_date' => $date,
            'end_date' => $date,
            'duration_type' => 'full',
            'payment_type' => 'DP',
            'payment_method' => 'Cash',
            'guarantee' => 'KTP',
        ])->assertRedirect(route('booking'));

        $this->assertDatabaseCount('bookings', 2);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
    }

    private function createCommerceTables(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('role', 20)->default('user');
            $table->string('status', 40)->default('Aktif');
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->unsignedInteger('category_id');
            $table->string('name', 150);
            $table->decimal('price_per_half_day', 12, 2)->default(0);
            $table->decimal('price_per_day', 12, 2)->default(0);
            $table->decimal('price_sale', 12, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->string('status', 30)->default('Aktif');
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 20);
            $table->unsignedBigInteger('user_id');
            $table->string('type', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('duration_label', 30)->nullable();
            $table->string('payment_type', 20);
            $table->string('payment_method', 20);
            $table->string('guarantee', 50)->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status', 40)->default('Menunggu Konfirmasi');
            $table->string('note')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name_snap', 150);
            $table->decimal('price_snap', 12, 2);
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('subtotal', 12, 2);
        });
    }

    private function createProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'seller_id' => 99,
            'category_id' => 1,
            'name' => 'Kamera Contoh',
            'price_per_half_day' => 30000,
            'price_per_day' => 50000,
            'price_sale' => 0,
            'stock' => 1,
            'status' => 'Aktif',
        ], $overrides));
    }
}
