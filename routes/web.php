<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //product category
    Route::get('/product-category', [ProductCategoryController::class, 'index'])->name('product-category.index');
    Route::get('/product-category/create', [ProductCategoryController::class, 'create'])->name('product-category.create');
    Route::post('/product-category', [ProductCategoryController::class, 'store'])->name('product-category.store');
    Route::get('/product-category/{product_category}/edit', [ProductCategoryController::class, 'edit'])->name('product-category.edit');
    Route::put('/product-category/{product_category}', [ProductCategoryController::class, 'update'])->name('product-category.update');
    Route::delete('/product-category/{product_category}', [ProductCategoryController::class, 'destroy'])->name('product-category.destroy');

    //inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    //grn
    Route::get('/grn', [GrnController::class, 'index'])->name('grn.index');
    Route::get('/grn/create', [GrnController::class, 'create'])->name('grn.create');
    Route::post('/grn', [GrnController::class, 'store'])->name('grn.store');
    Route::get('/grn/{grn}/edit', [GrnController::class, 'edit'])->name('grn.edit');
    Route::put('/grn/{grn}', [GrnController::class, 'update'])->name('grn.update');
    Route::delete('/grn/{grn}', [GrnController::class, 'destroy'])->name('grn.destroy');

});


require __DIR__.'/auth.php';
