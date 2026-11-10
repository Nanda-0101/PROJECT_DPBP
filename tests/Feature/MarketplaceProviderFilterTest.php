<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MarketplaceProviderFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('role');
        });
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('price_per_half_day')->default(0);
            $table->unsignedInteger('price_per_day')->default(0);
            $table->unsignedInteger('price_sale')->default(0);
            $table->unsignedInteger('stock')->default(1);
            $table->decimal('rating', 3, 1)->nullable();
            $table->unsignedInteger('reviews_count')->default(0);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->string('status')->default('Aktif');
        });

        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Penyewa', 'role' => 'user'],
            ['id' => 2, 'name' => 'Mitra', 'role' => 'mitra'],
        ]);
        DB::table('categories')->insert(['id' => 1, 'name' => 'Kamera']);
        DB::table('products')->insert([
            ['id' => 1, 'seller_id' => 1, 'category_id' => 1, 'name' => 'Kamera Penyewa', 'price_per_day' => 100000, 'status' => 'Aktif'],
            ['id' => 2, 'seller_id' => 2, 'category_id' => 1, 'name' => 'Kamera Mitra', 'price_per_day' => 100000, 'status' => 'Aktif'],
            ['id' => 3, 'seller_id' => null, 'category_id' => 1, 'name' => 'Kamera Vendor', 'price_per_day' => 100000, 'status' => 'Aktif'],
        ]);
    }

    public function test_marketplace_can_filter_products_by_provider(): void
    {
        $this->get(route('marketplace'))
            ->assertOk()
            ->assertSee('Kamera Penyewa')
            ->assertSee('Kamera Mitra')
            ->assertSee('Kamera Vendor')
            ->assertSee('<option value="">Semua</option>', false)
            ->assertSee('<option value="mitra"', false)
            ->assertSee('<option value="camrent"', false)
            ->assertDontSee('<option value="penyewa"', false)
            ->assertDontSee('Sewa &amp; Jual')
            ->assertDontSee('Khusus disewa')
            ->assertDontSee('Khusus dijual');

        $this->get(route('marketplace', ['source' => 'mitra']))
            ->assertOk()
            ->assertSee('Kamera Mitra')
            ->assertDontSee('Kamera Penyewa')
            ->assertDontSee('Kamera Vendor');

        $this->get(route('marketplace', ['source' => 'camrent']))
            ->assertOk()
            ->assertSee('Kamera Vendor')
            ->assertDontSee('Kamera Penyewa')
            ->assertDontSee('Kamera Mitra');

        $this->get(route('marketplace', ['source' => 'penyewa']))
            ->assertOk()
            ->assertSee('Kamera Penyewa')
            ->assertSee('Kamera Mitra')
            ->assertSee('Kamera Vendor');
    }
}
