<?php

namespace Tests\Feature;

use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageDeliveryTest extends TestCase
{
    public function test_database_product_image_paths_are_served_from_public_storage(): void
    {
        Storage::fake('public');
        $image = file_get_contents(storage_path('app/public/products/sony-a7iii.jpg'));
        $this->assertNotFalse($image);
        Storage::disk('public')->put('products/sony-a7iii.jpg', $image);

        $url = ImageHelper::url('products/sony-a7iii.jpg');

        $this->assertSame(route('media.product-image', 'sony-a7iii.jpg'), $url);
        $this->get($url)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_product_image_endpoint_rejects_invalid_file_names(): void
    {
        $this->get('/media/products/..%2F.env')->assertNotFound();
        $this->get('/media/products/not-an-image.txt')->assertNotFound();
    }

    public function test_external_image_urls_are_preserved(): void
    {
        $url = 'https://images.example.test/camera.jpg';

        $this->assertSame($url, ImageHelper::url($url));
    }
}
