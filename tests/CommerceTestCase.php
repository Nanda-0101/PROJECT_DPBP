<?php

namespace Tests;

use App\Models\Booking;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dasar test untuk alur sewa: membuat skema SQLite in-memory minimal
 * (users, categories, products, bookings, order_items) beserta helper data.
 */
abstract class CommerceTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createCommerceTables();
    }

    protected function createCommerceTables(): void
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

        Schema::create('categories', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('icon', 50)->default('bi-box');
            $table->unsignedTinyInteger('sort_order')->default(0);
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedInteger('category_id');
            $table->string('name', 150);
            $table->string('brand', 80)->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('image', 500)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price_per_half_day', 12, 2)->default(0);
            $table->decimal('price_per_day', 12, 2)->default(0);
            $table->decimal('price_sale', 12, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->string('status', 30)->default('Aktif');
            $table->timestamps();
        });

        Schema::create('product_specs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('spec_text', 180);
            $table->unsignedTinyInteger('sort_order')->default(0);
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
            $table->string('payment_method', 30);
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

    protected function createCategory(string $name): Category
    {
        return Category::create(['name' => $name, 'icon' => 'bi-box', 'sort_order' => 1]);
    }

    protected function createProduct(array $overrides = []): Product
    {
        if (!isset($overrides['category_id'])) {
            $overrides['category_id'] = (Category::first() ?? $this->createCategory('Kamera'))->id;
        }

        return Product::create(array_merge([
            'seller_id' => null,
            'name' => 'Kamera Contoh',
            'price_per_half_day' => 30000,
            'price_per_day' => 50000,
            'price_sale' => 0,
            'stock' => 3,
            'status' => 'Aktif',
        ], $overrides));
    }

    protected function createUser(array $overrides = []): User
    {
        static $n = 0;
        $n++;

        return User::create(array_merge([
            'name' => "Pengguna {$n}",
            'email' => "user{$n}@example.test",
            'password' => 'unused',
            'phone' => '081234567890',
            'role' => 'user',
            'status' => 'Aktif',
        ], $overrides));
    }

    /** Booking sewa dengan satu item. */
    protected function createRental(User $user, Product $product, string $date, int $qty = 1, string $status = 'Menunggu Konfirmasi'): Booking
    {
        $booking = Booking::create([
            'code' => Booking::generateCode('BKG'),
            'user_id' => $user->id,
            'type' => 'Sewa',
            'start_date' => $date,
            'end_date' => $date,
            'duration_label' => '1 Hari',
            'payment_type' => 'DP',
            'payment_method' => 'Cash',
            'guarantee' => 'KTP',
            'total_amount' => $product->price_per_day * $qty,
            'paid_amount' => 0,
            'status' => $status,
        ]);
        OrderItem::create([
            'booking_id' => $booking->id,
            'product_id' => $product->id,
            'product_name_snap' => $product->name,
            'price_snap' => $product->price_per_day,
            'qty' => $qty,
            'subtotal' => $product->price_per_day * $qty,
        ]);

        return $booking;
    }

    /** Payload checkout valid; timpa sesuai kebutuhan test. */
    protected function checkoutPayload(array $selected, array $overrides = []): array
    {
        $date = now()->addDays(3)->toDateString();

        return array_merge([
            'selected' => $selected,
            'renter_name' => 'Penyewa Baru',
            'renter_phone' => '081298765432',
            'start_date' => $date,
            'end_date' => $date,
            'duration_type' => 'full',
            'payment_type' => 'DP',
            'payment_method' => 'Cash',
            'guarantee' => 'KTP',
        ], $overrides);
    }
}
