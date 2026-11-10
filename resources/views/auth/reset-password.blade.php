@extends('layouts.storefront')

@section('title', 'Redefinir senha | Royal Time')

@section('content')
    <div class="auth-shell">
        <div class="auth-card">
            <div class="section-label">Segurança</div>
            <h1>Redefinir senha</h1>

            @if ($errors->any())
                <div class="form-error-box">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="auth-form">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ old('email', $email) }}">

                <label for="password">Nova senha</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">

                <label for="password_confirmation">Confirmar nova senha</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">

                <button type="submit" class="btn btn-primary btn-primary--full">ALTERAR SENHA</button>
            </form>
        </div>
    </div>
@endsection
