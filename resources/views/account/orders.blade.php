@extends('layouts.storefront')

@section('title', 'Pedidos | Royal Time')

@section('content')
    <div class="container account-shell">
        <div class="account-layout">
            <aside class="account-sidebar">
                <div class="section-label">Área do cliente</div>
                <h1>Pedidos</h1>
                <nav class="account-nav">
                    <a href="{{ route('account.dashboard') }}">Resumo</a>
                    <a href="{{ route('account.profile') }}">Meus dados</a>
                    <a href="{{ route('account.addresses') }}">Endereços</a>
                    <a href="{{ route('account.orders') }}" class="is-active">Pedidos</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="link-button">Sair</button>
                    </form>
                </nav>
            </aside>

            <main class="account-content">
                <section class="account-panel">
                    <div class="panel-heading">
                        <h2>Histórico de compras</h2>
                    </div>

                    @if ($orders->isEmpty())
                        <p class="muted-text">Você ainda não possui compras registradas.</p>
                    @else
                        <div class="table-list">
                            @foreach ($orders as $order)
                                <div class="table-row table-row--stacked">
                                    <span><strong>Pedido #{{ $order->id }}</strong></span>
                                    <span>{{ $order->status }}</span>
                                    <span>R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                                    <span>{{ $order->created_at->format('d/m/Y') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </main>
        </div>
    </div>
@endsection
