<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Support\OrderPdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    private const STATUSES = [
        'pendente',
        'em_preparacao',
        'enviado',
        'entregue',
        'cancelado',
    ];

    public function index(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito ao painel administrativo.');

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $orders = Order::query()
            ->with(['user:id,name,email,phone', 'address'])
            ->when(
                $validated['status'] ?? null,
                fn ($query, string $status) => $query->where('status', $status)
            )
            ->when($validated['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    if (ctype_digit($search)) {
                        $query->where('id', (int) $search);
                    }

                    $query->orWhereHas('user', function ($query) use ($search): void {
                        $query->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
                });
            })
            ->latest('created_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders', [
            'orders' => $orders,
            'statuses' => self::STATUSES,
            'selectedStatus' => $validated['status'] ?? '',
            'search' => $validated['q'] ?? '',
        ]);
    }

    public function update(Request $request, Order $order)
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito ao painel administrativo.');

        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        DB::transaction(function () use ($order, $validated): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status === 'cancelado' && $validated['status'] !== 'cancelado') {
                throw ValidationException::withMessages([
                    'status' => 'Um pedido cancelado não pode ser reaberto.',
                ]);
            }

            if ($validated['status'] === 'cancelado' && in_array($lockedOrder->status, ['enviado', 'entregue'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pedidos enviados ou entregues não podem ser cancelados pelo painel.',
                ]);
            }

            if ($validated['status'] === 'cancelado' && $lockedOrder->status !== 'cancelado') {
                foreach ($lockedOrder->items ?? [] as $item) {
                    if (empty($item['slug']) || empty($item['quantity'])) {
                        continue;
                    }

                    Product::query()
                        ->where('slug', $item['slug'])
                        ->lockForUpdate()
                        ->increment('stock', (int) $item['quantity']);
                }
            }

            $lockedOrder->update(['status' => $validated['status']]);
        });

        return redirect()->route('admin.orders')->with('success', 'Status do pedido atualizado.');
    }

    public function download(Request $request, Order $order, OrderPdf $orderPdf): Response
    {
        abort_unless($request->user()?->is_admin, 403, 'Acesso restrito ao painel administrativo.');

        return response($orderPdf->render($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="pedido-'.$order->id.'.pdf"',
        ]);
    }
}
