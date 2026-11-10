@extends('layouts.storefront')

@section('title', 'Finalizar compra | Royal Time')

@section('content')
    <div class="container checkout-shell">
        <div class="checkout-layout">
            <section class="account-panel">
                <div class="panel-heading">
                    <h2>Finalizar compra</h2>
                </div>

                @if ($errors->any())
                    <div class="form-error-box">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('checkout.place') }}" class="account-form">
                    @csrf
                    <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">

                    <h3>Dados do comprador</h3>
                    <div class="field-grid">
                        <div>
                            <label for="name">Nome completo</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                        </div>
                        <div>
                            <label for="email">E-mail</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                        </div>
                        <div>
                            <label for="cpf">CPF</label>
                            <input id="cpf" type="text" name="cpf" value="{{ old('cpf', $user->cpf) }}" inputmode="numeric" autocomplete="off" placeholder="000.000.000-00" required>
                        </div>
                        <div>
                            <label for="phone">Telefone</label>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel" placeholder="(00) 00000-0000" required>
                        </div>
                    </div>

                    <h3>Endereço de entrega</h3>
                    <div class="field-grid">
                        @forelse ($addresses as $address)
                            <label class="checkout-address-option">
                                <input type="radio" name="address_id" value="{{ $address->id }}" {{ old('address_id', $selectedAddressId) == $address->id ? 'checked' : '' }} required>
                                <span>
                                    <strong>{{ $address->label }}</strong>
                                    <small>{{ $address->recipient_name }} • {{ $address->address }}, {{ $address->number }} • {{ $address->city }}/{{ $address->state }}</small>
                                </span>
                            </label>
                        @empty
                            <p class="muted-text">Nenhum endereço cadastrado. Cadastre um endereço antes de concluir a compra.</p>
                        @endforelse
                    </div>

                    @if ($addresses->isNotEmpty())
                        <div class="field-grid">
                            <div>
                                <label for="payment_method">Forma de pagamento</label>
                                <select id="payment_method" name="payment_method">
                                    <option value="cartao" {{ old('payment_method', 'cartao') === 'cartao' ? 'selected' : '' }}>Cartão de crédito</option>
                                    <option value="pix" {{ old('payment_method') === 'pix' ? 'selected' : '' }}>PIX</option>
                                    <option value="boleto" {{ old('payment_method') === 'boleto' ? 'selected' : '' }}>Boleto</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Confirmar pedido</button>
                        <p class="muted-text">O pedido ficará aguardando a confirmação do pagamento pelo meio de pagamento integrado.</p>
                    @else
                        <a href="{{ route('account.addresses') }}" class="btn btn-primary">Cadastrar endereço</a>
                    @endif
                </form>
            </section>

            <aside class="account-panel checkout-summary">
                <div class="panel-heading">
                    <h2>Resumo do pedido</h2>
                </div>

                <div class="checkout-summary__list">
                    @foreach ($items as $item)
                        <div class="checkout-summary__item">
                            <div>
                                <strong>{{ $item['name'] }}</strong>
                                <span class="checkout-summary__meta">Qtd. {{ $item['quantity'] }}</span>
                            </div>
                            <span>R$ {{ number_format($item['line_total'], 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="checkout-summary__line">
                    <span>Subtotal</span>
                    <strong>R$ {{ number_format($subtotal, 2, ',', '.') }}</strong>
                </div>
                <div class="checkout-summary__line">
                    <span>Frete</span>
                    <strong>R$ {{ number_format($shipping, 2, ',', '.') }}</strong>
                </div>
                <div class="checkout-summary__line checkout-summary__line--total">
                    <span>Total</span>
                    <strong>R$ {{ number_format($total, 2, ',', '.') }}</strong>
                </div>
            </aside>
        </div>
    </div>
@endsection
