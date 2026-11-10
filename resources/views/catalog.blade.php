@extends('layouts.storefront')

@section('title', 'Royal Time | Catálogo')

@section('content')
    <section class="catalog-banner">
        <div class="container catalog-banner__inner">
            <div>
                <div class="section-label section-label--light">Coleção completa</div>
                <h1>Relógios para homens que valorizam presença e elegância.</h1>
                <p class="catalog-banner__subtitle">Uma seleção de relógios masculinos com presença, sofisticação e personalidade.</p>
            </div>
        </div>
    </section>

    <section class="catalog container">
        <aside class="filters" aria-label="Filtros do catálogo">
            <form method="GET" action="{{ route('catalog') }}" class="filter-form">
                <div class="filter-group">
                    <label for="category">Categoria</label>
                    <select id="category" name="category">
                        <option value="">Todas</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['slug'] }}" {{ $selectedCategory === $category['slug'] ? 'selected' : '' }}>
                                {{ $category['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="range">Faixa de preço</label>
                    <select id="range" name="range">
                        <option value="">Todos os preços</option>
                        <option value="0-1500" {{ $selectedRange === '0-1500' ? 'selected' : '' }}>Até R$ 1.500</option>
                        <option value="1500-3000" {{ $selectedRange === '1500-3000' ? 'selected' : '' }}>R$ 1.500 a R$ 3.000</option>
                        <option value="3000-4500" {{ $selectedRange === '3000-4500' ? 'selected' : '' }}>R$ 3.000 a R$ 4.500</option>
                        <option value="4500-6000" {{ $selectedRange === '4500-6000' ? 'selected' : '' }}>R$ 4.500 a R$ 6.000</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="color">Cor</label>
                    <select id="color" name="color">
                        <option value="">Todas</option>
                        @foreach ($colorOptions as $color)
                            <option value="{{ $color }}" {{ $selectedColor === $color ? 'selected' : '' }}>{{ $color }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="strap">Pulseira</label>
                    <select id="strap" name="strap">
                        <option value="">Todas</option>
                        @foreach ($strapOptions as $strap)
                            <option value="{{ $strap }}" {{ $selectedStrap === $strap ? 'selected' : '' }}>{{ $strap }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sort">Ordenar por</label>
                    <select id="sort" name="sort">
                        <option value="featured" {{ $selectedSort === 'featured' ? 'selected' : '' }}>Destaques</option>
                        <option value="price_asc" {{ $selectedSort === 'price_asc' ? 'selected' : '' }}>Menor preço</option>
                        <option value="price_desc" {{ $selectedSort === 'price_desc' ? 'selected' : '' }}>Maior preço</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-primary--full">Aplicar filtros</button>
                    <a href="{{ route('catalog') }}" class="btn btn-secondary btn-secondary--full">Limpar</a>
                </div>
            </form>
        </aside>

        <div class="catalog-results">
            <div class="catalog-summary">
                <p>
                    {{ count($products) }} produtos encontrados
                    @if ($selectedQuery !== '')
                        para “{{ $selectedQuery }}”
                    @endif
                </p>
            </div>

            @if ($products)
                <div class="product-grid product-grid--catalog">
                    @foreach ($products as $product)
                        <article class="product-card">
                            <div class="product-card__media">
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                                <span class="product-card__badge">{{ $product['badge'] }}</span>
                            </div>
                            <div class="product-card__body">
                                <div class="product-card__meta">
                                    <span>{{ $product['category_label'] }}</span>
                                    <span>{{ $product['color'] }}</span>
                                </div>
                                <h3>{{ $product['name'] }}</h3>
                                <div class="product-card__price-row">
                                    @if ($product['promo_price'])
                                        <span class="product-card__price product-card__price--old">R$ {{ number_format($product['price'], 2, ',', '.') }}</span>
                                        <span class="product-card__price product-card__price--promo">R$ {{ number_format($product['promo_price'], 2, ',', '.') }}</span>
                                    @else
                                        <span class="product-card__price">R$ {{ number_format($product['price'], 2, ',', '.') }}</span>
                                    @endif
                                </div>
                                <div class="product-card__installments">{{ $product['installments'] }}</div>
                                <div class="product-card__actions">
                                    <a href="{{ route('product', ['slug' => $product['slug']]) }}" class="btn btn-secondary">Visualizar</a>
                                    <form method="POST" action="{{ route('cart.add', ['slug' => $product['slug']]) }}">
                                        @csrf
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary btn-primary--compact">Comprar</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <h2>Nenhum item encontrado</h2>
                    <p>Tente ajustar os filtros e explore outras categorias de acessórios masculinos.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
