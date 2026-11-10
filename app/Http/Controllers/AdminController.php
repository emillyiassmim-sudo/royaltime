<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito ao painel administrativo.');

        $products = StoreController::catalogProducts();
        $orders = Order::with('user')->latest()->limit(5)->get();

        return view('admin.dashboard', [
            'productsCount' => count($products),
            'usersCount' => User::count(),
            'ordersCount' => Order::count(),
            'revenue' => Order::where('status', '!=', 'cancelado')->sum('total'),
            'pendingOrdersCount' => Order::where('status', 'pendente')->count(),
            'lowStockCount' => Product::where('is_active', true)->where('stock', '<=', 5)->count(),
            'recentOrders' => $orders,
        ]);
    }

    public function products(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito ao painel administrativo.');

        return view('admin.products', [
            'products' => StoreController::catalogProducts(),
        ]);
    }

    public function updateProduct(Request $request, string $slug)
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito ao painel administrativo.');

        $product = collect(StoreController::catalogProducts())->first(fn (array $item) => ($item['slug'] ?? '') === $slug);

        abort_if($product === null, 404);

        $category = trim((string) $request->input('category', $product['category'] ?? 'relogios'));
        $selectedSections = array_values(array_filter(
            (array) $request->input('sections', []),
            fn (mixed $section) => isset(StoreController::productSections()[(string) $section])
        ));

        $technicalInput = (array) $request->input('technical', []);
        $technical = is_array($product['technical'] ?? null) ? $product['technical'] : [];

        foreach (StoreController::technicalFieldsForProduct(array_merge($product, ['category' => $category])) as $fieldKey => $fieldLabel) {
            $value = $technicalInput[$fieldLabel] ?? $technicalInput[$fieldKey] ?? $technical[$fieldLabel] ?? $technical[$fieldKey] ?? '';
            $normalizedValue = trim((string) $value);

            if ($normalizedValue === '') {
                unset($technical[$fieldLabel]);

                continue;
            }

            $technical[$fieldLabel] = $normalizedValue;
        }

        $validated = $request->validate([
            'gallery' => ['nullable', 'string'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['string', 'max:2048'],
            'gallery_files' => ['nullable', 'array'],
            'gallery_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        $existingGallery = $product['images'] ?? [$product['image'] ?? ''];
        $requestedGallery = $request->exists('gallery')
            ? $this->parseGalleryInput((string) ($validated['gallery'] ?? ''))
            : $existingGallery;
        $removedImages = array_intersect(
            array_map('strval', $validated['remove_gallery'] ?? []),
            $existingGallery
        );
        $remainingGallery = array_values(array_unique(array_filter(
            $requestedGallery,
            fn (string $image): bool => ! in_array($image, $removedImages, true)
        )));
        $newGalleryImages = count($request->file('gallery_files', []));

        if (count($remainingGallery) + $newGalleryImages < 3) {
            return back()
                ->withErrors(['gallery' => 'Mantenha pelo menos 3 imagens na galeria. Adicione outras imagens antes de remover mais.'])
                ->withInput();
        }

        $gallery = $this->resolveGalleryImages($request, $existingGallery);
        $gallery = StoreController::normalizeProductGalleryImages(['images' => $gallery, 'image' => $gallery[0] ?? ($product['image'] ?? '')]);

        $data = [
            'name' => trim((string) $request->input('name', $product['name'] ?? '')) ?: ($product['name'] ?? ''),
            'slug' => $slug,
            'category' => $category,
            'category_label' => StoreController::productSections()[$category]['label'] ?? ($product['category_label'] ?? 'Relógios masculinos'),
            'description' => trim((string) $request->input('description', $product['description'] ?? '')) ?: ($product['description'] ?? ''),
            'reference' => trim((string) $request->input('reference', $product['reference'] ?? '')) ?: ($product['reference'] ?? ''),
            'image' => $gallery[0] ?? (trim((string) $request->input('image', $product['image'] ?? '')) ?: ($product['image'] ?? '')),
            'images' => $gallery,
            'color' => trim((string) $request->input('color', $product['color'] ?? '')) ?: ($product['color'] ?? ''),
            'strap' => trim((string) $request->input('strap', $product['strap'] ?? '')) ?: ($product['strap'] ?? ''),
            'stock' => max(0, (int) $request->input('stock', $product['stock'] ?? 0)),
            'promo_price' => $request->filled('promo_price') ? (float) $request->input('promo_price') : ($product['promo_price'] ?? null),
            'price' => max(0, (float) $request->input('price', $product['price'] ?? 0)),
            'technical' => $technical,
            'sections' => $selectedSections !== [] ? $selectedSections : [$category],
            'badge' => $product['badge'] ?? 'Novo',
            'installments' => $product['installments'] ?? '10x sem juros',
            'is_featured' => true,
            'is_active' => true,
        ];

        $productModel = Product::query()->firstOrNew(['slug' => $slug]);
        $productModel->fill($data);
        $productModel->save();

        $overrides = $request->session()->get('admin_catalog_overrides', []);
        $overrides[$slug] = $data;
        $request->session()->put('admin_catalog_overrides', $overrides);

        $sectionSelections = $request->session()->get('admin_home_sections', []);
        foreach (array_keys(StoreController::productSections()) as $sectionKey) {
            $sectionSelections[$sectionKey] = array_values(array_filter(
                $sectionSelections[$sectionKey] ?? [],
                fn (string $productSlug) => $productSlug !== $slug
            ));
        }

        foreach ($selectedSections as $sectionKey) {
            $sectionSelections[$sectionKey] = array_values(array_unique(array_merge(
                $sectionSelections[$sectionKey] ?? [],
                [$slug]
            )));
        }

        $request->session()->put('admin_home_sections', $sectionSelections);

        return redirect()->route('admin.products')->with('success', 'Produto atualizado com sucesso.');
    }

    public function storeProduct(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito ao painel administrativo.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:relogios,pulseiras,colares,aneis'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'gallery' => ['nullable', 'string'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['string', 'max:2048'],
            'gallery_files' => ['nullable', 'array'],
            'gallery_files.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'description' => ['nullable', 'string'],
            'reference' => ['nullable', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:80'],
            'strap' => ['nullable', 'string', 'max:80'],
        ]);

        $galleryUrls = $this->parseGalleryInput($validated['gallery'] ?? '');
        $newGalleryImages = count($request->file('gallery_files', []));
        if (count(array_unique($galleryUrls)) + $newGalleryImages < 3) {
            return back()->withErrors(['gallery' => 'Adicione pelo menos 3 imagens para o produto.'])->withInput();
        }
        $gallery = $this->resolveGalleryImages($request, []);

        $category = $validated['category'];
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 2;

        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $category,
            'category_label' => StoreController::productSections()[$category]['label'] ?? 'Produto',
            'description' => $validated['description'] ?? '',
            'price' => (float) $validated['price'],
            'promo_price' => $request->filled('promo_price') ? (float) $request->input('promo_price') : null,
            'stock' => (int) $validated['stock'],
            'reference' => $validated['reference'] ?? '',
            'color' => $validated['color'] ?? '',
            'strap' => $validated['strap'] ?? '',
            'image' => $gallery[0],
            'images' => $gallery,
            'badge' => $request->input('badge', 'Novo'),
            'installments' => $request->input('installments', '10x sem juros'),
            'technical' => $this->buildTechnicalPayload($request, $category),
            'sections' => $this->selectedSectionsForRequest($request, $category),
            'is_featured' => $request->boolean('is_featured', true),
            'is_active' => true,
        ]);

        return redirect()->route('admin.products')->with('success', 'Produto criado com sucesso.');
    }

    public function deleteProduct(Request $request, string $slug)
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito ao painel administrativo.');

        $product = Product::query()->where('slug', $slug)->firstOrFail();
        $product->delete();

        $sectionSelections = $request->session()->get('admin_home_sections', []);
        foreach (array_keys(StoreController::productSections()) as $sectionKey) {
            $sectionSelections[$sectionKey] = array_values(array_filter(
                $sectionSelections[$sectionKey] ?? [],
                fn (string $productSlug) => $productSlug !== $slug
            ));
        }
        $request->session()->put('admin_home_sections', $sectionSelections);

        return redirect()->route('admin.products')->with('success', 'Produto removido com sucesso.');
    }

    private function parseGalleryInput(string $galleryInput): array
    {
        if ($galleryInput === '') {
            return [];
        }

        return array_values(array_filter(
            array_map(fn (string $image) => trim($image), preg_split('/\r\n|\n|\r|,/', $galleryInput)),
            fn (string $image) => $image !== ''
        ));
    }

    /**
     * @param  array<int, string>  $fallbackGallery
     * @return array<int, string>
     */
    private function resolveGalleryImages(Request $request, array $fallbackGallery = []): array
    {
        $baseGallery = $this->parseGalleryInput((string) $request->input('gallery', ''));
        $removedImages = array_values(array_filter(
            array_map(fn (mixed $image) => trim((string) $image), (array) $request->input('remove_gallery', [])),
            fn (string $image) => $image !== ''
        ));

        $currentGallery = $request->exists('gallery') ? $baseGallery : $fallbackGallery;
        if ($removedImages !== []) {
            $currentGallery = array_values(array_filter(
                $currentGallery,
                fn (string $image) => ! in_array($image, $removedImages, true)
                    || ! in_array($image, $fallbackGallery, true)
            ));
        }

        foreach ((array) $request->file('gallery_files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $storedPath = $file->store('products', 'public');
            if ($storedPath === false) {
                continue;
            }

            $currentGallery[] = Storage::disk('public')->url($storedPath);
        }

        return array_values(array_filter(array_unique($currentGallery), fn (string $image) => $image !== ''));
    }

    /**
     * @return array<string, string>
     */
    private function buildTechnicalPayload(Request $request, string $category): array
    {
        $technicalKeys = StoreController::technicalFieldsForProduct(['category' => $category]);
        $payload = [];

        foreach ($technicalKeys as $fieldKey => $fieldLabel) {
            $value = trim((string) ($request->input('technical.'.$fieldLabel, $request->input('technical.'.$fieldKey, ''))));

            if ($value !== '') {
                $payload[$fieldLabel] = $value;
            }
        }

        return $payload;
    }

    /**
     * @return array<int, string>
     */
    private function selectedSectionsForRequest(Request $request, string $category): array
    {
        $sections = array_values(array_filter(
            (array) $request->input('sections', []),
            fn (mixed $section) => isset(StoreController::productSections()[(string) $section])
        ));

        if ($sections === []) {
            return [$category];
        }

        return $sections;
    }
}
