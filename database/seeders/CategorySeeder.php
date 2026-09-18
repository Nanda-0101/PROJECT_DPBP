<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Kamera',   'icon' => 'bi-camera',        'sort_order' => 1],
            ['name' => 'Lensa',    'icon' => 'bi-camera2',       'sort_order' => 2],
            ['name' => 'Lighting', 'icon' => 'bi-lightbulb',     'sort_order' => 3],
            ['name' => 'Audio',    'icon' => 'bi-mic',           'sort_order' => 4],
            ['name' => 'Gimbal',   'icon' => 'bi-camera-reels',  'sort_order' => 5],
            ['name' => 'Tripod',   'icon' => 'bi-camera-reels',  'sort_order' => 6],
            ['name' => 'Video',    'icon' => 'bi-play-btn',      'sort_order' => 7],
        ];
        foreach ($data as $row) Category::create($row);
    }
}