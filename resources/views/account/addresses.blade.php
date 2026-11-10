@extends('layouts.storefront')

@section('title', 'Endereços | Royal Time')

@section('content')
    <div class="container account-shell">
        <div class="account-layout">
            <aside class="account-sidebar">
                <div class="section-label">Área do cliente</div>
                <h1>Endereços</h1>
                <nav class="account-nav">
                    <a href="{{ route('account.dashboard') }}">Resumo</a>
                    <a href="{{ route('account.profile') }}">Meus dados</a>
                    <a href="{{ route('account.addresses') }}" class="is-active">Endereços</a>
                    <a href="{{ route('account.orders') }}">Pedidos</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="link-button">Sair</button>
                    </form>
                </nav>
            </aside>

            <main class="account-content">
                <section class="account-panel">
                    <div class="panel-heading">
                        <h2>Adicionar endereço</h2>
                    </div>

                    @if (session('success'))
                        <div class="form-success-box">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="form-error-box">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('account.addresses.store') }}" class="account-form">
                        @csrf

                        <div class="field-grid">
                            <div>
                                <label for="label">Apelido</label>
                                <input id="label" type="text" name="label" value="{{ old('label') }}" placeholder="Casa, escritório, etc." required>
                            </div>
                            <div>
                                <label for="recipient_name">Nome do destinatário</label>
                                <input id="recipient_name" type="text" name="recipient_name" value="{{ old('recipient_name') }}" required>
                            </div>
                            <div>
                                <label for="cep">CEP</label>
                                <input id="cep" type="text" name="cep" value="{{ old('cep') }}" required>
                            </div>
                            <div>
                                <label for="city">Cidade</label>
                                <input id="city" type="text" name="city" value="{{ old('city') }}" required>
                            </div>
                            <div class="field-span-2">
                                <label for="address">Endereço</label>
                                <input id="address" type="text" name="address" value="{{ old('address') }}" required>
                            </div>
                            <div>
                                <label for="number">Número</label>
                                <input id="number" type="text" name="number" value="{{ old('number') }}" required>
                            </div>
                            <div>
                                <label for="complement">Complemento</label>
                                <input id="complement" type="text" name="complement" value="{{ old('complement') }}">
                            </div>
                            <div>
                                <label for="neighborhood">Bairro</label>
                                <input id="neighborhood" type="text" name="neighborhood" value="{{ old('neighborhood') }}" required>
                            </div>
                            <div>
                                <label for="state">Estado</label>
                                <input id="state" type="text" name="state" value="{{ old('state') }}" maxlength="2" placeholder="SP" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">SALVAR ENDEREÇO</button>
                    </form>
                </section>

                @if ($addresses->isNotEmpty())
                    <section class="account-panel">
                        <div class="panel-heading">
                            <h2>Endereços cadastrados</h2>
                        </div>

                        <div class="address-list">
                            @foreach ($addresses as $address)
                                <div class="address-item">
                                    <strong>{{ $address->label }}</strong>
                                    <p>{{ $address->recipient_name }}</p>
                                    <p>{{ $address->address }}, {{ $address->number }}</p>
                                    <p>{{ $address->neighborhood }} - {{ $address->city }}/{{ $address->state }}</p>
                                    <p>CEP: {{ $address->cep }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </main>
        </div>
    </div>
@endsection
