<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductSpec;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $mitraId = \App\Models\User::where('email', 'mitra@camrent.id')->value('id');
        $catId   = fn(string $name) => \App\Models\Category::where('name', $name)->value('id');

        $items = [
            ['Sony A7III',          'Sony',      'Kamera',   280000, 480000, 18500000, 4,  4.9, 212, 1, 'bi-camera',       'Mirrorless full-frame andalan.',       ['Sensor full-frame 24.2MP','Video 4K 30fps','Dual slot SD card']],
            ['Canon EOS R6',        'Canon',     'Kamera',   300000, 520000, 24900000, 3,  4.8, 154, 1, 'bi-camera',       'Mirrorless dengan stabilizer 5-axis.', ['20MP full-frame','4K60 10-bit','IBIS 5-axis']],
            ['Fujifilm X-T5',       'Fujifilm',  'Kamera',   230000, 400000, 0,        2,  4.7, 98,  0, 'bi-camera',       'APS-C dengan film simulation.',        ['40MP APS-C','Film simulation','IBIS 7 stop']],
            ['Sigma 24-70mm f/2.8', 'Sigma',     'Lensa',    150000, 260000, 9800000,  5,  4.6, 76,  1, 'bi-camera2',      'Lensa zoom serbaguna.',                ['Mount E/EF/L','f/2.8 konstan','Optical stabilizer']],
            ['Tamron 70-200mm',     'Tamron',    'Lensa',    180000, 320000, 0,        2,  4.5, 41,  0, 'bi-camera2',      'Telephoto ringan, tajam.',             ['f/2.8','VC stabilizer','67cm min focus']],
            ['Aputure 120D II',     'Aputure',   'Lighting', 190000, 340000, 6500000,  6,  4.8, 132, 1, 'bi-lightbulb',    'LED daylight tenaga besar.',           ['120W daylight 5500K','Bowens mount','Kontrol via aplikasi']],
            ['Godox SL60W',         'Godox',     'Lighting', 90000,  160000, 1850000,  8,  4.4, 59,  0, 'bi-lightbulb',    'LED continuous ekonomis.',             ['60W','Bowens mount','Remote termasuk']],
            ['RØDE NTG5',           'RØDE',      'Audio',    120000, 210000, 4200000,  3,  4.7, 64,  0, 'bi-mic',          'Shotgun mic broadcast-grade.',         ['Shotgun condenser','Low noise 12dB','Tahan cuaca']],
            ['Shure SM7B',          'Shure',     'Audio',    130000, 230000, 4900000,  2,  4.9, 88,  1, 'bi-mic',          'Mic dinamis studio favorit.',          ['Dynamic cardioid','Built-in pop filter','Konektor XLR']],
            ['DJI RS3 Gimbal',      'DJI',       'Gimbal',   140000, 250000, 6900000,  4,  4.8, 120, 1, 'bi-camera-reels', 'Stabilizer 3-axis payload 3kg.',       ['Payload 3kg','LiDAR autofocus','Baterai 12 jam']],
            ['Manfrotto Tripod 190','Manfrotto', 'Tripod',   60000,  100000, 1650000,  10, 4.5, 47,  0, 'bi-camera-reels', 'Tripod aluminium kokoh.',              ['Tinggi maks 150cm','Beban maks 6kg']],
            ['GoPro Hero 12',       'GoPro',     'Video',    110000, 190000, 5600000,  5,  4.6, 93,  1, 'bi-play-btn',     'Action cam tahan air.',                ['5.3K60','HyperSmooth 6.0','Waterproof 10m']],
        ];

        foreach ($items as $it) {
            [$name, $brand, $catName, $half, $day, $sale, $stock, $rating, $reviews, $trend, $icon, $desc, $specs] = $it;

            $product = Product::create([
                'seller_id'          => $mitraId,
                'category_id'        => $catId($catName),
                'name'               => $name,
                'brand'              => $brand,
                'icon'               => $icon,
                'description'        => $desc,
                'price_per_half_day' => $half,
                'price_per_day'      => $day,
                'price_sale'         => $sale,
                'stock'              => $stock,
                'rating'             => $rating,
                'reviews_count'      => $reviews,
                'is_trending'        => (bool) $trend,
                'is_verified'        => true,
                'status'             => 'Aktif',
            ]);

            foreach ($specs as $i => $s) {
                ProductSpec::create([
                    'product_id' => $product->id,
                    'spec_text'  => $s,
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }
}