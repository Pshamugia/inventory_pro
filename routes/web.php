<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\{
    DashboardController, ProductController, PurchaseController, SaleController,
    AdjustmentController, ReportController, CategoryController, UnitController, UserController
};

 

Route::get('/login',    [LoginController::class,'show'])->name('login');
Route::post('/login',   [LoginController::class,'login']);
Route::post('/logout',  [LoginController::class,'logout'])->name('logout');

Route::get('/register', [RegisterController::class,'show'])->name('register');
Route::post('/register',[RegisterController::class,'store']);

Route::middleware('auth')->group(function () {
  // Dashboard
  Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

  // Common data (both see products, but Cashier is read-only in UI)
  Route::resource('products', ProductController::class)->only(['index','show','create','store','edit','update','destroy']);
  Route::resource('categories', CategoryController::class);
  Route::resource('units', UnitController::class);

  // Reports (admins only will see link in UI; route still allowed for all auth — optional tighten below)
  Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
  Route::get('/reports/low',   [ReportController::class, 'low'])->name('reports.low');
  Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');

  // POS / Sales — Cashier or above
  Route::middleware('role:Admin|Manager|Cashier')->group(function () {
    Route::get('/pos',  [SaleController::class,'create'])->name('pos');
    Route::post('/sales',[SaleController::class,'store'])->name('sales.store');
  });




  // Inventory ops — Manager or Admin
  Route::middleware('role:Admin|Manager')->group(function () {
    Route::post('/purchases',  [PurchaseController::class, 'store'])->name('purchases.store');
    Route::post('/adjustments',[AdjustmentController::class,'store'])->name('adjustments.store');
  });

  // User management — Admin only
  Route::middleware('role:Admin')->group(function () {
    Route::resource('users', UserController::class)->only(['index','create','store','edit','update','destroy']);
  });
});

Route::middleware(['auth','role:Admin|Manager|Cashier'])->group(function () {
    Route::get('/pos',            [SaleController::class, 'create'])->name('pos');          // POS screen
    Route::get('/pos/search',     [SaleController::class, 'searchProducts'])->name('pos.search'); // AJAX search
    Route::post('/pos/sale',      [SaleController::class, 'storeAjax'])->name('pos.sale');  // AJAX save sale
});