@extends('layouts.storefront')

@section('title', 'Meus dados | Royal Time')

@section('content')
    <div class="container account-shell">
        <div class="account-layout">
            <aside class="account-sidebar">
                <div class="section-label">Área do cliente</div>
                <h1>{{ $user->name }}</h1>
                <nav class="account-nav">
                    <a href="{{ route('account.dashboard') }}">Resumo</a>
                    <a href="{{ route('account.profile') }}" class="is-active">Meus dados</a>
                    <a href="{{ route('account.addresses') }}">Endereços</a>
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
                        <h2>Dados pessoais</h2>
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

                    <form method="POST" action="{{ route('account.profile.update') }}" class="account-form">
                        @csrf

                        <div class="field-grid">
                            <div>
                                <label for="name">Nome completo</label>
                                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div>
                                <label for="email">E-mail</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div>
                                <label for="cpf">CPF</label>
                                <input id="cpf" type="text" name="cpf" value="{{ old('cpf', $user->cpf) }}" inputmode="numeric" placeholder="000.000.000-00" required>
                            </div>
                            <div>
                                <label for="phone">Telefone</label>
                                <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel" placeholder="(00) 00000-0000" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">SALVAR ALTERAÇÕES</button>
                    </form>
                </section>
            </main>
        </div>
    </div>
@endsection
