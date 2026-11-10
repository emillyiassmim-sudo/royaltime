<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StoreController;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito ao painel administrativo.');

        return view('admin.categories', [
            'categories' => Category::query()->orderBy('sort_order')->orderBy('name')->get(),
            'productGroups' => StoreController::productSections(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito ao painel administrativo.');

        Category::query()->create($this->validatedCategoryData($request));

        return redirect()->route('admin.categories')->with('success', 'Coleção criada com sucesso.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito ao painel administrativo.');

        $category->update($this->validatedCategoryData($request, $category));

        return redirect()->route('admin.categories')->with('success', 'Coleção atualizada com sucesso.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito ao painel administrativo.');

        $category->delete();

        return redirect()->route('admin.categories')->with('success', 'Coleção removida com sucesso.');
    }

    /**
     * @return array{name: string, slug: string, product_group: string, image_url: ?string, sort_order: int, is_active: bool}
     */
    private function validatedCategoryData(Request $request, ?Category $category = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('name')),
        ]);

        $uniqueSlug = Rule::unique('categories', 'slug');
        if ($category !== null) {
            $uniqueSlug->ignore($category);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:120', $uniqueSlug],
            'product_group' => ['required', Rule::in(array_keys(StoreController::productSections()))],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['image_url'] = $data['image_url'] ?? null;
        $data['sort_order'] = (int) $data['sort_order'];
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
