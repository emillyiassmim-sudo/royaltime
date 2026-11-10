@extends('layouts.storefront')

@section('title', $product['name'] . ' | Royal Time')

@section('content')
    <div class="container product-page">
        <nav class="breadcrumb" aria-label="Navegação do produto">
            <a href="{{ route('home') }}">Início</a>
            <span>/</span>
            <a href="{{ route('catalog', ['category' => $product['category']]) }}">{{ $product['category_label'] }}</a>
            <span>/</span>
            <span>{{ $product['name'] }}</span>
        </nav>

        <section class="product-layout" data-product-gallery>
            <div class="gallery-panel">
                <div class="gallery-stage">
                    <button type="button" class="gallery-nav gallery-nav--prev" data-gallery-prev aria-label="Imagem anterior">
                        <span>‹</span>
                    </button>
                    <img src="{{ $product['images'][0] ?? $product['image'] }}" alt="{{ $product['name'] }}" data-gallery-main>
                    <button type="button" class="gallery-nav gallery-nav--next" data-gallery-next aria-label="Próxima imagem">
                        <span>›</span>
                    </button>
                </div>

                <div class="thumbnail-list" aria-label="Miniaturas do produto">
                    @foreach ($product['images'] as $index => $image)
                        <button type="button" class="thumbnail {{ $index === 0 ? 'is-active' : '' }}" data-gallery-thumb data-image="{{ $image }}" aria-label="Ver imagem {{ $index + 1 }}">
                            <img src="{{ $image }}" alt="{{ $product['name'] }} - imagem {{ $index + 1 }}">
                        </button>
                    @endforeach
                </div>
            </div>

            <aside class="product-summary">
                <div class="section-label">{{ $product['category_label'] }}</div>
                <h1>{{ $product['name'] }}</h1>
                <div class="product-reference">Ref. {{ $product['reference'] }}</div>

                <div class="product-price-row">
                    @if ($product['promo_price'])
                        <span class="product-price product-price--old">R$ {{ number_format($product['price'], 2, ',', '.') }}</span>
                        <span class="product-price product-price--promo">R$ {{ number_format($product['promo_price'], 2, ',', '.') }}</span>
                    @else
                        <span class="product-price">R$ {{ number_format($product['price'], 2, ',', '.') }}</span>
                    @endif
                </div>

                <div class="product-installments">{{ $product['installments'] }}</div>

                <div class="product-stock {{ $product['stock'] > 0 ? 'is-available' : 'is-unavailable' }}">
                    {{ $product['stock'] > 0 ? 'Disponível em estoque' : 'Indisponível no momento' }}
                </div>

                <p class="product-description">{{ $product['description'] }}</p>

                <div class="product-actions">
                    <form method="POST" action="{{ route('cart.add', ['slug' => $product['slug']]) }}">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-primary">ADICIONAR AO CARRINHO</button>
                    </form>

                    <form method="POST" action="{{ route('cart.add', ['slug' => $product['slug']]) }}">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-secondary">COMPRAR AGORA</button>
                    </form>
                </div>

                <div class="product-share">
                    <span>Compartilhar</span>
                    <button type="button" class="text-link" data-share-product>Copiar link</button>
                </div>

                <div class="spec-grid">
                    @foreach ($product['technical'] as $label => $value)
                        <div class="spec-item">
                            <span>{{ $label }}</span>
                            <strong>{{ $value }}</strong>
                        </div>
                    @endforeach
                </div>
            </aside>
        </section>

        <section class="related-section">
            <div class="section-heading">
                <div>
                    <div class="section-label">Produtos relacionados</div>
                    <h2>VOCÊ TAMBÉM PODE GOSTAR</h2>
                </div>
            </div>

            <div class="product-grid product-grid--catalog">
                @foreach ($relatedProducts as $related)
                    <article class="product-card">
                        <div class="product-card__media">
                            <img src="{{ $related['image'] }}" alt="{{ $related['name'] }}" loading="lazy">
                            <span class="product-card__badge">{{ $related['badge'] }}</span>
                        </div>
                        <div class="product-card__body">
                            <div class="product-card__meta">
                                <span>{{ $related['category_label'] }}</span>
                                <span>{{ $related['color'] }}</span>
                            </div>
                            <h3>{{ $related['name'] }}</h3>
                            <div class="product-card__price-row">
                                @if ($related['promo_price'])
                                    <span class="product-card__price product-card__price--old">R$ {{ number_format($related['price'], 2, ',', '.') }}</span>
                                    <span class="product-card__price product-card__price--promo">R$ {{ number_format($related['promo_price'], 2, ',', '.') }}</span>
                                @else
                                    <span class="product-card__price">R$ {{ number_format($related['price'], 2, ',', '.') }}</span>
                                @endif
                            </div>
                            <div class="product-card__actions">
                                <a href="{{ route('product', ['slug' => $related['slug']]) }}" class="btn btn-secondary">Visualizar</a>
                                <form method="POST" action="{{ route('cart.add', ['slug' => $related['slug']]) }}">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary btn-primary--compact">Comprar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>

    <div class="product-modal" data-product-modal aria-hidden="true">
        <div class="product-modal__backdrop" data-close-modal></div>
        <div class="product-modal__content" role="dialog" aria-modal="true" aria-label="Visualização ampliada do produto">
            <button type="button" class="product-modal__close" data-close-modal aria-label="Fechar visualização">×</button>
            <div class="product-modal__viewport">
                <img src="{{ $product['images'][0] ?? $product['image'] }}" alt="{{ $product['name'] }} ampliado" data-modal-image>
            </div>
            <div class="product-modal__controls">
                <button type="button" class="btn btn-secondary" data-zoom-out>-</button>
                <button type="button" class="btn btn-secondary" data-zoom-in>+</button>
            </div>
        </div>
    </div>
@endsection
