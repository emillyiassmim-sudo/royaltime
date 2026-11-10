<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class AdminCategoriesTest extends TestCase
{
    public function test_homepage_only_displays_active_managed_categories(): void
    {
        Category::query()->create([
            'name' => 'Relógios clássicos',
            'slug' => 'classicos',
            'product_group' => 'relogios',
            'image_url' => 'https://example.com/classicos.jpg',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        Category::query()->create([
            'name' => 'Coleção reservada',
            'slug' => 'reservada',
            'product_group' => 'relogios',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('Relógios clássicos');
        $response->assertDontSeeText('Coleção reservada');
    }

    public function test_admin_can_create_update_and_delete_a_category(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $createResponse = $this->post(route('admin.categories.store'), [
            'name' => 'Relógios clássicos',
            'product_group' => 'relogios',
            'image_url' => 'https://example.com/classicos.jpg',
            'sort_order' => 1,
            'is_active' => '1',
        ]);

        $createResponse->assertRedirect(route('admin.categories'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Relógios clássicos',
            'slug' => 'relogios-classicos',
            'product_group' => 'relogios',
            'is_active' => true,
        ]);

        $pageResponse = $this->get(route('admin.categories'));
        $pageResponse->assertOk();
        $pageResponse->assertSeeText('Relógios clássicos');

        $updateResponse = $this->put(route('admin.categories.update', ['category' => 'relogios-classicos']), [
            'name' => 'Coleção clássica',
            'slug' => 'classicos',
            'product_group' => 'relogios',
            'sort_order' => 3,
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect(route('admin.categories'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Coleção clássica',
            'slug' => 'classicos',
            'sort_order' => 3,
        ]);

        $deleteResponse = $this->delete(route('admin.categories.destroy', ['category' => 'classicos']));

        $deleteResponse->assertRedirect(route('admin.categories'));
        $this->assertDatabaseMissing('categories', ['slug' => 'classicos']);
    }

    public function test_non_admin_cannot_manage_categories(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)->get(route('admin.categories'))->assertForbidden();

        $response = $this->post(route('admin.categories.store'), [
            'name' => 'Coleção restrita',
            'product_group' => 'relogios',
            'sort_order' => 1,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('categories', ['name' => 'Coleção restrita']);
    }

    public function test_category_requires_a_valid_product_group(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Coleção inválida',
            'product_group' => 'nao-existe',
            'sort_order' => 1,
        ]);

        $response->assertSessionHasErrors('product_group');
        $this->assertDatabaseMissing('categories', ['name' => 'Coleção inválida']);
    }

    public function test_collection_category_filters_products_by_its_product_group(): void
    {
        Category::query()->create([
            'name' => 'Relógios clássicos',
            'slug' => 'classicos',
            'product_group' => 'relogios',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('catalog', ['category' => 'classicos']));

        $response->assertOk();
        $response->assertSeeText('Aurelio Chronograph');
        $response->assertDontSeeText('Atlas Leather Bracelet');
    }
}
