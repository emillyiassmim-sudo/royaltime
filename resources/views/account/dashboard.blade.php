@extends('layouts.storefront')

@section('title', 'Minha conta | Royal Time')

@section('content')
    <div class="container account-shell">
        <div class="account-layout">
            <aside class="account-sidebar">
                <div class="section-label">Área do cliente</div>
                <h1>Olá, {{ $user->name }}</h1>

                <nav class="account-nav">
                    <a href="{{ route('account.dashboard') }}" class="is-active">Resumo</a>
                    <a href="{{ route('account.profile') }}">Meus dados</a>
                    <a href="{{ route('account.addresses') }}">Endereços</a>
                    <a href="{{ route('account.orders') }}">Pedidos</a>
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

                <div class="account-cards">
                    <div class="account-card">
                        <span class="account-card__label">Pedidos</span>
                        <strong>{{ $orders->count() }}</strong>
                        <small>Últimas compras</small>
                    </div>
                    <div class="account-card">
                        <span class="account-card__label">Endereços</span>
                        <strong>{{ $addresses->count() }}</strong>
                        <small>Cadastros salvos</small>
                    </div>
                </div>

                <section class="account-panel">
                    <div class="panel-heading">
                        <h2>Últimos pedidos</h2>
                    </div>

                    @if ($orders->isEmpty())
                        <p class="muted-text">Você ainda não realizou nenhuma compra.</p>
                    @else
                        <div class="table-list">
                            @foreach ($orders as $order)
                                <div class="table-row">
                                    <span>#{{ $order->id }}</span>
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
