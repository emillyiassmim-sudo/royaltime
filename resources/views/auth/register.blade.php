@extends('layouts.storefront')

@section('title', 'Cadastro | Royal Time')

@section('content')
    <div class="auth-shell">
        <div class="auth-card">
            <div class="section-label">Cadastro</div>
            <h1>Criar uma conta</h1>

            @if ($errors->any())
                <div class="form-error-box">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf

                <label for="name">Nome completo</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name">

                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">

                <label for="password">Senha</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">

                <label for="password_confirmation">Confirmar senha</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">

                <button type="submit" class="btn btn-primary btn-primary--full">CRIAR CONTA</button>
            </form>

            <p class="auth-footer">
                Já possui conta?
                <a href="{{ route('login') }}" class="text-link">Entrar</a>
            </p>
        </div>
    </div>
@endsection
