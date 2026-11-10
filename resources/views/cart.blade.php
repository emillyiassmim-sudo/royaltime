@extends('layouts.storefront')

@section('title', 'Carrinho | Royal Time')

@section('content')
    <div class="container cart-page">
        <div class="cart-header">
            <div>
                <div class="section-label">Carrinho</div>
                <h1>Seu pedido</h1>
            </div>
            <a href="{{ route('catalog') }}" class="text-link">Continuar comprando</a>
        </div>

        @if (session('success'))
            <div class="flash-message" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="form-error-box" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if (empty($items))
            <div class="empty-cart">
                <h2>Seu carrinho está vazio.</h2>
                <p>Explore a coleção e escolha o relógio ideal para você.</p>
                <a href="{{ route('catalog') }}" class="btn btn-primary">VER CATÁLOGO</a>
            </div>
        @else
            <div class="cart-layout">
                <div class="cart-items">
                    @foreach ($items as $item)
                        <article class="cart-item">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">

                            <div class="cart-item__content">
                                <h2>{{ $item['name'] }}</h2>
                                <p>Preço unitário: <strong>R$ {{ number_format($item['price'], 2, ',', '.') }}</strong></p>

                                <div class="cart-item__controls">
                                    <form method="POST" action="{{ route('cart.update', ['slug' => $item['slug']]) }}">
                                        @csrf
                                        <label for="quantity-{{ $item['slug'] }}">Quantidade</label>
                                        <div class="cart-item__quantity">
                                            <input id="quantity-{{ $item['slug'] }}" type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ $item['stock'] }}" aria-label="Quantidade de {{ $item['name'] }}">
                                            <button type="submit" class="btn btn-secondary btn-secondary--small">Atualizar</button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('cart.remove', ['slug' => $item['slug']]) }}">
                                        @csrf
                                        <button type="submit" class="text-link text-link--danger">Remover</button>
                                    </form>
                                </div>
                            </div>

                            <div class="cart-item__total">
                                <span>Subtotal</span>
                                <strong>R$ {{ number_format($item['line_total'], 2, ',', '.') }}</strong>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="cart-summary">
                    <h2>Resumo do pedido</h2>
                    <div class="cart-summary__line">
                        <span>Subtotal</span>
                        <strong>R$ {{ number_format($subtotal, 2, ',', '.') }}</strong>
                    </div>
                    <div class="cart-summary__line">
                        <span>Frete</span>
                        <strong>Calcular na etapa seguinte</strong>
                    </div>
                    <div class="cart-summary__line cart-summary__line--total">
                        <span>Total</span>
                        <strong>R$ {{ number_format($total, 2, ',', '.') }}</strong>
                    </div>

                    @if (auth()->check())
                        <a href="{{ route('checkout') }}" class="btn btn-primary btn-primary--full">FINALIZAR PEDIDO</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-primary--full">ENTRAR PARA FINALIZAR</a>
                    @endif
                </aside>
            </div>
        @endif
    </div>
@endsection
