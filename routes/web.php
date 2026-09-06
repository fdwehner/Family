<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\LocaleController;
use App\Models\GroceryProduct;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('groceries.index');
    }

    return view('welcome');
})->name('home');

Route::post('/locale/{locale}', [LocaleController::class, 'update'])
    ->name('locale.update');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/groceries', fn () => view('groceries.index'))->name('groceries.index');
    Route::get('/master-data/grocery-products', fn () => view('master-data.grocery-products.index'))
        ->name('master-data.grocery-products.index');
    Route::get('/master-data/grocery-products/create', fn () => view('master-data.grocery-products.create'))
        ->name('master-data.grocery-products.create');
    Route::get('/master-data/grocery-products/{groceryProduct}/edit', function (GroceryProduct $groceryProduct) {
        return view('master-data.grocery-products.edit', ['groceryProduct' => $groceryProduct]);
    })->name('master-data.grocery-products.edit');
});
