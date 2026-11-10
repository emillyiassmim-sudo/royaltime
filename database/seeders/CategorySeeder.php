<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Relógios clássicos',
                'slug' => 'classicos',
                'product_group' => 'relogios',
                'image_url' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=900&q=80',
                'sort_order' => 1,
            ],
            [
                'name' => 'Relógios esportivos',
                'slug' => 'esportivos',
                'product_group' => 'relogios',
                'image_url' => 'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=900&q=80',
                'sort_order' => 2,
            ],
            [
                'name' => 'Relógios dourados',
                'slug' => 'dourados',
                'product_group' => 'relogios',
                'image_url' => 'https://images.unsplash.com/photo-1533139502658-0198f920d8e8?auto=format&fit=crop&w=900&q=80',
                'sort_order' => 3,
            ],
            [
                'name' => 'Relógios prateados',
                'slug' => 'prateados',
                'product_group' => 'relogios',
                'image_url' => 'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?auto=format&fit=crop&w=900&q=80',
                'sort_order' => 4,
            ],
            [
                'name' => 'Relógios com pulseira de couro',
                'slug' => 'couro',
                'product_group' => 'relogios',
                'image_url' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=900&q=80',
                'sort_order' => 5,
            ],
            [
                'name' => 'Relógios com mostrador verde',
                'slug' => 'mostradores-verdes',
                'product_group' => 'relogios',
                'image_url' => 'https://images.unsplash.com/photo-1490367532201-b9bc1dc483f6?auto=format&fit=crop&w=900&q=80',
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'is_active' => true],
            );
        }
    }
}
