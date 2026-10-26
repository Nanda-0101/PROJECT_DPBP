<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MarketplaceProductSpecTest extends TestCase
{
    public function test_marketplace_product_detail_returns_its_specsheet(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('price_per_half_day')->default(0);
            $table->unsignedInteger('price_per_day')->default(0);
            $table->unsignedInteger('price_sale')->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->string('status');
            $table->decimal('rating', 3, 1)->nullable();
        });
        Schema::create('product_specs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('spec_text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('users')->insert(['id' => 1, 'name' => 'Mitra Kamera']);
        DB::table('categories')->insert(['id' => 1, 'name' => 'Kamera']);
        DB::table('products')->insert([
            'id' => 1,
            'seller_id' => 1,
            'category_id' => 1,
            'name' => 'Sony A7III',
            'brand' => 'Sony',
            'description' => 'Kamera full-frame',
            'price_per_day' => 480000,
            'stock' => 1,
            'status' => 'Aktif',
        ]);
        DB::table('product_specs')->insert([
            ['product_id' => 1, 'spec_text' => 'Sensor full-frame 24.2MP', 'sort_order' => 1],
            ['product_id' => 1, 'spec_text' => 'Video 4K 30fps', 'sort_order' => 2],
        ]);

        $this->getJson(route('marketplace.product.show', 1))
            ->assertOk()
            ->assertJsonPath('name', 'Sony A7III')
            ->assertJsonPath('category', 'Kamera')
            ->assertJsonPath('seller', 'Mitra Kamera')
            ->assertJsonPath('specs.0', 'Sensor full-frame 24.2MP')
            ->assertJsonPath('specs.1', 'Video 4K 30fps');
    }
}
