@extends('layouts.storefront')

@section('title', 'Produtos | Admin | Royal Time')

@section('content')
    <div class="container admin-shell">
        <div class="admin-layout">
            <aside class="account-sidebar">
                <div class="section-label">Royal Time</div>
                <h1>Admin</h1>
                <nav class="account-nav">
                    <a href="{{ route('admin.dashboard') }}">Resumo</a>
                    <a href="{{ route('admin.products') }}" class="is-active">Produtos</a>
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
                @if (session('success'))
                    <div class="form-success-box">{{ session('success') }}</div>
                @endif

                <section class="account-panel">
                    <div class="panel-heading">
                        <h2>Gestão de catálogo</h2>
                    </div>

                    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="admin-product-form admin-product-form--new js-admin-product-form">
                        @csrf
                        <div class="admin-product-grid">
                            <label>
                                <span>Nome</span>
                                <input type="text" name="name" placeholder="Ex.: Royal Crest Watch" required>
                            </label>

                            <label>
                                <span>Categoria</span>
                                <select name="category" required>
                                    @foreach (App\Http\Controllers\StoreController::productSections() as $key => $section)
                                        <option value="{{ $key }}">{{ $section['label'] }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span>Preço</span>
                                <input type="number" step="0.01" name="price" required>
                            </label>

                            <label>
                                <span>Promoção</span>
                                <input type="number" step="0.01" name="promo_price" placeholder="Opcional">
                            </label>

                            <label>
                                <span>Estoque</span>
                                <input type="number" min="0" name="stock" value="0" required>
                            </label>

                            <label>
                                <span>SKU</span>
                                <input type="text" name="reference" placeholder="RT-XXX-000">
                            </label>

                            <label>
                                <span>Cor</span>
                                <input type="text" name="color" placeholder="Preto, dourado...">
                            </label>

                            <label>
                                <span>Material / Strap</span>
                                <input type="text" name="strap" placeholder="Aço, couro, corrente...">
                            </label>
                        </div>

                        <label class="admin-field admin-field--full">
                            <span>Descrição</span>
                            <textarea name="description" rows="4" placeholder="Descreva o produto..."></textarea>
                        </label>

                        <label class="admin-field admin-field--full">
                            <span>Galeria (mínimo 3 URLs, uma por linha)</span>
                            <textarea name="gallery" rows="4" placeholder="https://...\nhttps://...\nhttps://..."></textarea>
                        </label>

                        <label class="admin-field admin-field--full admin-upload-field">
                            <span>Enviar imagens da galeria</span>
                            <input type="file" name="gallery_files[]" accept="image/*" multiple>
                        </label>

                        <div class="admin-product-specs">
                            @foreach (App\Http\Controllers\StoreController::technicalFieldDefinitions() as $category => $fields)
                                @foreach ($fields as $fieldKey => $fieldLabel)
                                    <label class="admin-technical-field" data-technical-field="{{ $fieldKey }}" {{ $category !== 'relogios' ? 'hidden' : '' }}>
                                        <span>{{ $fieldLabel }}</span>
                                        <input type="text" name="technical[{{ $fieldLabel }}]" placeholder="{{ $fieldLabel }}">
                                    </label>
                                @endforeach
                            @endforeach
                        </div>

                        <div class="admin-product-sections">
                            @foreach (App\Http\Controllers\StoreController::productSections() as $sectionKey => $section)
                                <label class="admin-section-option">
                                    <input type="checkbox" name="sections[]" value="{{ $sectionKey }}">
                                    <span>{{ $section['label'] }}</span>
                                </label>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary">Adicionar produto</button>
                    </form>

                    <div class="admin-product-list">
                        @foreach ($products as $product)
                            @php
                                $technicalFields = App\Http\Controllers\StoreController::technicalFieldsForProduct($product);
                            @endphp
                            <div class="admin-product-item">
                                <div class="admin-product-item__meta">
                                    <strong>{{ $product['name'] }}</strong>
                                    <span>{{ $product['reference'] ?? 'Sem referência' }}</span>
                                </div>

                                <form method="POST" action="{{ route('admin.products.update', ['slug' => $product['slug']]) }}" enctype="multipart/form-data" class="admin-product-form js-admin-product-form">
                                    @csrf

                                    <div class="admin-product-grid">
                                        <label>
                                            <span>Nome</span>
                                            <input type="text" name="name" value="{{ $product['name'] }}">
                                        </label>

                                        <label>
                                            <span>Código / SKU</span>
                                            <input type="text" name="reference" value="{{ $product['reference'] ?? '' }}">
                                        </label>

                                        <label>
                                            <span>Preço</span>
                                            <input type="number" step="0.01" name="price" value="{{ $product['price'] }}">
                                        </label>

                                        <label>
                                            <span>Promoção</span>
                                            <input type="number" step="0.01" name="promo_price" value="{{ $product['promo_price'] ?? '' }}" placeholder="Opcional">
                                        </label>

                                        <label>
                                            <span>Estoque</span>
                                            <input type="number" min="0" name="stock" value="{{ $product['stock'] ?? 0 }}">
                                        </label>

                                        <label>
                                            <span>Imagem principal — primeira da galeria</span>
                                            <input type="text" name="image" value="{{ $product['images'][0] ?? $product['image'] ?? '' }}" readonly data-gallery-primary>
                                        </label>

                                        <label class="admin-field admin-field--full">
                                            <span>Ordem das imagens — primeira foto será a principal</span>
                                            <textarea name="gallery" rows="3" placeholder="Uma URL por linha" data-gallery-input>{{ implode("\n", $product['images'] ?? []) }}</textarea>
                                        </label>

                                        <div class="admin-field admin-field--full admin-gallery-manager" data-gallery-manager>
                                            <div class="admin-gallery-manager__heading">
                                                <div>
                                                    <strong>Fotos cadastradas</strong>
                                                    <p>Marque apenas as imagens que deseja excluir. A primeira foto ativa será a principal.</p>
                                                </div>
                                                <span class="admin-gallery-count" data-gallery-count aria-live="polite"></span>
                                            </div>
                                            <p class="admin-gallery-warning" data-gallery-warning role="status" hidden>
                                                Mantenha pelo menos 3 fotos ou adicione novas imagens antes de salvar.
                                            </p>
                                            <div class="admin-gallery-list">
                                                @foreach ($product['images'] ?? [] as $image)
                                                    <div class="admin-gallery-item" data-gallery-card data-gallery-index="{{ $loop->iteration }}">
                                                        <img src="{{ $image }}" alt="{{ $product['name'] }} — foto {{ $loop->iteration }}" loading="lazy">
                                                        <div class="admin-gallery-item__details">
                                                            <span class="admin-gallery-item__position" data-gallery-position>
                                                                @if ($loop->first)
                                                                    Principal
                                                                @else
                                                                    Foto {{ $loop->iteration }}
                                                                @endif
                                                            </span>
                                                            <input type="checkbox" class="admin-gallery-remove-input" name="remove_gallery[]" value="{{ $image }}" data-gallery-remove>
                                                            <button type="button" class="admin-gallery-remove-button" data-gallery-toggle aria-pressed="false" aria-label="Marcar foto {{ $loop->iteration }} para exclusão">
                                                                <span aria-hidden="true">&times;</span>
                                                                <span data-gallery-toggle-label>Remover foto</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="admin-gallery-list admin-gallery-list--pending" data-gallery-upload-preview hidden></div>
                                        </div>

                                        <label class="admin-field admin-field--full admin-upload-field">
                                            <span>Adicionar fotos à galeria</span>
                                            <input type="file" name="gallery_files[]" accept="image/jpeg,image/png,image/webp" multiple data-gallery-upload>
                                            <small>Selecione arquivos JPG, PNG ou WEBP. Serão adicionados ao fim da galeria.</small>
                                        </label>

                                        <label>
                                            <span>Cor</span>
                                            <input type="text" name="color" value="{{ $product['color'] ?? '' }}">
                                        </label>

                                        <label>
                                            <span>Material / Strap</span>
                                            <input type="text" name="strap" value="{{ $product['strap'] ?? '' }}">
                                        </label>
                                    </div>

                                    <label class="admin-field admin-field--full">
                                        <span>Descrição</span>
                                        <textarea name="description" rows="4">{{ $product['description'] ?? '' }}</textarea>
                                    </label>

                                    <div class="admin-product-specs">
                                        @foreach (App\Http\Controllers\StoreController::technicalFieldDefinitions() as $categoryKey => $fields)
                                            @foreach ($fields as $fieldKey => $fieldLabel)
                                                <label class="admin-technical-field" data-technical-field="{{ $fieldKey }}" {{ ($product['category'] ?? 'relogios') !== $categoryKey ? 'hidden' : '' }}>
                                                    <span>{{ $fieldLabel }}</span>
                                                    <input type="text" name="technical[{{ $fieldLabel }}]" value="{{ $product['technical'][$fieldLabel] ?? $product['technical'][$fieldKey] ?? '' }}">
                                                </label>
                                            @endforeach
                                        @endforeach
                                    </div>

                                    <label>
                                        <span>Categoria</span>
                                        <select name="category">
                                            @foreach (App\Http\Controllers\StoreController::productSections() as $sectionKey => $section)
                                                <option value="{{ $sectionKey }}" {{ ($product['category'] ?? 'relogios') === $sectionKey ? 'selected' : '' }}>{{ $section['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <div class="admin-product-sections">
                                        @foreach (App\Http\Controllers\StoreController::productSections() as $sectionKey => $section)
                                            <label class="admin-section-option">
                                                <input type="checkbox" name="sections[]" value="{{ $sectionKey }}" {{ (in_array($sectionKey, $product['sections'] ?? [$product['category']] ?? [], true) || ($sectionKey === 'relogios' && in_array($product['category'] ?? '', ['clássicos', 'esportivos', 'dourados', 'prateados', 'relogios'], true))) ? 'checked' : '' }}>
                                                <span>{{ $section['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="admin-product-actions">
                                        <button type="submit" class="btn btn-secondary btn-secondary--small">Salvar</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('admin.products.delete', ['slug' => $product['slug']]) }}" onsubmit="return confirm('Deseja remover este produto?');" class="admin-product-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-secondary--small btn-danger">Excluir</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        const technicalDefinitions = @json(App\Http\Controllers\StoreController::technicalFieldDefinitions());

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-admin-product-form').forEach(function (form) {
                const galleryInput = form.querySelector('[data-gallery-input]');
                const galleryManager = form.querySelector('[data-gallery-manager]');
                const primaryImageInput = form.querySelector('[data-gallery-primary]');
                const removeToggles = Array.from(form.querySelectorAll('[data-gallery-toggle]'));
                const galleryUpload = form.querySelector('[data-gallery-upload]');
                const galleryCount = form.querySelector('[data-gallery-count]');
                const uploadPreview = form.querySelector('[data-gallery-upload-preview]');
                const galleryWarning = form.querySelector('[data-gallery-warning]');
                const saveButton = form.querySelector('button[type="submit"]');
                let previewUrls = [];

                const updateGalleryState = function () {
                    if (!galleryInput || !galleryManager) {
                        return;
                    }

                    const urls = Array.from(new Set(
                        galleryInput.value.split(/[\n,]+/).map(function (url) {
                            return url.trim();
                        }).filter(Boolean)
                    ));
                    const removedUrls = Array.from(galleryManager.querySelectorAll('[data-gallery-remove]:checked'))
                        .map(function (input) {
                            return input.value;
                        });
                    const remainingUrls = urls.filter(function (url) {
                        return !removedUrls.includes(url);
                    });
                    const selectedFiles = galleryUpload ? Array.from(galleryUpload.files || []) : [];
                    const imageCount = remainingUrls.length + selectedFiles.length;

                    galleryManager.querySelectorAll('[data-gallery-card]').forEach(function (card) {
                        const removeInput = card.querySelector('[data-gallery-remove]');
                        const toggle = card.querySelector('[data-gallery-toggle]');
                        const toggleLabel = card.querySelector('[data-gallery-toggle-label]');
                        const position = card.querySelector('[data-gallery-position]');
                        const isRemoved = Boolean(removeInput && removeInput.checked);
                        const urlIndex = remainingUrls.indexOf(removeInput ? removeInput.value : '');

                        card.classList.toggle('is-marked-for-removal', isRemoved);
                        toggle?.setAttribute('aria-pressed', String(isRemoved));

                        if (toggleLabel) {
                            toggleLabel.textContent = isRemoved ? 'Manter foto' : 'Remover foto';
                        }

                        if (toggle) {
                            const imageNumber = card.dataset.galleryIndex;
                            toggle.setAttribute(
                                'aria-label',
                                isRemoved
                                    ? 'Cancelar remoção da foto ' + imageNumber
                                    : 'Marcar foto ' + imageNumber + ' para exclusão'
                            );
                        }

                        if (position) {
                            position.textContent = isRemoved
                                ? 'Será excluída'
                                : (urlIndex === 0 ? 'Principal' : 'Foto ' + (urlIndex + 1));
                        }
                    });

                    if (primaryImageInput && remainingUrls.length > 0) {
                        primaryImageInput.value = remainingUrls[0];
                    } else if (primaryImageInput) {
                        primaryImageInput.value = selectedFiles.length > 0
                            ? 'A primeira nova foto será a principal'
                            : '';
                    }

                    if (galleryCount) {
                        galleryCount.textContent = imageCount + (imageCount === 1 ? ' foto após salvar' : ' fotos após salvar');
                    }

                    if (galleryWarning) {
                        galleryWarning.hidden = imageCount >= 3;
                    }

                    if (saveButton) {
                        saveButton.disabled = imageCount < 3;
                        saveButton.title = imageCount < 3 ? 'Mantenha pelo menos 3 fotos ou adicione novas imagens.' : '';
                    }

                    if (uploadPreview) {
                        previewUrls.forEach(URL.revokeObjectURL);
                        previewUrls = selectedFiles.map(function (file) {
                            return URL.createObjectURL(file);
                        });
                        const previewCards = selectedFiles.map(function (file, index) {
                            const card = document.createElement('div');
                            card.className = 'admin-gallery-item admin-gallery-item--pending';

                            const image = document.createElement('img');
                            image.src = previewUrls[index];
                            image.alt = 'Prévia da nova foto ' + (index + 1);

                            const caption = document.createElement('span');
                            caption.textContent = 'Nova foto ' + (index + 1);

                            card.append(image, caption);
                            return card;
                        });
                        uploadPreview.replaceChildren(...previewCards);
                        uploadPreview.hidden = selectedFiles.length === 0;
                    }
                };

                removeToggles.forEach(function (toggle) {
                    toggle.addEventListener('click', function () {
                        const card = toggle.closest('[data-gallery-card]');
                        const removeInput = card?.querySelector('[data-gallery-remove]');

                        if (removeInput) {
                            removeInput.checked = !removeInput.checked;
                            updateGalleryState();
                        }
                    });
                });

                galleryInput?.addEventListener('input', updateGalleryState);
                galleryUpload?.addEventListener('change', updateGalleryState);
                updateGalleryState();

                const categorySelect = form.querySelector('[name="category"]');
                const technicalFields = Array.from(form.querySelectorAll('[data-technical-field]'));

                if (!categorySelect || !technicalFields.length) {
                    return;
                }

                const updateTechnicalFields = function () {
                    const selectedCategory = categorySelect.value || 'relogios';
                    const allowedFields = Object.keys(technicalDefinitions[selectedCategory] || {});
                    const availableFields = new Set(allowedFields);

                    technicalFields.forEach(function (field) {
                        const fieldKey = field.dataset.technicalField;
                        const isVisible = availableFields.has(fieldKey);
                        field.hidden = !isVisible;

                        const input = field.querySelector('input');
                        if (input) {
                            input.disabled = !isVisible;
                        }
                    });
                };

                categorySelect.addEventListener('change', updateTechnicalFields);
                updateTechnicalFields();
            });
        });
    </script>
@endsection
