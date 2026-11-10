<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Aurelio Chronograph',
                'slug' => 'aurelio-chronograph',
                'category' => 'relogios',
                'category_label' => 'Relógios masculinos',
                'description' => 'Um clássico com proporções refinadas e acabamento premium para quem valoriza sofisticação.',
                'price' => 3280,
                'promo_price' => 2890,
                'stock' => 6,
                'reference' => 'RT-AC-2042',
                'color' => 'Dourado',
                'strap' => 'Couro',
                'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Lançamento',
                'installments' => '12x sem juros',
                'technical' => [
                    'Material da pulseira' => 'Couro genuíno',
                    'Material da caixa' => 'Aço inoxidável',
                    'Mecanismo' => 'Quartz',
                    'Resistência à água' => '5 ATM',
                    'Dimensões' => '40 mm',
                    'Garantia' => '2 anos',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1200&q=80',
                ],
                'sections' => ['relogios'],
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Monarch Automatic',
                'slug' => 'monarch-automatic',
                'category' => 'relogios',
                'category_label' => 'Relógios masculinos',
                'description' => 'Design esportivo com presença marcante, resistência diária e acabamento minimalista.',
                'price' => 2940,
                'promo_price' => null,
                'stock' => 11,
                'reference' => 'RT-MA-3321',
                'color' => 'Preto',
                'strap' => 'Aço',
                'image' => 'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Mais vendido',
                'installments' => '10x sem juros',
                'technical' => [
                    'Material da pulseira' => 'Aço',
                    'Material da caixa' => 'Aço inoxidável',
                    'Mecanismo' => 'Automatic',
                    'Resistência à água' => '10 ATM',
                    'Dimensões' => '42 mm',
                    'Garantia' => '2 anos',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1434056886845-dac89ffe9b56?auto=format&fit=crop&w=1200&q=80',
                ],
                'sections' => ['relogios'],
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Atlas Leather Bracelet',
                'slug' => 'atlas-leather-bracelet',
                'category' => 'pulseiras',
                'category_label' => 'Pulseiras masculinas',
                'description' => 'Pulseira de couro premium para compor o visual urbano com sofisticação discreta.',
                'price' => 860,
                'promo_price' => 720,
                'stock' => 15,
                'reference' => 'RT-AL-101',
                'color' => 'Castanho',
                'strap' => 'Couro',
                'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Essencial',
                'installments' => '10x sem juros',
                'technical' => [
                    'Material' => 'Couro italiano',
                    'Comprimento' => '20 cm',
                    'Tipo de fecho' => 'Fecho de pressão',
                    'Cor' => 'Castanho escuro',
                    'Tamanho' => 'Único',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1200&q=80',
                ],
                'sections' => ['pulseiras'],
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Vanguard Steel Bracelet',
                'slug' => 'vanguard-steel-bracelet',
                'category' => 'pulseiras',
                'category_label' => 'Pulseiras masculinas',
                'description' => 'Aço reforçado com acabamento polido para presença marcante e visual moderno.',
                'price' => 1180,
                'promo_price' => 990,
                'stock' => 11,
                'reference' => 'RT-VS-210',
                'color' => 'Preto',
                'strap' => 'Aço',
                'image' => 'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Popular',
                'installments' => '12x sem juros',
                'technical' => [
                    'Material' => 'Aço inoxidável',
                    'Comprimento' => '22 cm',
                    'Tipo de fecho' => 'Fecho tipo ímã',
                    'Cor' => 'Preto',
                    'Tamanho' => 'M',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1434056886845-dac89ffe9b56?auto=format&fit=crop&w=1200&q=80',
                ],
                'sections' => ['pulseiras'],
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Luxe Silver Chain',
                'slug' => 'luxe-silver-chain',
                'category' => 'colares',
                'category_label' => 'Colares masculinos',
                'description' => 'Corrente com acabamento prateado e design minimalista para looks sofisticados.',
                'price' => 990,
                'promo_price' => 840,
                'stock' => 9,
                'reference' => 'RT-LS-305',
                'color' => 'Prateado',
                'strap' => 'Corrente',
                'image' => 'https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Novo',
                'installments' => '10x sem juros',
                'technical' => [
                    'Material' => 'Aço prateado',
                    'Comprimento da corrente' => '50 cm',
                    'Tipo de fecho' => 'Fecho de segurança',
                    'Pingente' => 'Minimal',
                    'Cor' => 'Prateado',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1490367532201-b9bc1dc483f6?auto=format&fit=crop&w=1200&q=80',
                ],
                'sections' => ['colares'],
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Imperial Signet Ring',
                'slug' => 'imperial-signet-ring',
                'category' => 'aneis',
                'category_label' => 'Anéis masculinos',
                'description' => 'Anel clássico e elegante para um visual marcante, sem excesso.',
                'price' => 760,
                'promo_price' => null,
                'stock' => 18,
                'reference' => 'RT-IS-512',
                'color' => 'Preto',
                'strap' => 'Metal',
                'image' => 'https://images.unsplash.com/photo-1601821765780-754fa986c0fd?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Clássico',
                'installments' => '10x sem juros',
                'technical' => [
                    'Material' => 'Aço escovado',
                    'Cor' => 'Grafite',
                    'Tamanho' => '17',
                    'Modelo' => 'Signet',
                    'Acabamento' => 'Opaco',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1601821765780-754fa986c0fd?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?auto=format&fit=crop&w=1200&q=80',
                ],
                'sections' => ['aneis'],
                'is_featured' => true,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['slug' => $product['slug']],
                $product
            );
        }
    }
}
