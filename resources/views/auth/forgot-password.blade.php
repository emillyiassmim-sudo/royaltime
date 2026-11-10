@extends('layouts.storefront')

@section('title', 'Recuperar senha | Royal Time')

@section('content')
    <div class="auth-shell">
        <div class="auth-card">
            <div class="section-label">Recuperação</div>
            <h1>Recuperar senha</h1>

            @if (session('status'))
                <div class="form-success-box">{{ session('status') }}</div>
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

            <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                @csrf

                <label for="email">E-mail cadastrado</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>

                <button type="submit" class="btn btn-primary btn-primary--full">ENVIAR LINK</button>
            </form>
        </div>
    </div>
@endsection
