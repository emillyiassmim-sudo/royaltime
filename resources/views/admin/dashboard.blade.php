@extends('layouts.storefront')

@section('title', 'Admin | Royal Time')

@section('content')
    <div class="container admin-shell">
        <div class="admin-layout">
            <aside class="account-sidebar">
                <div class="section-label">Royal Time</div>
                <h1>Admin</h1>
                <nav class="account-nav">
                    <a href="{{ route('admin.dashboard') }}" class="is-active">Resumo</a>
                    <a href="{{ route('admin.products') }}">Produtos</a>
                    <a href="{{ route('admin.categories') }}">Categorias</a>
                    <a href="{{ route('admin.orders') }}">Pedidos</a>
                    <a href="{{ route('account.dashboard') }}">Minha conta</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="link-button">Sair</button>
                    </form>
                </nav>
            </aside>

            <main class="account-content">
                <div class="account-cards">
                    <div class="account-card">
                        <span class="account-card__label">Produtos</span>
                        <strong>{{ $productsCount }}</strong>
                        <small>Itens no catálogo</small>
                    </div>
                    <div class="account-card">
                        <span class="account-card__label">Clientes</span>
                        <strong>{{ $usersCount }}</strong>
                        <small>Usuários cadastrados</small>
                    </div>
                    <div class="account-card">
                        <span class="account-card__label">Pedidos</span>
                        <strong>{{ $ordersCount }}</strong>
                        <small>Compras registradas</small>
                    </div>
                    <div class="account-card">
                        <span class="account-card__label">Receita</span>
                        <strong>R$ {{ number_format($revenue, 2, ',', '.') }}</strong>
                        <small>Pedidos não cancelados; pagamentos não confirmados</small>
                    </div>
                    <div class="account-card">
                        <span class="account-card__label">Pendentes</span>
                        <strong>{{ $pendingOrdersCount }}</strong>
                        <small>Aguardando processamento</small>
                    </div>
                    <div class="account-card">
                        <span class="account-card__label">Estoque baixo</span>
                        <strong>{{ $lowStockCount }}</strong>
                        <small>Produtos com até 5 unidades</small>
                    </div>
                </div>

                <section class="account-panel">
                    <div class="panel-heading">
                        <h2>Pedidos recentes</h2>
                        <a href="{{ route('admin.orders') }}">Ver todos</a>
                    </div>

                    @if ($recentOrders->isEmpty())
                        <p class="muted-text">Ainda não há ordens registradas.</p>
                    @else
                        <div class="table-list">
                            @foreach ($recentOrders as $order)
                                <a href="{{ route('admin.orders', ['q' => $order->id]) }}" class="table-row">
                                    <span>#{{ $order->id }}</span>
                                    <span>{{ $order->customer_snapshot['name'] ?? $order->user->name ?? 'Cliente' }}</span>
                                    <span>{{ $order->status }}</span>
                                    <span>R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </main>
        </div>
    </div>
@endsection
