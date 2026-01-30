<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\{
    DashboardController,
    ProductController,
    PurchaseController,
    SaleController,
    AdjustmentController,
    ReportController,
    CategoryController,
    UnitController,
    UserController,
    ReceiptPrinterController
};

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::get('/login',    [LoginController::class, 'show'])->name('login');
Route::post('/login',   [LoginController::class, 'login']);
Route::post('/logout',  [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Authenticated Area
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    | Dashboard
    */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    | Products / Categories / Units
    | (UI already hides buttons for Cashier)
    */
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitController::class);

    Route::post('/categories/quick-back', [CategoryController::class, 'quickStoreBack'])
        ->name('categories.quick-back');

    Route::post('/units/quick-back', [UnitController::class, 'storeQuickBack'])
        ->name('units.quick-back');

    /*
    | Reports (visible only to Admin / Manager in UI)
    */
    Route::get('/reports/stock',  [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('/reports/low',    [ReportController::class, 'low'])->name('reports.low');
    Route::get('/reports/sales',  [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/today',  [ReportController::class, 'todaySummary'])->name('reports.today');
    Route::get('/reports/export/today', [ReportController::class, 'exportTodayCsv'])
        ->name('reports.export.today');

    /*
    |--------------------------------------------------------------------------
    | POS (Admin / Manager / Cashier)
    |--------------------------------------------------------------------------
    */
    Route::get('/pos', function () {
        $u = auth()->user();

        abort_unless(
            $u && (
                $u->hasRole('Admin') ||
                $u->hasRole('Manager') ||
                $u->hasRole('Cashier')
            ),
            403
        );

        return app(SaleController::class)->create();
    })->name('pos');

    Route::get('/pos/search', [SaleController::class, 'searchProducts'])
        ->name('pos.search');

    Route::post('/pos/sale', [SaleController::class, 'storeAjax'])
        ->name('pos.sale');

    Route::post('/sales', [SaleController::class, 'store'])
        ->name('sales.store');

    Route::post('/pos/print', [ReceiptPrinterController::class, 'printAndOpen'])
        ->name('pos.print');

    Route::get('/pos/customers', [SaleController::class, 'customers'])
        ->name('pos.customers');

    Route::get('/reports/cash-today', [SaleController::class, 'todaySummary'])
        ->name('reports.cash.today');

    Route::post('/pos/open-drawer', [SaleController::class, 'openDrawer'])
        ->name('pos.openDrawer');

    Route::get('/reports/z', [SaleController::class, 'zReport'])->name('reports.z');

    Route::post('/pos/close-day', [SaleController::class, 'closeDay'])->name('pos.closeDay');

    Route::post('/pos/reopen-day', [SaleController::class, 'reopenDay'])->name('pos.reopenDay');



    /*
    |--------------------------------------------------------------------------
    | Inventory Ops (Admin / Manager)
    |--------------------------------------------------------------------------
    */
    Route::post('/purchases', function (\Illuminate\Http\Request $r) {
        abort_unless(
            auth()->user()?->hasRole('Admin') ||
                auth()->user()?->hasRole('Manager'),
            403
        );

        return app(PurchaseController::class)->store(
            $r,
            app(\App\Services\InventoryService::class)
        );
    })->name('purchases.store');

    Route::post('/adjustments', function (\Illuminate\Http\Request $r) {
        abort_unless(
            auth()->user()?->hasRole('Admin') ||
                auth()->user()?->hasRole('Manager'),
            403
        );

        return app(AdjustmentController::class)->store(
            $r,
            app(\App\Services\InventoryService::class)
        );
    })->name('adjustments.store');

    /*
    |--------------------------------------------------------------------------
    | Users (ADMIN ONLY)
    |--------------------------------------------------------------------------
    */
    Route::resource('users', UserController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->missing(fn() => abort(404));

    Route::get('/users', function () {
        abort_unless(auth()->user()?->hasRole('Admin'), 403);
        return app(UserController::class)->index();
    })->name('users.index');
});
