<?php

namespace Tests\Feature;

use App\Http\Controllers\StoreController;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    public function test_homepage_displays_brand_and_collection(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('ROYAL TIME');
        $response->assertSeeText('Elegância que não precisa de apresentação.');
        $response->assertSeeText('Pulseiras masculinas');
    }

    public function test_catalog_page_displays_products_and_filters(): void
    {
        $response = $this->get('/catalogo');

        $response->assertOk();
        $response->assertSeeText('Relógios para homens que valorizam presença e elegância.');
        $response->assertSeeText('Aurelio Chronograph');
        $response->assertSeeText('Categoria');
    }

    public function test_catalog_search_filters_products_by_name(): void
    {
        $response = $this->get('/catalogo?q=Aurelio');

        $response->assertOk();
        $response->assertSeeText('Aurelio Chronograph');
        $response->assertDontSeeText('Monarch Automatic');
    }

    public function test_product_page_displays_product_details(): void
    {
        $response = $this->get('/produto/aurelio-chronograph');

        $response->assertOk();
        $response->assertSeeText('Aurelio Chronograph');
        $response->assertSeeText('Ref. RT-AC-2042');
        $response->assertSeeText('ADICIONAR AO CARRINHO');
    }

    public function test_cart_can_add_products_and_persist_them_in_session(): void
    {
        $response = $this->withSession([])
            ->post('/carrinho/adicionar/aurelio-chronograph', ['quantity' => 2]);

        $response->assertRedirect('/carrinho');
        $this->assertNotEmpty(session('cart'));
        $this->assertSame(2, session('cart.aurelio-chronograph.quantity'));
    }

    public function test_customer_can_register_and_access_dashboard(): void
    {
        $response = $this->post('/cadastro', [
            'name' => 'Cliente Teste',
            'email' => 'cliente@royaltime.com.br',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
        ]);

        $response->assertRedirect('/minha-conta');
        $this->assertDatabaseHas('users', ['email' => 'cliente@royaltime.com.br']);

        $user = User::where('email', 'cliente@royaltime.com.br')->first();
        $dashboard = $this->actingAs($user)->get('/minha-conta');

        $dashboard->assertOk();
        $dashboard->assertSeeText('Olá, Cliente Teste');
    }

    public function test_customer_can_checkout_and_create_an_order(): void
    {
        $user = User::factory()->create([
            'name' => 'Cliente Checkout',
            'email' => 'checkout@royaltime.com.br',
        ]);

        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Casa',
            'recipient_name' => 'Cliente Checkout',
            'cep' => '01310-100',
            'address' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => 'Apto 12',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);

        $this->actingAs($user)->withSession([
            'cart' => [
                'aurelio-chronograph' => [
                    'slug' => 'aurelio-chronograph',
                    'name' => 'Aurelio Chronograph',
                    'price' => 2890,
                    'quantity' => 1,
                    'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=900&q=80',
                ],
            ],
        ]);
        $this->get('/finalizar-compra')->assertOk();
        $checkoutToken = session('checkout_token');

        $response = $this->post('/finalizar-compra', [
            'name' => 'Cliente Checkout',
            'email' => 'checkout@royaltime.com.br',
            'cpf' => '529.982.247-25',
            'phone' => '(11) 99999-1234',
            'address_id' => $address->id,
            'payment_method' => 'pix',
            'checkout_token' => $checkoutToken,
        ]);

        $response->assertRedirect('/minha-conta/pedidos');
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'address_id' => $address->id,
            'payment_method' => 'pix',
        ]);
        $this->assertSame('52998224725', $user->refresh()->cpf);
        $this->assertSame(hash('sha256', '52998224725'), $user->cpf_hash);
        $order = Order::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Cliente Checkout', $order->customer_snapshot['name']);
        $this->assertSame('52998224725', $order->customer_snapshot['cpf']);
        $this->assertSame('RT-AC-2042', $order->items[0]['reference']);
        $this->assertSame(1, $order->items[0]['quantity']);
        $this->assertSame(5, Product::query()->where('slug', 'aurelio-chronograph')->value('stock'));
        $this->assertStringNotContainsString('52998224725', (string) DB::table('users')->where('id', $user->id)->value('cpf'));
        $this->assertStringNotContainsString('52998224725', (string) DB::table('orders')->where('id', $order->id)->value('customer_snapshot'));
        $this->assertEmpty(session('cart'));
    }

    public function test_checkout_rejects_an_invalid_cpf_without_creating_an_order(): void
    {
        $user = User::factory()->create();
        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Casa',
            'recipient_name' => $user->name,
            'cep' => '01310-100',
            'address' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => '',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);

        $this->actingAs($user)->withSession([
            'cart' => [
                'aurelio-chronograph' => ['slug' => 'aurelio-chronograph', 'quantity' => 1],
            ],
        ]);
        $this->get('/finalizar-compra')->assertOk();

        $response = $this->post('/finalizar-compra', [
            'name' => 'Cliente Inválido',
            'email' => $user->email,
            'cpf' => '111.111.111-11',
            'phone' => '(11) 99999-1234',
            'address_id' => $address->id,
            'payment_method' => 'pix',
            'checkout_token' => session('checkout_token'),
        ]);

        $response->assertSessionHasErrors(['cpf']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_displays_complete_buyer_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withSession([
            'cart' => [
                'aurelio-chronograph' => ['slug' => 'aurelio-chronograph', 'quantity' => 1],
            ],
        ])->get('/finalizar-compra');

        $response->assertOk();
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="cpf"', false);
        $response->assertSee('name="phone"', false);
    }

    public function test_customer_profile_can_store_validated_cpf_and_phone(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/minha-conta/perfil', [
            'name' => $user->name,
            'email' => $user->email,
            'cpf' => '529.982.247-25',
            'phone' => '(11) 99999-1234',
        ]);

        $response->assertRedirect('/minha-conta/perfil');
        $this->assertSame('52998224725', $user->refresh()->cpf);
        $this->assertSame('(11) 99999-1234', $user->phone);
    }

    public function test_customer_cannot_register_a_cpf_already_associated_with_another_account(): void
    {
        User::factory()->create([
            'cpf' => '52998224725',
            'cpf_hash' => hash('sha256', '52998224725'),
        ]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/minha-conta/perfil', [
            'name' => $user->name,
            'email' => $user->email,
            'cpf' => '529.982.247-25',
            'phone' => '(11) 99999-1234',
        ]);

        $response->assertSessionHasErrors(['cpf_hash']);
        $this->assertNull($user->refresh()->cpf);
    }

    public function test_repeating_a_checkout_token_does_not_create_another_order_or_reduce_stock_twice(): void
    {
        $user = User::factory()->create();
        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Casa',
            'recipient_name' => $user->name,
            'cep' => '01310-100',
            'address' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => '',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);
        $this->actingAs($user)->withSession([
            'cart' => ['aurelio-chronograph' => ['slug' => 'aurelio-chronograph', 'quantity' => 1]],
        ]);
        $this->get('/finalizar-compra')->assertOk();
        $token = session('checkout_token');
        $payload = [
            'name' => 'Cliente Checkout',
            'email' => $user->email,
            'cpf' => '529.982.247-25',
            'phone' => '(11) 99999-1234',
            'address_id' => $address->id,
            'payment_method' => 'pix',
            'checkout_token' => $token,
        ];

        $firstResponse = $this->post('/finalizar-compra', $payload);
        $firstResponse->assertRedirect('/minha-conta/pedidos');
        $this->withSession([
            'checkout_token' => $token,
            'cart' => ['aurelio-chronograph' => ['slug' => 'aurelio-chronograph', 'quantity' => 1]],
        ]);

        $secondResponse = $this->post('/finalizar-compra', $payload);

        $secondResponse->assertRedirect('/minha-conta/pedidos');
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(5, Product::query()->where('slug', 'aurelio-chronograph')->value('stock'));
    }

    public function test_admin_can_manage_catalog_and_access_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Royal',
            'email' => 'admin@royaltime.com.br',
            'is_admin' => true,
        ]);

        $dashboard = $this->actingAs($admin)->get('/admin');
        $dashboard->assertOk();
        $dashboard->assertSeeText('Admin');

        $response = $this->actingAs($admin)->post('/admin/produtos/aurelio-chronograph', [
            'name' => 'Aurelio Chronograph',
            'price' => 3190,
            'promo_price' => 2790,
            'stock' => 12,
            'technical' => [
                'Mecanismo' => 'Quartz Premium',
                'Material da caixa' => 'Aço inoxidável',
            ],
        ]);

        $response->assertRedirect('/admin/produtos');
        $this->assertSame(12, session('admin_catalog_overrides.aurelio-chronograph.stock'));
        $this->assertSame('Quartz Premium', session('admin_catalog_overrides.aurelio-chronograph.technical.Mecanismo'));
    }

    public function test_admin_can_remove_a_product_image_and_the_next_image_becomes_primary(): void
    {
        StoreController::catalogProducts();
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::query()->where('slug', 'aurelio-chronograph')->firstOrFail();
        $images = [
            'https://images.example.com/front.jpg',
            'https://images.example.com/detail.jpg',
            'https://images.example.com/wrist.jpg',
            'https://images.example.com/side.jpg',
        ];
        $product->update(['images' => $images, 'image' => $images[0]]);

        $response = $this->actingAs($admin)->post('/admin/produtos/aurelio-chronograph', [
            'gallery' => implode("\n", $images),
            'remove_gallery' => [$images[0]],
        ]);

        $response->assertRedirect('/admin/produtos');
        $this->assertSame([
            'https://images.example.com/detail.jpg',
            'https://images.example.com/wrist.jpg',
            'https://images.example.com/side.jpg',
        ], $product->refresh()->images);
        $this->assertSame('https://images.example.com/detail.jpg', $product->image);
    }

    public function test_admin_cannot_remove_an_image_if_fewer_than_three_would_remain(): void
    {
        StoreController::catalogProducts();
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::query()->where('slug', 'aurelio-chronograph')->firstOrFail();
        $images = [
            'https://images.example.com/front.jpg',
            'https://images.example.com/detail.jpg',
            'https://images.example.com/wrist.jpg',
        ];
        $product->update(['images' => $images, 'image' => $images[0]]);

        $response = $this->actingAs($admin)->post('/admin/produtos/aurelio-chronograph', [
            'gallery' => implode("\n", $images),
            'remove_gallery' => [$images[2]],
        ]);

        $response->assertSessionHasErrors(['gallery']);
        $this->assertSame($images, $product->refresh()->images);
    }

    public function test_admin_product_gallery_explains_image_removal_and_minimum_count(): void
    {
        StoreController::catalogProducts();
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/produtos');

        $response->assertOk();
        $response->assertSeeText('Marque apenas as imagens que deseja excluir.');
        $response->assertSeeText('Mantenha pelo menos 3 fotos');
        $response->assertSee('data-gallery-toggle', false);
        $response->assertSee('data-gallery-count', false);
    }
}
