@extends('layouts.storefront')

@section('title', 'Login | Royal Time')

@section('content')
    <div class="auth-shell">
        <div class="auth-card">
            <div class="section-label">Acesso</div>
            <h1>Entrar na sua conta</h1>

            @if ($errors->any())
                <div class="form-error-box">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="form-success-box">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">

                <label for="password">Senha</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">

                <div class="auth-row">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="remember" value="1">
                        <span>Lembrar-me</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-link">Esqueci a senha</a>
                </div>

                <button type="submit" class="btn btn-primary btn-primary--full">ENTRAR</button>
            </form>

            <p class="auth-footer">
                Ainda não tem conta?
                <a href="{{ route('register') }}" class="text-link">Criar conta</a>
            </p>
        </div>
    </div>
@endsection
