<?php

namespace Tests\Feature;

use App\Http\Controllers\StoreController;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class AdminOrdersTest extends TestCase
{
    public function test_admin_can_view_order_details_and_customer_information(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        $order = $customer->orders()->create([
            'status' => 'pendente',
            'total' => 1019.90,
            'shipping' => 149.90,
            'payment_method' => 'pix',
            'items' => [[
                'name' => 'Relógio de demonstração',
                'reference' => 'RT-TEST-01',
                'category_label' => 'Relógios clássicos',
                'color' => 'Prateado',
                'strap' => 'Aço',
                'image' => 'https://images.example.com/watch.jpg',
                'quantity' => 2,
                'price' => 435,
                'line_total' => 870,
                'technical' => ['Mecanismo' => 'Quartz'],
            ]],
            'customer_snapshot' => [
                'name' => 'Cliente Pedido',
                'email' => 'pedido@example.com',
                'cpf' => '52998224725',
                'phone' => '(11) 99999-1234',
                'address' => [
                    'recipient_name' => 'Cliente Pedido',
                    'cep' => '01310-100',
                    'address' => 'Avenida Paulista',
                    'number' => '1000',
                    'complement' => 'Apto 12',
                    'neighborhood' => 'Bela Vista',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                ],
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders'));

        $response->assertOk();
        $response->assertSeeText('Relógio de demonstração');
        $response->assertSee('alt="Relógio de demonstração"', false);
        $response->assertSeeText('RT-TEST-01');
        $response->assertSeeText('Qtd. 2');
        $response->assertSeeText('52998224725');
        $response->assertSeeText('Avenida Paulista');
        $response->assertSee(route('admin.orders.pdf', $order), false);
    }

    public function test_non_admin_cannot_view_admin_orders(): void
    {
        $customer = User::factory()->create();

        $response = $this->actingAs($customer)->get(route('admin.orders'));

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_download_or_update_an_order(): void
    {
        $customer = User::factory()->create();
        $order = $customer->orders()->create([
            'status' => 'pendente',
            'total' => 500,
            'shipping' => 0,
            'items' => [],
        ]);

        $downloadResponse = $this->actingAs($customer)->get(route('admin.orders.pdf', $order));
        $downloadResponse->assertForbidden();

        $updateResponse = $this->patch(route('admin.orders.update', $order), [
            'status' => 'entregue',
        ]);

        $updateResponse->assertForbidden();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pendente',
        ]);
    }

    public function test_unauthenticated_customer_is_redirected_from_admin_order_download(): void
    {
        $customer = User::factory()->create();
        $order = $customer->orders()->create([
            'status' => 'pendente',
            'total' => 500,
            'shipping' => 0,
            'items' => [],
        ]);

        $response = $this->get(route('admin.orders.pdf', $order));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_update_order_status_without_marking_payment_as_confirmed(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $admin->orders()->create([
            'status' => 'pendente',
            'total' => 500,
            'shipping' => 0,
            'items' => [],
            'payment_method' => 'pix',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
            'status' => 'em_preparacao',
        ]);

        $response->assertRedirect(route('admin.orders'));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'em_preparacao',
            'payment_method' => 'pix',
        ]);
    }

    public function test_admin_cannot_set_payment_as_confirmed_through_order_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $admin->orders()->create([
            'status' => 'pendente',
            'total' => 500,
            'shipping' => 0,
            'items' => [],
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
            'status' => 'pago',
        ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pendente',
        ]);
    }

    public function test_admin_can_download_a_valid_pdf_with_order_items(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $admin->orders()->create([
            'status' => 'pendente',
            'total' => 250,
            'shipping' => 0,
            'items' => [[
                'name' => 'Watch PDF',
                'reference' => 'RT-PDF-10',
                'quantity' => 1,
                'price' => 250,
                'line_total' => 250,
            ]],
            'customer_snapshot' => [
                'name' => 'Comprador PDF',
                'email' => 'pdf@example.com',
                'cpf' => '52998224725',
                'phone' => '(11) 99999-1234',
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.pdf', $order));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition', 'attachment; filename="pedido-'.$order->id.'.pdf"');
        $response->assertSee('%PDF-1.4', false);
        $response->assertSee('Watch PDF', false);
        $response->assertSee('RT-PDF-10', false);
        $this->assertStringContainsString('%%EOF', $response->getContent());
    }

    public function test_cancelling_a_pending_order_restores_stock_only_once(): void
    {
        StoreController::catalogProducts();
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $admin->orders()->create([
            'status' => 'pendente',
            'total' => 5780,
            'shipping' => 0,
            'items' => [[
                'slug' => 'aurelio-chronograph',
                'name' => 'Aurelio Chronograph',
                'quantity' => 2,
                'price' => 2890,
                'line_total' => 5780,
            ]],
        ]);

        $firstResponse = $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
            'status' => 'cancelado',
        ]);
        $firstResponse->assertRedirect(route('admin.orders'));
        $this->assertSame(8, Product::query()->where('slug', 'aurelio-chronograph')->value('stock'));

        $secondResponse = $this->patch(route('admin.orders.update', $order), [
            'status' => 'cancelado',
        ]);

        $secondResponse->assertRedirect(route('admin.orders'));
        $this->assertSame(8, Product::query()->where('slug', 'aurelio-chronograph')->value('stock'));
    }
}
