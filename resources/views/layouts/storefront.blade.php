<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Royal Time — relojoaria de luxo com peças sob medida para homens que valorizam elegância e presença.">
        <title>@yield('title', 'Royal Time')</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="topbar">
            <div class="container topbar__inner">
                <span>Entrega para todo o Brasil</span>
                <span>Compra segura</span>
                <span>Atendimento especializado</span>
            </div>
        </div>

        <header class="site-header">
            <div class="container header__inner">
                <button class="nav-toggle" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="main-navigation" data-nav-toggle>
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <nav class="main-nav" id="main-navigation" data-nav-menu>
                    <a href="{{ route('home') }}">Início</a>
                    <a href="{{ route('catalog') }}">Relógios</a>
                    <a href="{{ route('home') }}#colecoes">Coleções</a>
                    <a href="{{ route('home') }}#sobre">Sobre nós</a>
                    <a href="#contato">Contato</a>
                </nav>

                <a href="{{ route('home') }}" class="brand" aria-label="Royal Time home">
                    <span class="brand__word">ROYAL</span>
                    <span class="brand__word">TIME</span>
                </a>

                <div class="header-actions">
                    <button type="button" aria-label="Buscar produtos" aria-expanded="false" aria-controls="header-search" class="icon-button" data-search-toggle>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.5 3a7.5 7.5 0 0 1 5.88 12.8l4.42 4.42 1.41-1.41-4.42-4.42A7.5 7.5 0 1 1 10.5 3Zm0 2a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11Z"/></svg>
                    </button>
                    @auth
                        @if (auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" aria-label="Painel admin" class="icon-button">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16v2H4zm2-2h2V8H6zm5 0h2V4h-2zm5 0h2v-6h-2z"/></svg>
                            </a>
                        @endif
                        <a href="{{ route('account.dashboard') }}" aria-label="Minha conta" class="icon-button">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" aria-label="Minha conta" class="icon-button">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/></svg>
                        </a>
                    @endauth
                    <a href="{{ route('cart') }}" aria-label="Carrinho" class="icon-button cart-button">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 18a2 2 0 1 0 2 2 2 2 0 0 0-2-2Zm10 0a2 2 0 1 0 2 2 2 2 0 0 0-2-2ZM6.2 6h14.08l-1.2 8.81a2 2 0 0 1-2 1.69H9.04a2 2 0 0 1-2-1.69L5.4 4.5H2V2h4.2l.65 3.5Zm2.9 10.5h8.11l1.04-7.5H7.57Z"/></svg>
                        <span>0</span>
                    </a>
                </div>

                <form method="GET" action="{{ route('catalog') }}" class="header-search" id="header-search" data-search-panel hidden>
                    <label class="sr-only" for="header-search-input">Buscar produtos</label>
                    <input type="search" id="header-search-input" name="q" value="{{ request('q') }}" placeholder="Buscar relógios">
                    <button type="submit" class="btn btn-primary btn-primary--compact">Buscar</button>
                </form>
            </div>
        </header>

        <main>@yield('content')</main>

        <footer class="site-footer" id="contato">
            <div class="container footer-grid">
                <div>
                    <a href="{{ route('home') }}" class="brand brand--footer" aria-label="Royal Time home">
                        <span class="brand__word">ROYAL</span>
                        <span class="brand__word">TIME</span>
                    </a>
                    <p>
                        Coleções de relógios masculinos com design refinado, precisão e presença atemporal.
                    </p>
                </div>

                <div>
                    <h3>Sobre a marca</h3>
                    <ul>
                        <li><a href="{{ route('home') }}#sobre">Nossa história</a></li>
                        <li><a href="{{ route('catalog') }}">Catálogo</a></li>
                        <li><a href="{{ route('home') }}#colecoes">Coleções</a></li>
                    </ul>
                </div>

                <div>
                    <h3>Políticas</h3>
                    <ul>
                        <li><a href="#">Política de privacidade</a></li>
                        <li><a href="#">Política de trocas e devoluções</a></li>
                        <li><a href="#">Termos de uso</a></li>
                    </ul>
                </div>

                <div>
                    <h3>Contato</h3>
                    <ul>
                        <li><a href="mailto:atendimento@royaltime.com.br">atendimento@royaltime.com.br</a></li>
                        <li><a href="tel:+551155556666">+55 (11) 5555-6666</a></li>
                        <li><a href="https://instagram.com" target="_blank" rel="noreferrer">Instagram</a></li>
                        <li><a href="https://wa.me/551155556666" target="_blank" rel="noreferrer">WhatsApp</a></li>
                    </ul>
                </div>
            </div>

            <div class="container footer-meta">
                <span>Formas de pagamento</span>
                <span>Cartões, boleto e Pix</span>
                <span>Entrega em todo o Brasil</span>
            </div>
        </footer>
    </body>
</html>
