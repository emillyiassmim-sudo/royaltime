@extends('layouts.storefront')

@section('title', 'Categorias | Admin | Royal Time')

@section('content')
    <div class="container admin-shell">
        <div class="admin-layout">
            <aside class="account-sidebar">
                <div class="section-label">Royal Time</div>
                <h1>Admin</h1>
                <nav class="account-nav">
                    <a href="{{ route('admin.dashboard') }}">Resumo</a>
                    <a href="{{ route('admin.products') }}">Produtos</a>
                    <a href="{{ route('admin.categories') }}" class="is-active">Categorias</a>
                    <a href="{{ route('admin.orders') }}">Pedidos</a>
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
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <section class="account-panel">
                    <div class="panel-heading">
                        <h2>Nova coleção</h2>
                    </div>

                    <form method="POST" action="{{ route('admin.categories.store') }}" class="admin-product-form">
                        @csrf
                        <div class="admin-product-grid">
                            <label>
                                <span>Nome da coleção</span>
                                <input type="text" name="name" value="{{ old('name') }}" maxlength="100" required>
                            </label>

                            <label>
                                <span>Identificador (opcional)</span>
                                <input type="text" name="slug" value="{{ old('slug') }}" placeholder="Gerado a partir do nome">
                            </label>

                            <label>
                                <span>Tipo de produto relacionado</span>
                                <select name="product_group" required>
                                    @foreach ($productGroups as $key => $group)
                                        <option value="{{ $key }}" {{ old('product_group', 'relogios') === $key ? 'selected' : '' }}>{{ $group['label'] }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span>Ordem de exibição</span>
                                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="65535" required>
                            </label>

                            <label class="admin-field admin-field--full">
                                <span>Imagem da coleção (URL)</span>
                                <input type="url" name="image_url" value="{{ old('image_url') }}" placeholder="https://...">
                            </label>
                        </div>

                        <label class="admin-section-option">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>Exibir na página inicial</span>
                        </label>

                        <button type="submit" class="btn btn-primary">Criar coleção</button>
                    </form>
                </section>

                <section class="account-panel">
                    <div class="panel-heading">
                        <h2>Coleções cadastradas</h2>
                    </div>

                    @forelse ($categories as $category)
                        <div class="admin-product-item">
                            <div class="admin-product-item__meta">
                                <strong>{{ $category->name }}</strong>
                                <span>{{ $category->is_active ? 'Visível na loja' : 'Oculta da loja' }} · {{ $productGroups[$category->product_group]['label'] ?? $category->product_group }}</span>
                            </div>

                            <form method="POST" action="{{ route('admin.categories.update', ['category' => $category->slug]) }}" class="admin-product-form">
                                @csrf
                                @method('PUT')

                                <div class="admin-product-grid">
                                    <label>
                                        <span>Nome da coleção</span>
                                        <input type="text" name="name" value="{{ $category->name }}" maxlength="100" required>
                                    </label>

                                    <label>
                                        <span>Identificador</span>
                                        <input type="text" name="slug" value="{{ $category->slug }}" required>
                                    </label>

                                    <label>
                                        <span>Tipo de produto relacionado</span>
                                        <select name="product_group" required>
                                            @foreach ($productGroups as $key => $group)
                                                <option value="{{ $key }}" {{ $category->product_group === $key ? 'selected' : '' }}>{{ $group['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label>
                                        <span>Ordem de exibição</span>
                                        <input type="number" name="sort_order" value="{{ $category->sort_order }}" min="0" max="65535" required>
                                    </label>

                                    <label class="admin-field admin-field--full">
                                        <span>Imagem da coleção (URL)</span>
                                        <input type="url" name="image_url" value="{{ $category->image_url }}" placeholder="https://...">
                                    </label>
                                </div>

                                <label class="admin-section-option">
                                    <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}>
                                    <span>Exibir na página inicial</span>
                                </label>

                                <div class="admin-product-actions">
                                    <button type="submit" class="btn btn-secondary btn-secondary--small">Salvar coleção</button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('admin.categories.destroy', ['category' => $category->slug]) }}" onsubmit="return confirm('Deseja remover esta coleção?');" class="admin-product-delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-secondary--small btn-danger">Remover</button>
                            </form>
                        </div>
                    @empty
                        <p class="muted-text">Nenhuma coleção cadastrada.</p>
                    @endforelse
                </section>
            </main>
        </div>
    </div>
@endsection
