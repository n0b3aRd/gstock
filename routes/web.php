<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesNoteController;
use App\Http\Controllers\ShopInventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransferNoteController;
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
    return redirect(\route('login'));
//    return Inertia::render('Welcome', [
//        'canLogin' => Route::has('login'),
//        'canRegister' => Route::has('register'),
//        'laravelVersion' => Application::VERSION,
//        'phpVersion' => PHP_VERSION,
//    ]);
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

    //transfer note
    Route::get('/tnote', [TransferNoteController::class, 'index'])->name('tnote.index');
    Route::get('/tnote/create', [TransferNoteController::class, 'create'])->name('tnote.create');
    Route::post('/tnote', [TransferNoteController::class, 'store'])->name('tnote.store');
    Route::get('/tnote/{tnote}/edit', [TransferNoteController::class, 'edit'])->name('tnote.edit');
    Route::put('/tnote/{tnote}', [TransferNoteController::class, 'update'])->name('tnote.update');
    Route::delete('/tnote/{tnote}', [TransferNoteController::class, 'destroy'])->name('tnote.destroy');

    //shop
    Route::get('/shop', [ShopInventoryController::class, 'index'])->name('shop.index');
    Route::get('/shop/create', [ShopInventoryController::class, 'create'])->name('shop.create');
    Route::post('/shop', [ShopInventoryController::class, 'store'])->name('shop.store');
    Route::get('/shop/{shop}/edit', [ShopInventoryController::class, 'edit'])->name('shop.edit');
    Route::put('/shop/{shop}', [ShopInventoryController::class, 'update'])->name('shop.update');
    Route::delete('/shop/{shop}', [ShopInventoryController::class, 'destroy'])->name('shop.destroy');

    //sales
    Route::get('/sales', [SalesNoteController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SalesNoteController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SalesNoteController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}/edit', [SalesNoteController::class, 'edit'])->name('sales.edit');
    Route::put('/sales/{sale}', [SalesNoteController::class, 'update'])->name('sales.update');
    Route::delete('/sales/{sale}', [SalesNoteController::class, 'destroy'])->name('sales.destroy');

    //suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
});


require __DIR__.'/auth.php';
