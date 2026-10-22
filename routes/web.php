<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CatalogMetadataController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'catalog'])->name('catalog');
Route::get('/catalog/{book}', [BookController::class, 'show'])->name('catalog.show');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest', 'throttle:6,1'])->name('login.store');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function (): void {
        Route::get('/', [DashboardController::class, 'admin'])->name('dashboard');
        Route::view('/profile', 'admin.profile')->name('profile');

        Route::get('/books', [BookController::class, 'index'])->name('books.index');
        Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
        Route::post('/books', [BookController::class, 'store'])->name('books.store');
        Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
        Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
        Route::patch('/books/{book}/archive', [BookController::class, 'archive'])->name('books.archive');

        Route::get('/catalog-data', [CatalogMetadataController::class, 'index'])->name('catalog-data.index');
        Route::post('/categories', [CatalogMetadataController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [CatalogMetadataController::class, 'updateCategory'])->name('categories.update');
        Route::post('/authors', [CatalogMetadataController::class, 'storeAuthor'])->name('authors.store');
        Route::put('/authors/{author}', [CatalogMetadataController::class, 'updateAuthor'])->name('authors.update');

        Route::get('/members', [MemberController::class, 'index'])->name('members.index');
        Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
        Route::post('/members', [MemberController::class, 'store'])->name('members.store');
        Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
        Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');

        Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
        Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
        Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
        Route::patch('/loans/{loan}/return', [LoanController::class, 'returnBook'])->name('loans.return');

        Route::get('/fines', [FineController::class, 'index'])->name('fines.index');
        Route::post('/loans/{loan}/payments', [FineController::class, 'storePayment'])->name('fines.payments.store');
    });

    Route::get('/my-account', [DashboardController::class, 'member'])
        ->middleware('role:member')->name('member.account');
});
