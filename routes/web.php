<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\LocaleController;
use App\Models\GroceryItem;
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
    Route::get('/groceries/create', fn () => view('groceries.create'))->name('groceries.create');
    Route::get('/groceries/{groceryItem}/edit', function (GroceryItem $groceryItem) {
        return view('groceries.edit', ['groceryItem' => $groceryItem]);
    })->name('groceries.edit');
});
