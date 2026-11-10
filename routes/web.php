<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('/catalogo', [StoreController::class, 'catalog'])->name('catalog');
Route::get('/produto/{slug}', [StoreController::class, 'product'])->name('product');
Route::get('/carrinho', [StoreController::class, 'cart'])->name('cart');
Route::post('/carrinho/adicionar/{slug}', [StoreController::class, 'addToCart'])->name('cart.add');
Route::post('/carrinho/remover/{slug}', [StoreController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/carrinho/atualizar/{slug}', [StoreController::class, 'updateCart'])->name('cart.update');

Route::middleware('guest')->group(function () {
    Route::get('/cadastro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/cadastro', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/recuperar-senha', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/recuperar-senha', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/redefinir-senha/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/redefinir-senha', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/finalizar-compra', [StoreController::class, 'checkout'])->name('checkout');
    Route::post('/finalizar-compra', [StoreController::class, 'placeOrder'])->name('checkout.place');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/minha-conta', [CustomerController::class, 'dashboard'])->name('account.dashboard');
    Route::get('/minha-conta/perfil', [CustomerController::class, 'profile'])->name('account.profile');
    Route::post('/minha-conta/perfil', [CustomerController::class, 'updateProfile'])->name('account.profile.update');
    Route::get('/minha-conta/enderecos', [CustomerController::class, 'addresses'])->name('account.addresses');
    Route::post('/minha-conta/enderecos', [CustomerController::class, 'storeAddress'])->name('account.addresses.store');
    Route::get('/minha-conta/pedidos', [CustomerController::class, 'orders'])->name('account.orders');

    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/produtos', [AdminController::class, 'products'])->name('admin.products');
    Route::post('/admin/produtos', [AdminController::class, 'storeProduct'])->name('admin.products.store');
    Route::post('/admin/produtos/{slug}', [AdminController::class, 'updateProduct'])->name('admin.products.update');
    Route::delete('/admin/produtos/{slug}', [AdminController::class, 'deleteProduct'])->name('admin.products.delete');
    Route::get('/admin/categorias', [CategoryController::class, 'index'])->name('admin.categories');
    Route::post('/admin/categorias', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/admin/categorias/{category:slug}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/admin/categorias/{category:slug}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    Route::get('/admin/pedidos', [OrderController::class, 'index'])->name('admin.orders');
    Route::patch('/admin/pedidos/{order}', [OrderController::class, 'update'])->name('admin.orders.update');
    Route::get('/admin/pedidos/{order}/pdf', [OrderController::class, 'download'])->name('admin.orders.pdf');
});
