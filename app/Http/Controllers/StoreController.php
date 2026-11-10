<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\BrazilianTaxId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function catalogProducts(): array
    {
        $databaseProducts = Product::query()->orderBy('id')->get();

        if ($databaseProducts->isNotEmpty()) {
            $products = $databaseProducts
                ->map(fn (Product $product) => self::normalizeProductRecord($product))
                ->all();

            $overrides = session()->get('admin_catalog_overrides', []);

            if ($overrides !== []) {
                foreach ($products as $index => $product) {
                    $slug = (string) ($product['slug'] ?? '');

                    if ($slug !== '' && isset($overrides[$slug])) {
                        $products[$index] = array_replace($product, $overrides[$slug]);
                    }
                }
            }

            return $products;
        }

        self::seedProductCatalogIfNeeded();

        $databaseProducts = Product::query()->orderBy('id')->get();

        if ($databaseProducts->isNotEmpty()) {
            return $databaseProducts
                ->map(fn (Product $product) => self::normalizeProductRecord($product))
                ->all();
        }

        $products = [
            [
                'id' => 1,
                'name' => 'Aurelio Chronograph',
                'slug' => 'aurelio-chronograph',
                'category' => 'clássicos',
                'category_label' => 'Relógios clássicos',
                'price' => 3280,
                'promo_price' => 2890,
                'color' => 'Dourado',
                'strap' => 'Couro',
                'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Lançamento',
                'installments' => '12x sem juros',
                'reference' => 'RT-AC-2042',
                'stock' => 6,
                'description' => 'Um clássico com proporções refinadas, caixa em aço, mostrador em tom champagne e acabamento que remete à tradição relojoeira europeia.',
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
                    'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'id' => 2,
                'name' => 'Monarch Automatic',
                'slug' => 'monarch-automatic',
                'category' => 'esportivos',
                'category_label' => 'Relógios esportivos',
                'price' => 2940,
                'promo_price' => null,
                'color' => 'Preto',
                'strap' => 'Aço',
                'image' => 'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Mais vendido',
                'installments' => '10x sem juros',
                'reference' => 'RT-MA-3321',
                'stock' => 11,
                'description' => 'Design esportivo com presença marcante, resistência diária e acabamento minimalista para quem valoriza movimento e funcionalidade.',
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
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'id' => 3,
                'name' => 'Vittorio GMT',
                'slug' => 'vittorio-gmt',
                'category' => 'dourados',
                'category_label' => 'Relógios dourados',
                'price' => 4190,
                'promo_price' => 3890,
                'color' => 'Dourado',
                'strap' => 'Aço',
                'image' => 'https://images.unsplash.com/photo-1434056886845-dac89ffe9b56?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Coleção',
                'installments' => '12x sem juros',
                'reference' => 'RT-VG-1189',
                'stock' => 4,
                'description' => 'Composição elegante em dourado, ideal para quem busca realeza discreta e sofisticação em cada detalhe do relógio.',
                'technical' => [
                    'Material da pulseira' => 'Aço',
                    'Material da caixa' => 'Aço dourado',
                    'Mecanismo' => 'Automatic',
                    'Resistência à água' => '5 ATM',
                    'Dimensões' => '41 mm',
                    'Garantia' => '2 anos',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1434056886845-dac89ffe9b56?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'id' => 4,
                'name' => 'Siena Heritage',
                'slug' => 'siena-heritage',
                'category' => 'prateados',
                'category_label' => 'Relógios prateados',
                'price' => 3520,
                'promo_price' => null,
                'color' => 'Prateado',
                'strap' => 'Couro',
                'image' => 'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Clássico',
                'installments' => '9x sem juros',
                'reference' => 'RT-SH-7421',
                'stock' => 8,
                'description' => 'Linha de corte clássico com brilho discreto em prata e acabamento refinado para compor o look formal com presença.',
                'technical' => [
                    'Material da pulseira' => 'Couro',
                    'Material da caixa' => 'Aço prateado',
                    'Mecanismo' => 'Quartz',
                    'Resistência à água' => '3 ATM',
                    'Dimensões' => '38 mm',
                    'Garantia' => '18 meses',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1490367532201-b9bc1dc483f6?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'id' => 5,
                'name' => 'Milano Diver',
                'slug' => 'milano-diver',
                'category' => 'esportivos',
                'category_label' => 'Relógios esportivos',
                'price' => 3650,
                'promo_price' => 3290,
                'color' => 'Azul',
                'strap' => 'Nylon',
                'image' => 'https://images.unsplash.com/photo-1612817159949-195b6eb9e31a?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Oferta',
                'installments' => '12x sem juros',
                'reference' => 'RT-MD-9124',
                'stock' => 13,
                'description' => 'Uma referência para uso ativo com visual contemporâneo, leitura clara e resistência para dias intensos.',
                'technical' => [
                    'Material da pulseira' => 'Nylon',
                    'Material da caixa' => 'Aço inoxidável',
                    'Mecanismo' => 'Quartz',
                    'Resistência à água' => '10 ATM',
                    'Dimensões' => '43 mm',
                    'Garantia' => '2 anos',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1612817159949-195b6eb9e31a?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1434056886845-dac89ffe9b56?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'id' => 6,
                'name' => 'Verona Leather',
                'slug' => 'verona-leather',
                'category' => 'couro',
                'category_label' => 'Relógios com pulseira de couro',
                'price' => 2780,
                'promo_price' => null,
                'color' => 'Marrom',
                'strap' => 'Couro',
                'image' => 'https://images.unsplash.com/photo-1490367532201-b9bc1dc483f6?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Premium',
                'installments' => '10x sem juros',
                'reference' => 'RT-VL-2841',
                'stock' => 7,
                'description' => 'A textura do couro em contraste com o metal elegante cria uma proposta clássica e polida para diferentes ocasiões.',
                'technical' => [
                    'Material da pulseira' => 'Couro italiano',
                    'Material da caixa' => 'Aço inoxidável',
                    'Mecanismo' => 'Quartz',
                    'Resistência à água' => '3 ATM',
                    'Dimensões' => '39 mm',
                    'Garantia' => '18 meses',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1490367532201-b9bc1dc483f6?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'id' => 7,
                'name' => 'Napoli Green',
                'slug' => 'napoli-green',
                'category' => 'mostradores-verdes',
                'category_label' => 'Relógios com mostrador verde',
                'price' => 3390,
                'promo_price' => 2990,
                'color' => 'Verde',
                'strap' => 'Couro',
                'image' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Novo',
                'installments' => '12x sem juros',
                'reference' => 'RT-NG-1785',
                'stock' => 9,
                'description' => 'Um modelo exclusivo com detalhe verde em destaque, pensado para quem deseja um relógio com identidade forte e presença precisa.',
                'technical' => [
                    'Material da pulseira' => 'Couro',
                    'Material da caixa' => 'Aço inoxidável',
                    'Mecanismo' => 'Quartz',
                    'Resistência à água' => '5 ATM',
                    'Dimensões' => '40 mm',
                    'Garantia' => '2 anos',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1490367532201-b9bc1dc483f6?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'id' => 8,
                'name' => 'Cervo Reserve',
                'slug' => 'cervo-reserve',
                'category' => 'clássicos',
                'category_label' => 'Relógios clássicos',
                'price' => 4560,
                'promo_price' => null,
                'color' => 'Preto',
                'strap' => 'Couro',
                'image' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Exclusivo',
                'installments' => '12x sem juros',
                'reference' => 'RT-CR-4042',
                'stock' => 3,
                'description' => 'Uma peça de alto impacto visual, focada em presença, acabamento preciso e um acabamento luxuoso em linha com o que a Royal Time representa.',
                'technical' => [
                    'Material da pulseira' => 'Couro escuro',
                    'Material da caixa' => 'Aço',
                    'Mecanismo' => 'Automatic',
                    'Resistência à água' => '5 ATM',
                    'Dimensões' => '42 mm',
                    'Garantia' => '2 anos',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1434056886845-dac89ffe9b56?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'id' => 9,
                'name' => 'Atlas Leather Bracelet',
                'slug' => 'atlas-leather-bracelet',
                'category' => 'pulseiras',
                'category_label' => 'Pulseiras masculinas',
                'price' => 860,
                'promo_price' => 720,
                'color' => 'Castanho',
                'strap' => 'Couro',
                'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Essencial',
                'installments' => '10x sem juros',
                'reference' => 'RT-AL-101',
                'stock' => 15,
                'description' => 'Pulseira de couro com acabamento premium, ideal para compor o visual urbano com sofisticação discreta.',
                'technical' => [
                    'Material' => 'Couro italiano',
                    'Comprimento' => '20 cm',
                    'Fecho' => 'Fecho de pressão',
                    'Cor' => 'Castanho escuro',
                    'Tamanho' => 'Único',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1200&q=80',
                ],
                'sections' => ['pulseiras'],
            ],
            [
                'id' => 10,
                'name' => 'Vanguard Steel Bracelet',
                'slug' => 'vanguard-steel-bracelet',
                'category' => 'pulseiras',
                'category_label' => 'Pulseiras masculinas',
                'price' => 1180,
                'promo_price' => 990,
                'color' => 'Preto',
                'strap' => 'Aço',
                'image' => 'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Popular',
                'installments' => '12x sem juros',
                'reference' => 'RT-VS-210',
                'stock' => 11,
                'description' => 'Aço reforçado com acabamento polido para uma presença marcante e visual moderno, perfeito para o dia a dia.',
                'technical' => [
                    'Material' => 'Aço inoxidável',
                    'Comprimento' => '22 cm',
                    'Fecho' => 'Fecho tipo ímã',
                    'Cor' => 'Preto',
                    'Tamanho' => 'M',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1434056886845-dac89ffe9b56?auto=format&fit=crop&w=1200&q=80',
                ],
                'sections' => ['pulseiras'],
            ],
            [
                'id' => 11,
                'name' => 'Luxe Silver Chain',
                'slug' => 'luxe-silver-chain',
                'category' => 'colares',
                'category_label' => 'Colares masculinos',
                'price' => 990,
                'promo_price' => 840,
                'color' => 'Prateado',
                'strap' => 'Corrente',
                'image' => 'https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Novo',
                'installments' => '10x sem juros',
                'reference' => 'RT-LS-305',
                'stock' => 9,
                'description' => 'Corrente com acabamento prateado e design minimalista, pensada para complementar looks sofisticados e discretos.',
                'technical' => [
                    'Material' => 'Aço prateado',
                    'Comprimento' => '50 cm',
                    'Fecho' => 'Fecho de segurança',
                    'Pingente' => 'Minimal',
                    'Cor' => 'Prateado',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1490367532201-b9bc1dc483f6?auto=format&fit=crop&w=1200&q=80',
                ],
                'sections' => ['colares'],
            ],
            [
                'id' => 12,
                'name' => 'Orion Gold Necklace',
                'slug' => 'orion-gold-necklace',
                'category' => 'colares',
                'category_label' => 'Colares masculinos',
                'price' => 1450,
                'promo_price' => 1290,
                'color' => 'Dourado',
                'strap' => 'Corrente',
                'image' => 'https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Premium',
                'installments' => '12x sem juros',
                'reference' => 'RT-OG-411',
                'stock' => 6,
                'description' => 'Peça de alto luxo com corrente em dourado e acabamento refinado para quem valoriza presença e distinção.',
                'technical' => [
                    'Material' => 'Aço dourado',
                    'Comprimento' => '55 cm',
                    'Fecho' => 'Fecho de segurança',
                    'Pingente' => 'Cristal',
                    'Cor' => 'Dourado',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1200&q=80',
                ],
                'sections' => ['colares'],
            ],
            [
                'id' => 13,
                'name' => 'Imperial Signet Ring',
                'slug' => 'imperial-signet-ring',
                'category' => 'aneis',
                'category_label' => 'Anéis masculinos',
                'price' => 760,
                'promo_price' => null,
                'color' => 'Preto',
                'strap' => 'Metal',
                'image' => 'https://images.unsplash.com/photo-1601821765780-754fa986c0fd?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Clássico',
                'installments' => '10x sem juros',
                'reference' => 'RT-IS-512',
                'stock' => 18,
                'description' => 'Anel com design clássico e acabamento elegante para um olhar marcante, com presença sem exagero.',
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
            ],
            [
                'id' => 14,
                'name' => 'Monarch Ring',
                'slug' => 'monarch-ring',
                'category' => 'aneis',
                'category_label' => 'Anéis masculinos',
                'price' => 1020,
                'promo_price' => 890,
                'color' => 'Dourado',
                'strap' => 'Metal',
                'image' => 'https://images.unsplash.com/photo-1601821765780-754fa986c0fd?auto=format&fit=crop&w=900&q=80',
                'badge' => 'Especial',
                'installments' => '12x sem juros',
                'reference' => 'RT-MR-609',
                'stock' => 12,
                'description' => 'Um anel de luxo em dourado com linhas refinadas e acabamento impecável para um dressing com personalidade.',
                'technical' => [
                    'Material' => 'Zinco dourado',
                    'Cor' => 'Dourado',
                    'Tamanho' => '18',
                    'Modelo' => 'Executivo',
                    'Acabamento' => 'Polido',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1601821765780-754fa986c0fd?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=1200&q=80',
                ],
                'sections' => ['aneis'],
            ],
        ];

        $overrides = session()->get('admin_catalog_overrides', []);

        if ($overrides !== []) {
            foreach ($products as $index => $product) {
                $slug = (string) ($product['slug'] ?? '');

                if ($slug !== '' && isset($overrides[$slug])) {
                    $products[$index] = array_replace($product, $overrides[$slug]);
                    $products[$index]['images'] = self::normalizeProductGalleryImages($products[$index]);
                    $products[$index]['image'] = $products[$index]['images'][0] ?? $products[$index]['image'];
                }
            }
        }

        foreach ($products as $index => $product) {
            $products[$index]['images'] = self::normalizeProductGalleryImages($product);
            $products[$index]['image'] = $products[$index]['images'][0] ?? $products[$index]['image'];
        }

        return $products;
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<int, string>
     */
    public static function normalizeProductGalleryImages(array $product): array
    {
        $images = array_values(array_filter(
            array_map(fn (mixed $image) => trim((string) $image), (array) ($product['images'] ?? [])),
            fn (string $image) => $image !== ''
        ));

        if ($images === [] && ! empty($product['image'])) {
            $images = [(string) $product['image']];
        }

        return array_slice(array_values(array_unique($images)), 0, 6);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function seedProductCatalogIfNeeded(): array
    {
        if (Product::query()->exists()) {
            return Product::query()->orderBy('id')->get()->map(fn (Product $product) => self::normalizeProductRecord($product))->all();
        }

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
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['slug' => $product['slug']],
                [
                    'name' => $product['name'],
                    'category' => $product['category'],
                    'category_label' => $product['category_label'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'promo_price' => $product['promo_price'],
                    'stock' => $product['stock'],
                    'reference' => $product['reference'],
                    'color' => $product['color'],
                    'strap' => $product['strap'],
                    'image' => $product['image'],
                    'images' => $product['images'],
                    'badge' => $product['badge'],
                    'installments' => $product['installments'],
                    'technical' => $product['technical'],
                    'sections' => $product['sections'],
                    'is_featured' => true,
                    'is_active' => true,
                ]
            );
        }

        return Product::query()->orderBy('id')->get()->map(fn (Product $product) => self::normalizeProductRecord($product))->all();
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private static function normalizeProductRecord(Product $product): array
    {
        $images = is_array($product->images) ? $product->images : [];

        if ($images === [] && ! empty($product->image)) {
            $images = [$product->image];
        }

        $category = (string) ($product->category ?? 'relogios');
        $group = self::productCategoryGroup(['category' => $category]);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'category' => $category,
            'category_label' => self::productSections()[$group]['label'] ?? $product->category_label ?? ucfirst($category),
            'price' => (float) $product->price,
            'promo_price' => $product->promo_price !== null ? (float) $product->promo_price : null,
            'color' => $product->color,
            'strap' => $product->strap,
            'image' => $product->image ?: ($images[0] ?? ''),
            'badge' => $product->badge,
            'installments' => $product->installments,
            'reference' => $product->reference,
            'stock' => (int) $product->stock,
            'description' => $product->description,
            'technical' => is_array($product->technical) ? $product->technical : [],
            'images' => $images,
            'sections' => is_array($product->sections) ? $product->sections : [$group],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function productSections(): array
    {
        return [
            'relogios' => ['label' => 'Relógios masculinos'],
            'pulseiras' => ['label' => 'Pulseiras masculinas'],
            'colares' => ['label' => 'Colares masculinos'],
            'aneis' => ['label' => 'Anéis masculinos'],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function technicalFieldDefinitions(): array
    {
        return [
            'relogios' => [
                'mecanismo' => 'Mecanismo',
                'material_caixa' => 'Material da caixa',
                'material_pulseira' => 'Material da pulseira',
                'diametro' => 'Diâmetro',
                'resistencia_agua' => 'Resistência à água',
                'tipo_mostrador' => 'Tipo de mostrador',
            ],
            'pulseiras' => [
                'material' => 'Material',
                'comprimento' => 'Comprimento',
                'tipo_fecho' => 'Tipo de fecho',
                'cor' => 'Cor',
                'tamanho' => 'Tamanho',
            ],
            'colares' => [
                'material' => 'Material',
                'comprimento_corrente' => 'Comprimento da corrente',
                'tipo_fecho' => 'Tipo de fecho',
                'pingente' => 'Pingente',
                'cor' => 'Cor',
            ],
            'aneis' => [
                'material' => 'Material',
                'cor' => 'Cor',
                'tamanho' => 'Tamanho',
                'modelo' => 'Modelo',
                'acabamento' => 'Acabamento',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, string>
     */
    public static function technicalFieldsForProduct(array $product): array
    {
        $group = self::productCategoryGroup(['category' => $product['category'] ?? '']);

        return self::technicalFieldDefinitions()[$group] ?? self::technicalFieldDefinitions()['relogios'];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function categories(): array
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(static fn (Category $category): array => [
                'slug' => $category->slug,
                'name' => $category->name,
                'product_group' => $category->product_group,
                'image_url' => $category->image_url,
            ])
            ->all();
    }

    public static function productCategoryGroup(array $product): string
    {
        $category = strtolower((string) ($product['category'] ?? ''));

        return match ($category) {
            'clássicos', 'esportivos', 'dourados', 'prateados', 'relogios' => 'relogios',
            'pulseiras' => 'pulseiras',
            'colares' => 'colares',
            'aneis', 'anéis' => 'aneis',
            default => $category,
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $products
     * @return array<int, array<string, mixed>>
     */
    private function homeSectionProducts(array $products, string $sectionKey): array
    {
        $selected = session()->get('admin_home_sections', []);
        $productList = array_values(array_filter(
            $products,
            fn (array $product) => in_array($sectionKey, $product['sections'] ?? [$this->productCategoryGroup($product)], true)
        ));

        if (isset($selected[$sectionKey]) && is_array($selected[$sectionKey])) {
            $slugs = $selected[$sectionKey];
            $productList = array_values(array_filter(
                $productList,
                fn (array $product) => in_array((string) ($product['slug'] ?? ''), $slugs, true)
            ));
        }

        return $productList;
    }

    public function home()
    {
        $products = self::catalogProducts();
        $featuredProducts = array_slice($products, 0, 6);
        $sectionProducts = [];

        foreach (array_keys(self::productSections()) as $sectionKey) {
            $sectionProducts[$sectionKey] = $this->homeSectionProducts($products, $sectionKey);
            if ($sectionProducts[$sectionKey] === []) {
                $sectionProducts[$sectionKey] = array_values(array_filter(
                    $products,
                    fn (array $product) => in_array($sectionKey, $product['sections'] ?? [$this->productCategoryGroup($product)], true)
                ));
            }
        }

        return view('home', [
            'categories' => $this->categories(),
            'featuredProducts' => $featuredProducts,
            'sectionProducts' => $sectionProducts,
            'categorySections' => self::productSections(),
        ]);
    }

    public function catalog(Request $request)
    {
        $products = self::catalogProducts();

        $category = trim((string) $request->query('category', ''));
        $categoryRecord = $category === ''
            ? null
            : Category::query()->where('slug', $category)->where('is_active', true)->first();
        $categoryGroup = $categoryRecord?->product_group ?? $category;
        $color = trim((string) $request->query('color', ''));
        $strap = trim((string) $request->query('strap', ''));
        $range = trim((string) $request->query('range', ''));
        $sort = trim((string) $request->query('sort', 'featured'));
        $query = trim((string) $request->query('q', ''));

        if ($categoryGroup !== '') {
            $products = array_values(array_filter(
                $products,
                fn (array $product) => $this->productCategoryGroup($product) === $categoryGroup
            ));
        }

        if ($query !== '') {
            $searchTerm = Str::lower($query);
            $products = array_values(array_filter(
                $products,
                fn (array $product) => str_contains(Str::lower((string) $product['name']), $searchTerm)
                    || str_contains(Str::lower((string) $product['description']), $searchTerm)
            ));
        }

        if ($color !== '') {
            $products = array_values(array_filter(
                $products,
                fn (array $product) => strtolower((string) $product['color']) === strtolower($color)
            ));
        }

        if ($strap !== '') {
            $products = array_values(array_filter(
                $products,
                fn (array $product) => strtolower((string) $product['strap']) === strtolower($strap)
            ));
        }

        if ($range !== '') {
            [$minPrice, $maxPrice] = match ($range) {
                '0-1500' => [0, 1500],
                '1500-3000' => [1500, 3000],
                '3000-4500' => [3000, 4500],
                '4500-6000' => [4500, 6000],
                default => [0, PHP_INT_MAX],
            };

            $products = array_values(array_filter(
                $products,
                fn (array $product) => (float) $product['price'] >= $minPrice && (float) $product['price'] <= $maxPrice
            ));
        }

        if ($sort === 'price_asc') {
            usort($products, fn (array $a, array $b) => (float) $a['price'] <=> (float) $b['price']);
        } elseif ($sort === 'price_desc') {
            usort($products, fn (array $a, array $b) => (float) $b['price'] <=> (float) $a['price']);
        }

        $colorOptions = collect($products)->pluck('color')->unique()->values()->all();
        $strapOptions = collect($products)->pluck('strap')->unique()->values()->all();

        return view('catalog', [
            'categories' => $this->categories(),
            'products' => $products,
            'selectedCategory' => $category,
            'selectedQuery' => $query,
            'selectedColor' => $color,
            'selectedStrap' => $strap,
            'selectedRange' => $range,
            'selectedSort' => $sort,
            'colorOptions' => $colorOptions,
            'strapOptions' => $strapOptions,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findProduct(string $slug): ?array
    {
        $product = collect(self::catalogProducts())->first(fn (array $item) => ($item['slug'] ?? '') === $slug);

        return is_array($product) ? $product : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function relatedProducts(array $product): array
    {
        $related = collect(self::catalogProducts())
            ->reject(fn (array $item) => ($item['slug'] ?? '') === ($product['slug'] ?? ''))
            ->filter(fn (array $item) => $this->productCategoryGroup($item) === $this->productCategoryGroup($product))
            ->take(3)
            ->values()
            ->all();

        if (count($related) < 3) {
            $related = collect(self::catalogProducts())
                ->reject(fn (array $item) => ($item['slug'] ?? '') === ($product['slug'] ?? ''))
                ->take(3)
                ->values()
                ->all();
        }

        return $related;
    }

    public function product(string $slug)
    {
        $product = $this->findProduct($slug);

        abort_if($product === null, 404);

        return view('product', [
            'product' => $product,
            'relatedProducts' => $this->relatedProducts($product),
        ]);
    }

    public function addToCart(Request $request, string $slug)
    {
        $product = $this->findProduct($slug);

        abort_if($product === null, 404);

        $cart = $request->session()->get('cart', []);
        $quantity = max(1, (int) $request->input('quantity', 1));
        $stock = (int) ($product['stock'] ?? 1);

        $currentQuantity = isset($cart[$slug]['quantity']) ? (int) $cart[$slug]['quantity'] : 0;
        $newQuantity = min($stock, $currentQuantity + $quantity);

        $cart[$slug] = [
            'slug' => $slug,
            'name' => $product['name'],
            'price' => $product['promo_price'] ?? $product['price'],
            'image' => $product['image'],
            'quantity' => $newQuantity,
        ];

        $request->session()->put('cart', $cart);

        return redirect()->route('cart')->with('success', 'Produto adicionado ao carrinho.');
    }

    public function removeFromCart(Request $request, string $slug)
    {
        $cart = $request->session()->get('cart', []);

        unset($cart[$slug]);

        $request->session()->put('cart', $cart);

        return redirect()->route('cart')->with('success', 'Produto removido do carrinho.');
    }

    public function updateCart(Request $request, string $slug)
    {
        $product = $this->findProduct($slug);

        abort_if($product === null, 404);

        $cart = $request->session()->get('cart', []);
        $quantity = max(1, (int) $request->input('quantity', 1));
        $stock = (int) ($product['stock'] ?? 1);

        if (isset($cart[$slug])) {
            $cart[$slug]['quantity'] = min($stock, $quantity);
            $request->session()->put('cart', $cart);
        }

        return redirect()->route('cart');
    }

    private function cartContext(Request $request): array
    {
        $cart = $request->session()->get('cart', []);
        $items = [];
        $subtotal = 0.0;

        foreach ($cart as $slug => $item) {
            $product = $this->findProduct($slug);

            if ($product === null) {
                continue;
            }

            $price = (float) ($product['promo_price'] ?? $product['price']);
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $lineTotal = $price * $quantity;

            $items[] = [
                'slug' => $slug,
                'name' => $product['name'],
                'image' => $product['image'],
                'price' => $price,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
                'stock' => (int) ($product['stock'] ?? 1),
            ];

            $subtotal += $lineTotal;
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ];
    }

    public function checkout(Request $request)
    {
        $user = $request->user();
        $cartContext = $this->cartContext($request);
        $items = $cartContext['items'];

        if ($items === []) {
            return redirect()->route('cart')->with('error', 'Seu carrinho está vazio.');
        }

        $shipping = $cartContext['subtotal'] >= 2500 ? 0.0 : 149.90;
        $addresses = $user->addresses()->latest()->get();
        $checkoutToken = (string) Str::uuid();
        $request->session()->put('checkout_token', $checkoutToken);

        return view('checkout', [
            'items' => $items,
            'subtotal' => $cartContext['subtotal'],
            'shipping' => $shipping,
            'total' => $cartContext['subtotal'] + $shipping,
            'addresses' => $addresses,
            'selectedAddressId' => $addresses->first()?->id,
            'user' => $user,
            'checkoutToken' => $checkoutToken,
        ]);
    }

    public function placeOrder(Request $request)
    {
        $user = $request->user();
        $cartContext = $this->cartContext($request);
        $items = $cartContext['items'];

        if ($items === []) {
            return redirect()->route('cart')->with('error', 'Seu carrinho está vazio.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (count(preg_split('/\s+/u', trim((string) $value)) ?: []) < 2) {
                        $fail('Informe seu nome completo.');
                    }
                },
            ],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'cpf' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! BrazilianTaxId::isValid((string) $value)) {
                        $fail('Informe um CPF válido.');
                    }
                },
            ],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9()\s+.-]{10,30}$/'],
            'address_id' => ['required', 'integer'],
            'payment_method' => ['required', Rule::in(['cartao', 'pix', 'boleto'])],
            'checkout_token' => ['required', 'uuid', Rule::in([(string) $request->session()->get('checkout_token')])],
        ]);
        $normalizedCpf = BrazilianTaxId::normalize($validated['cpf']);
        $cpfHash = hash('sha256', $normalizedCpf);

        Validator::make(
            ['cpf_hash' => $cpfHash],
            ['cpf_hash' => [Rule::unique('users', 'cpf_hash')->ignore($user->id)]],
            ['cpf_hash.unique' => 'Este CPF já está cadastrado em outra conta.'],
            ['cpf_hash' => 'CPF']
        )->validate();

        $address = $user->addresses()->find($validated['address_id']);

        if ($address === null) {
            throw ValidationException::withMessages([
                'address_id' => 'Selecione ou cadastre um endereço para entrega.',
            ]);
        }

        $order = DB::transaction(function () use ($user, $address, $validated, $normalizedCpf, $cpfHash, $items): Order {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            $existingOrder = Order::query()->where('checkout_token', $validated['checkout_token'])->first();
            if ($existingOrder !== null) {
                return $existingOrder;
            }

            $orderItems = [];
            $subtotal = 0.0;

            foreach ($items as $item) {
                $product = Product::query()
                    ->where('slug', $item['slug'])
                    ->lockForUpdate()
                    ->first();

                if ($product === null || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Um dos produtos do pedido não está mais disponível.',
                    ]);
                }

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => 'Estoque insuficiente para '.$product->name.'. Atualize a quantidade no carrinho.',
                    ]);
                }

                $price = (float) ($product->promo_price ?? $product->price);
                $lineTotal = $price * $item['quantity'];
                $subtotal += $lineTotal;
                $orderItems[] = [
                    'slug' => $product->slug,
                    'name' => $product->name,
                    'reference' => $product->reference,
                    'category' => $product->category,
                    'category_label' => $product->category_label,
                    'description' => $product->description,
                    'color' => $product->color,
                    'strap' => $product->strap,
                    'image' => $product->image,
                    'technical' => $product->technical ?? [],
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'line_total' => $lineTotal,
                ];

                $product->decrement('stock', $item['quantity']);
            }

            $shipping = $subtotal >= 2500 ? 0.0 : 149.90;
            $user->forceFill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'cpf' => $normalizedCpf,
                'cpf_hash' => $cpfHash,
                'phone' => $validated['phone'],
            ])->save();

            return $user->orders()->create([
                'address_id' => $address->id,
                'status' => 'pendente',
                'total' => $subtotal + $shipping,
                'shipping' => $shipping,
                'items' => $orderItems,
                'customer_snapshot' => [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'cpf' => $normalizedCpf,
                    'phone' => $validated['phone'],
                    'address' => $address->only([
                        'recipient_name',
                        'cep',
                        'address',
                        'number',
                        'complement',
                        'neighborhood',
                        'city',
                        'state',
                    ]),
                ],
                'payment_method' => $validated['payment_method'],
                'checkout_token' => $validated['checkout_token'],
            ]);
        });

        $request->session()->forget('cart');
        $request->session()->forget('checkout_token');

        return redirect()->route('account.orders')->with('success', 'Pedido #'.$order->id.' realizado com sucesso.');
    }

    public function cart(Request $request)
    {
        $cartContext = $this->cartContext($request);

        return view('cart', [
            'items' => $cartContext['items'],
            'subtotal' => $cartContext['subtotal'],
            'total' => $cartContext['total'],
        ]);
    }
}
