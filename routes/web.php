<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;


Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\{DashboardController, ProductController,  PurchaseController, SaleController, AdjustmentController};


 
Route::get('/', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::resource('products', controller: ProductController::class)->middleware('auth');
Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
Route::post('adjustments', [AdjustmentController::class, 'store'])->name('adjustments.store');


Route::get('/login', [LoginController::class,'show'])->name('login');
Route::post('/login', [LoginController::class,'login']);
Route::post('/logout', [LoginController::class,'logout'])->name('logout');


Route::get('/register', [RegisterController::class,'show'])->name('register');
Route::post('/register', [RegisterController::class,'store']);



Route::middleware('auth')->group(function(){
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('products', ProductController::class);
Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
Route::post('adjustments', [AdjustmentController::class, 'store'])->name('adjustments.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/reports/stock',  [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('/reports/low',    [ReportController::class, 'low'])->name('reports.low');
    Route::get('/reports/sales',  [ReportController::class, 'sales'])->name('reports.sales');
});


Route::get('/products/{product}/labels', function(\App\Models\Product $product){
    return view('products.labels', compact('product'));
})->middleware('auth')->name('products.labels');


Route::resource('products', ProductController::class)->only([
  'index','create','store','edit','update','destroy'
]);


Route::get('/pos', [SaleController::class,'create'])->name('pos');


 

Route::middleware(['auth','role:Admin'])->group(function () {
    Route::get('/users',        [UserController::class,'index'])->name('users.index');
    Route::get('/users/create', [UserController::class,'create'])->name('users.create');
    Route::post('/users',       [UserController::class,'store'])->name('users.store');
});



// Only Admin can access users:
Route::middleware(['auth','role:Admin'])->group(function () {
    Route::resource('users', UserController::class)->only(['index','create','store']);
});

// Only Manager or Admin can access purchases:
Route::post('purchases', [PurchaseController::class, 'store'])
    ->middleware(['auth','role:Admin|Manager'])
    ->name('purchases.store');

// Only Cashier or above can create sales:
Route::post('sales', [SaleController::class, 'store'])
    ->middleware(['auth','role:Admin|Manager|Cashier'])
    ->name('sales.store');


    Route::middleware('auth')->group(function () {
    Route::resource('sales', SaleController::class)->only(['index','create','store']);
    Route::get('/pos', [SaleController::class, 'create'])->name('pos')->middleware('auth');
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store')->middleware('auth');
});



Route::resource('categories', CategoryController::class)->middleware('auth');
Route::resource('units', UnitController::class)->middleware('auth');


// Quick-create endpoints that ALWAYS return JSON
Route::post('/categories/quick', [CategoryController::class, 'quickStore'])->middleware('auth')->name('categories.quick');
Route::post('/units/quick', [UnitController::class, 'quickStore'])->middleware('auth')->name('units.quick');

 

Route::post('/categories/quick-back', [CategoryController::class, 'quickStoreBack'])
    ->middleware('auth')->name('categories.quick-back');

Route::post('/units/quick-back', [UnitController::class, 'quickStoreBack'])
    ->middleware('auth')->name('units.quick-back');