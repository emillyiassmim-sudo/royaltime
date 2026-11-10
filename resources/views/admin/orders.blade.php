@extends('layouts.storefront')

@section('title', 'Pedidos | Admin | Royal Time')

@section('content')
    <div class="container admin-shell">
        <div class="admin-layout">
            <aside class="account-sidebar">
                <div class="section-label">Royal Time</div>
                <h1>Admin</h1>
                <nav class="account-nav">
                    <a href="{{ route('admin.dashboard') }}">Resumo</a>
                    <a href="{{ route('admin.products') }}">Produtos</a>
                    <a href="{{ route('admin.categories') }}">Categorias</a>
                    <a href="{{ route('admin.orders') }}" class="is-active">Pedidos</a>
                    <a href="{{ route('account.dashboard') }}">Minha conta</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="link-button">Sair</button>
                    </form>
                </nav>
            </aside>

            <main class="account-content">
                @if (session('success'))
                    <div class="form-success-box">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="form-error-box" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="account-panel">
                    <div class="panel-heading">
                        <h2>Pedidos da loja</h2>
                    </div>
                    <form method="GET" action="{{ route('admin.orders') }}" class="field-grid">
                        <div>
                            <label for="q">Buscar por pedido, cliente ou e-mail</label>
                            <input id="q" type="search" name="q" value="{{ $search }}">
                        </div>
                        <div>
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="">Todos</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" {{ $selectedStatus === $status ? 'selected' : '' }}>
                                        {{ str($status)->replace('_', ' ')->title() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-span-2">
                            <button type="submit" class="btn btn-primary">FILTRAR PEDIDOS</button>
                        </div>
                    </form>
                </section>

                @forelse ($orders as $order)
                    @php
                        $snapshot = $order->customer_snapshot ?? [];
                        $customer = $snapshot ?: [
                            'name' => $order->user?->name,
                            'email' => $order->user?->email,
                            'cpf' => $order->user?->cpf,
                            'phone' => $order->user?->phone,
                        ];
                        $address = $snapshot['address'] ?? [
                            'recipient_name' => $order->address?->recipient_name,
                            'cep' => $order->address?->cep,
                            'address' => $order->address?->address,
                            'number' => $order->address?->number,
                            'complement' => $order->address?->complement,
                            'neighborhood' => $order->address?->neighborhood,
                            'city' => $order->address?->city,
                            'state' => $order->address?->state,
                        ];
                    @endphp
                    <section class="account-panel">
                        <div class="panel-heading">
                            <div>
                                <h2>Pedido #{{ $order->id }}</h2>
                                <p>{{ $order->created_at->format('d/m/Y H:i') }} · {{ $customer['name'] ?? 'Cliente' }}</p>
                            </div>
                            <a class="btn btn-secondary" href="{{ route('admin.orders.pdf', $order) }}">BAIXAR PDF</a>
                        </div>

                        <div class="table-list">
                            @forelse ($order->items ?? [] as $item)
                                <div class="table-row table-row--stacked">
                                    <span class="admin-order-item__product">
                                        @if (! empty($item['image']))
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] ?? 'Produto' }}">
                                        @endif
                                        <strong>{{ $item['name'] ?? 'Produto' }}</strong>
                                    </span>
                                    <span>Ref. {{ $item['reference'] ?? 'N/I' }}</span>
                                    <span>Qtd. {{ $item['quantity'] ?? 0 }}</span>
                                    <span>Unitário R$ {{ number_format((float) ($item['price'] ?? 0), 2, ',', '.') }}</span>
                                    <span>Subtotal R$ {{ number_format((float) ($item['line_total'] ?? 0), 2, ',', '.') }}</span>
                                    <span>{{ $item['category_label'] ?? 'Categoria não informada' }} · {{ $item['color'] ?? 'Cor não informada' }} · {{ $item['strap'] ?? 'Pulseira não informada' }}</span>
                                    @if (! empty($item['technical']))
                                        <span>
                                            @foreach ($item['technical'] as $label => $value)
                                                {{ $label }}: {{ $value }}@if (! $loop->last); @endif
                                            @endforeach
                                        </span>
                                    @endif
                                    @if (! empty($item['description']))
                                        <span>{{ $item['description'] }}</span>
                                    @endif
                                </div>
                            @empty
                                <p class="muted-text">Este pedido não possui itens detalhados salvos.</p>
                            @endforelse
                        </div>

                        <div class="field-grid">
                            <div>
                                <h3>Dados do cliente</h3>
                                <p>{{ $customer['name'] ?? 'Não informado' }}</p>
                                <p>{{ $customer['email'] ?? 'Não informado' }}</p>
                                <p>CPF: {{ $customer['cpf'] ?? 'Não informado' }}</p>
                                <p>Telefone: {{ $customer['phone'] ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <h3>Entrega e pagamento</h3>
                                <p>{{ $address['recipient_name'] ?? 'Não informado' }}</p>
                                <p>{{ $address['address'] ?? '' }}, {{ $address['number'] ?? '' }} {{ $address['complement'] ?? '' }}</p>
                                <p>{{ $address['neighborhood'] ?? '' }} · {{ $address['city'] ?? '' }}/{{ $address['state'] ?? '' }}</p>
                                <p>CEP: {{ $address['cep'] ?? 'Não informado' }}</p>
                                <p>Forma: {{ strtoupper($order->payment_method ?? 'não informada') }} — aguardando confirmação do pagamento</p>
                            </div>
                        </div>

                        <div class="checkout-summary__line">
                            <span>Frete</span>
                            <strong>R$ {{ number_format((float) $order->shipping, 2, ',', '.') }}</strong>
                        </div>
                        <div class="checkout-summary__line checkout-summary__line--total">
                            <span>Total do pedido</span>
                            <strong>R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong>
                        </div>

                        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="field-grid">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="status-{{ $order->id }}">Atualizar status</label>
                                <select id="status-{{ $order->id }}" name="status" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>
                                            {{ str($status)->replace('_', ' ')->title() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field-span-2">
                                <button type="submit" class="btn btn-primary">SALVAR STATUS</button>
                            </div>
                        </form>
                    </section>
                @empty
                    <section class="account-panel">
                        <p class="muted-text">Nenhum pedido encontrado.</p>
                    </section>
                @endforelse

                {{ $orders->links() }}
            </main>
        </div>
    </div>
@endsection
