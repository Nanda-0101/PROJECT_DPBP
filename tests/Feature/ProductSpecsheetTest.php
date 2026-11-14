<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Support\ProductGallery;
use App\Support\SpecSheet;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\CommerceTestCase;

class ProductSpecsheetTest extends CommerceTestCase
{
    private function createImagesTable(): void
    {
        Schema::create('product_images', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('path', 500);
            $table->string('label', 60)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
        });
    }

    private function addSpecs(Product $product, array $lines): void
    {
        foreach ($lines as $i => $line) {
            DB::table('product_specs')->insert([
                'product_id' => $product->id, 'spec_text' => $line, 'sort_order' => $i + 1,
            ]);
        }
    }

    // ---- Tabel spesifikasi ----

    public function test_spec_sheet_splits_label_value_rows_from_plain_features(): void
    {
        $sheet = SpecSheet::parse([
            'Sensor: 24,2 MP full-frame',
            'ISO: 100–51.200 (diperluas 50–204.800)',
            'Video 4K 30fps',
            '  ',
            'Catatan panjang sekali yang jelas bukan sebuah label karena terlalu panjang: tetap fitur',
        ]);

        $this->assertSame([
            ['label' => 'Sensor', 'value' => '24,2 MP full-frame'],
            ['label' => 'ISO', 'value' => '100–51.200 (diperluas 50–204.800)'],
        ], $sheet['rows']);
        $this->assertSame([
            'Video 4K 30fps',
            'Catatan panjang sekali yang jelas bukan sebuah label karena terlalu panjang: tetap fitur',
        ], $sheet['features']);
    }

    public function test_product_detail_returns_structured_spec_rows_and_keeps_raw_specs(): void
    {
        $product = $this->createProduct(['name' => 'Sony A7III', 'brand' => 'Sony']);
        $this->addSpecs($product, ['Sensor: 24,2 MP full-frame', 'Dual slot SD']);

        $this->getJson(route('marketplace.product.show', $product))
            ->assertOk()
            ->assertJsonPath('specs.0', 'Sensor: 24,2 MP full-frame')
            ->assertJsonPath('spec_rows.0.label', 'Sensor')
            ->assertJsonPath('spec_rows.0.value', '24,2 MP full-frame')
            ->assertJsonPath('spec_features.0', 'Dual slot SD');
    }

    // ---- Galeri foto ----

    public function test_gallery_works_before_the_images_table_exists(): void
    {
        $product = $this->createProduct(['image' => 'products/kamera.jpg']);

        $images = $this->getJson(route('marketplace.product.show', $product))
            ->assertOk()
            ->json('images');

        $this->assertCount(1, $images);
        $this->assertSame('Tampak utama', $images[0]['label']);
    }

    public function test_gallery_lists_main_photo_then_stored_extra_angles_in_order(): void
    {
        $this->createImagesTable();
        $product = $this->createProduct(['image' => 'products/kamera.jpg']);
        ProductImage::create(['product_id' => $product->id, 'path' => 'products/kamera-b.jpg', 'label' => 'Samping', 'sort_order' => 2]);
        ProductImage::create(['product_id' => $product->id, 'path' => 'products/kamera-a.jpg', 'label' => 'Belakang', 'sort_order' => 1]);

        $images = ProductGallery::for($product->fresh());

        $this->assertSame(['Tampak utama', 'Belakang', 'Samping'], array_column($images, 'label'));
        $this->assertStringContainsString('kamera-a.jpg', $images[1]['url']);
    }

    public function test_gallery_discovers_sibling_angle_files_by_naming_convention(): void
    {
        Storage::fake('public');
        foreach (['kamera.jpg', 'kamera-2.jpg', 'kamera-3.png', 'kamera-5.jpg', 'lain-2.jpg'] as $file) {
            Storage::disk('public')->put("products/{$file}", 'x');
        }
        $product = $this->createProduct(['image' => 'products/kamera.jpg']);

        $images = ProductGallery::for($product);

        // -4 tidak ada, jadi -5 tetap ditemukan; "lain-2" milik produk lain diabaikan.
        $this->assertCount(4, $images);
        $this->assertStringContainsString('kamera-2.jpg', $images[1]['url']);
        $this->assertStringContainsString('kamera-3.png', $images[2]['url']);
        $this->assertSame('Sudut 2', $images[1]['label']);
    }

    public function test_gallery_does_not_duplicate_a_photo_present_in_both_sources(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/kamera.jpg', 'x');
        Storage::disk('public')->put('products/kamera-2.jpg', 'x');
        $this->createImagesTable();
        $product = $this->createProduct(['image' => 'products/kamera.jpg']);
        ProductImage::create(['product_id' => $product->id, 'path' => 'products/kamera-2.jpg', 'label' => 'Atas', 'sort_order' => 1]);

        $images = ProductGallery::for($product->fresh());

        $this->assertCount(2, $images);
        $this->assertSame('Atas', $images[1]['label']);
    }

    public function test_external_image_urls_are_used_as_is_without_sibling_lookup(): void
    {
        $product = $this->createProduct(['image' => 'https://example.test/foto.jpg']);

        $images = ProductGallery::for($product);

        $this->assertSame([['url' => 'https://example.test/foto.jpg', 'label' => 'Tampak utama']], $images);
    }

    public function test_product_without_any_photo_returns_empty_gallery(): void
    {
        $product = $this->createProduct(['image' => null]);

        $this->assertSame([], ProductGallery::for($product));
    }

    // ---- Unggah beberapa foto saat menyewakan barang ----

    public function test_renting_out_an_item_keeps_every_uploaded_photo_as_an_angle(): void
    {
        Storage::fake('public');
        $this->createImagesTable();
        $user = $this->createUser(['role' => 'mitra']);

        $this->actingAs($user)->post(route('marketplace.rent-out'), [
            'name' => 'Nikon Z6 II',
            'category' => 'Kamera',
            'price_rent' => 300000,
            'photos' => [
                UploadedFile::fake()->image('depan.jpg'),
                UploadedFile::fake()->image('samping.jpg'),
                UploadedFile::fake()->image('belakang.png'),
            ],
        ])->assertRedirect(route('marketplace'));

        $product = Product::where('name', 'Nikon Z6 II')->firstOrFail();
        $this->assertNotNull($product->image);
        $this->assertSame(2, ProductImage::where('product_id', $product->id)->count());
        $this->assertSame(['Sudut 2', 'Sudut 3'], ProductImage::orderBy('sort_order')->pluck('label')->all());
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_renting_out_limits_the_number_of_photos(): void
    {
        Storage::fake('public');
        $user = $this->createUser(['role' => 'mitra']);
        $photos = array_map(fn ($i) => UploadedFile::fake()->image("foto{$i}.jpg"), range(1, ProductGallery::MAX_EXTRA + 2));

        $this->actingAs($user)->from('/marketplace')
            ->post(route('marketplace.rent-out'), [
                'name' => 'Terlalu banyak foto', 'category' => 'Kamera', 'price_rent' => 100000, 'photos' => $photos,
            ])->assertSessionHasErrors('photos');
    }
}
