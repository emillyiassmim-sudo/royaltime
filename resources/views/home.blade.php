@extends('layouts.storefront')

@section('title', 'Royal Time | Relojoaria masculina')

@section('content')
    <section class="hero" aria-label="Banner principal da Royal Time">
        <div class="container hero__content">
            <p class="eyebrow">A ARTE DE VALORIZAR O TEMPO.</p>
            <h1>Elegância que não precisa de apresentação.</h1>
            <p class="hero__text">Descubra uma seleção de relógios para homens que apreciam cada detalhe.</p>
            <div class="hero__actions">
                <a href="{{ route('catalog') }}" class="btn btn-primary">EXPLORAR COLEÇÃO</a>
            </div>
        </div>
    </section>

    <section class="categories-section container" id="colecoes">
        <div class="section-heading">
            <div>
                <div class="section-label">Nossa curadoria</div>
                <h2>Coleções atemporais</h2>
            </div>
        </div>
        <div class="category-grid">
            @foreach ($categories as $category)
                <a href="{{ route('catalog', ['category' => $category['slug']]) }}" class="category-card-link">
                    <article class="category-card">
                        <div class="category-card__visual">
                            @if ($category['image_url'])
                                <img src="{{ $category['image_url'] }}" alt="" loading="lazy">
                            @endif
                        </div>
                        <h3>{{ $category['name'] }}</h3>
                    </article>
                </a>
            @endforeach
        </div>
    </section>

    <section class="featured-section container">
        <div class="section-heading">
            <div>
                <div class="section-label">Destaques</div>
                <h2>Seleção em evidência</h2>
            </div>
            <a href="{{ route('catalog') }}" class="text-link">Ver catálogo completo</a>
        </div>

        <div class="product-grid">
            @foreach ($featuredProducts as $product)
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
    </section>

    @foreach ($categorySections as $sectionKey => $section)
        @if (!empty($sectionProducts[$sectionKey]))
            <section class="featured-section container">
                <div class="section-heading">
                    <div>
                        <div class="section-label">{{ $section['label'] }}</div>
                        <h2>{{ $section['label'] }}</h2>
                    </div>
                    <a href="{{ route('catalog', ['category' => $sectionKey]) }}" class="text-link">Ver mais</a>
                </div>

                <div class="product-grid">
                    @foreach ($sectionProducts[$sectionKey] as $product)
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
            </section>
        @endif
    @endforeach

    <section class="story-section" id="sobre">
        <div class="container story-layout">
            <div class="story-copy">
                <div class="section-label">A marca</div>
                <h2>ROYAL TIME — O TEMPO É UMA ASSINATURA.</h2>
                <p>
                    Mais do que marcar as horas, um relógio representa personalidade, presença e estilo.
                    A Royal Time reúne peças selecionadas para homens que valorizam a elegância em cada detalhe.
                </p>
            </div>
            <div class="story-visual" aria-hidden="true"></div>
        </div>
    </section>
@endsection
