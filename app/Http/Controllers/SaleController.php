<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SaleController extends Controller
{
    // --- POS screen ---
    public function create()
    {
        // A tiny starter set to render buttons quickly (latest 12 products)
        $starter = Product::orderByDesc('id')->take(12)->get(['id', 'name', 'sku', 'sale_price']);
        return view('pos.cashier', compact('starter'));
    }

    // --- Search products (AJAX) ---
    public function searchProducts(Request $request)
    {
        $q = trim($request->get('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $list = Product::query()
            ->when($q, function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->take(25)
            ->get(['id', 'name', 'sku', 'sale_price']);

        // If you have Product::stockOnHand(), use it. Otherwise compute fast here.
        // Example using stock_movements.qty_change (positive in, negative out):
        $ids = $list->pluck('id');
        $stockMap = DB::table('stock_movements')
            ->select('product_id', DB::raw('COALESCE(SUM(qty_change),0) as soh'))
            ->whereIn('product_id', $ids)
            ->groupBy('product_id')
            ->pluck('soh', 'product_id');

        $out = $list->map(function ($p) use ($stockMap) {
            return [
                'id'    => $p->id,
                'name'  => $p->name,
                'sku'   => $p->sku,
                'price' => (float)$p->sale_price,
                'soh'   => (int)($stockMap[$p->id] ?? 0),
            ];
        });

        return response()->json($out);
    }

    // --- Save sale (AJAX) ---
    public function storeAjax(Request $request): JsonResponse
    {

    $license = app(\App\Services\LicenseService::class)->checkNow();

if (!$license['ok']) {
    return response()->json([
        'ok' => false,
        'error' => 'License inactive: ' . ($license['reason'] ?? 'unknown')
    ], 403);
}


        $today = now()->toDateString();

        $drawer = \App\Models\CashDrawer::where('user_id', auth()->id())
            ->where('business_date', $today)
            ->whereNull('closed_at')
            ->first();

        if (!$drawer) {
            return response()->json([
                'ok' => false,
                'error' => 'Day is closed (or not opened). Please Open Day first.'
            ], 403);
        }



        $data = $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer|exists:products,id',
            'items.*.qty'          => 'required|numeric|min:0.001',
            'items.*.price'        => 'required|numeric|min:0',
            'payment_method'       => 'required|string|max:50',
            'cash_given'           => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        DB::beginTransaction();
        try {
            $sale = new Sale();

            $sale->payment_method = $data['payment_method'];
            $sale->cash_given     = (float) ($data['cash_given'] ?? 0);
            $sale->reference      = 'S-' . now()->format('YmdHis') . '-' . random_int(100, 999);
            $sale->sold_at        = now();
            $sale->change_due     = 0;   // temporary, recalculated later
            $sale->total          = 0;
            $sale->customer_id = $data['customer_id'] ?? null;


            $sale->save();


            $sale->save();

            $total = 0;

            foreach ($data['items'] as $row) {
                $productId = (int) $row['product_id'];
                $qty       = (float) $row['qty'];
                $price     = (float) $row['price'];

                // Compute current stock directly from stock_movements.qty_change
                $soh = (int) DB::table('stock_movements')
                    ->where('product_id', $productId)
                    ->sum('qty_change');

                if ($soh < $qty) {
                    throw new \Exception('Not enough stock for ' . Product::find($productId)->name);
                }

                $warehouseId = DB::table('warehouses')->value('id');

                if (!$warehouseId) {
                    throw new \Exception('No warehouse found');
                }

                $line = $qty * $price;
                $total += $line;
                SaleItem::create([
                    'sale_id'      => $sale->id,
                    'product_id'   => $productId,
                    'warehouse_id' => $warehouseId,
                    'qty'          => $qty,
                    'unit_price'   => $price,
                    'line_total'   => $line,
                ]);


                // consume stock
                StockMovement::create([
                    'product_id'     => $productId,
                    'warehouse_id'   => 1,         // set your default warehouse id if you use it
                    'qty_change'     => -$qty,     // NEGATIVE on sale
                    'reason'         => 'sale',
                    'reference_type' => 'Sale',
                    'reference_id'   => $sale->id,
                ]);
            }

            $sale->total = $total;
            $sale->change_due = max(0, $sale->cash_given - $total);
            $sale->save();


            DB::commit();

            return response()->json([
                'ok'             => true,
                'sale_id'        => $sale->id,
                'total'          => (float) $total,
                'time'           => now()->format('Y-m-d H:i'),
                'payment_method' => $sale->payment_method,
                'cash_given' => (float) $sale->cash_given,
                'change'     => (float) $sale->change_due,

            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function customers(Request $request)
    {
        $q = trim($request->get('q', ''));

        return \App\Models\Customer::query()
            ->when(
                $q,
                fn($qq) =>
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
            )
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'phone']);
    }



    public function todaySummary()
    {
        $today = now()->toDateString();

        $rows = Sale::whereDate('sold_at', $today)->get();

        return response()->json([
            'date' => $today,
            'cash_sales' => $rows->where('payment_method', 'cash')->sum('total'),
            'card_sales' => $rows->where('payment_method', 'card')->sum('total'),
            'cash_received' => $rows->sum('cash_given'),
            'change_given' => $rows->sum('change_due'),
            'net_cash' => $rows->sum('cash_given') - $rows->sum('change_due'),
            'total_sales' => $rows->sum('total'),
            'count' => $rows->count(),
        ]);
    }



    public function openDrawer(Request $request)
    {
        $data = $request->validate([
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $date = now()->toDateString();

        $drawer = \App\Models\CashDrawer::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'business_date' => $date,
            ],
            [
                'opening_cash' => $data['opening_cash'],
            ]
        );

        return response()->json([
            'ok' => true,
            'drawer' => $drawer,
        ]);
    }



    public function zReport()
{
    $today = now()->toDateString();

    $drawer = \App\Models\CashDrawer::where('user_id', auth()->id())
        ->where('business_date', $today)
        ->first();

    if (!$drawer) {
        return response()->json([
            'date' => $today,
            'opening_cash' => 0,
            'cash_sales' => 0,
            'card_sales' => 0,
            'cash_received' => 0,
            'change_given' => 0,
            'expected_cash' => 0,
            'actual_cash' => 0,
            'cash_diff' => 0,
            'count' => 0,
        ]);
    }

    $sales = \App\Models\Sale::whereDate('sold_at', $today)->get();

    $cashSales = (float) $sales->where('payment_method', 'cash')->sum('total');
    $cardSales = (float) $sales->where('payment_method', 'card')->sum('total');
    $cashIn    = (float) $sales->sum('cash_given');
    $change    = (float) $sales->sum('change_due');

    $expectedCash = (float) $drawer->opening_cash + $cashIn - $change;

    return response()->json([
        'date' => $today,
        'opening_cash' => (float) $drawer->opening_cash,
        'cash_sales' => $cashSales,
        'card_sales' => $cardSales,
        'cash_received' => $cashIn,
        'change_given' => $change,
        'expected_cash' => $expectedCash,
        'actual_cash' => (float) ($drawer->actual_cash ?? 0),
        'cash_diff' => (float) ($drawer->cash_diff ?? 0),
        'count' => $sales->count(),
        'closed_at' => $drawer->closed_at,
        'note' => $drawer->close_note,
    ]);
}



    public function closeDay(Request $request)
{
    $data = $request->validate([
        'actual_cash' => 'required|numeric|min:0',
        'note' => 'nullable|string|max:255',
    ]);

    $today = now()->toDateString();

    $drawer = \App\Models\CashDrawer::where('user_id', auth()->id())
        ->where('business_date', $today)
        ->whereNull('closed_at')
        ->first();

    if (!$drawer) {
        return response()->json(['ok' => false, 'error' => 'No open day found.'], 422);
    }

    // Calculate totals
    $sales = \App\Models\Sale::whereDate('sold_at', $today)->get();

    $cashSales = (float) $sales->where('payment_method', 'cash')->sum('total');
    $cardSales = (float) $sales->where('payment_method', 'card')->sum('total');
    $cashIn    = (float) $sales->sum('cash_given');
    $change    = (float) $sales->sum('change_due');

    $expectedCash = (float) $drawer->opening_cash + $cashIn - $change;
    $actualCash   = (float) $data['actual_cash'];
    $diff         = (float) ($actualCash - $expectedCash);

    DB::beginTransaction();
    try {
        // Update drawer
        $drawer->actual_cash = $actualCash;
        $drawer->cash_diff   = $diff;
        $drawer->close_note  = $data['note'] ?? null;
        $drawer->closed_at   = now();
        $drawer->save();

        // Archive Z
        \App\Models\ZReport::updateOrCreate(
            ['user_id' => auth()->id(), 'business_date' => $today],
            [
                'opening_cash'  => (float) $drawer->opening_cash,
                'cash_sales'    => $cashSales,
                'card_sales'    => $cardSales,
                'cash_received' => $cashIn,
                'change_given'  => $change,
                'expected_cash' => $expectedCash,
                'actual_cash'   => $actualCash,
                'cash_diff'     => $diff,
                'closed_at'     => now(),
                'close_note'    => $drawer->close_note,
            ]
        );

        $this->audit('day.closed', [
            'business_date' => $today,
            'opening_cash' => (float)$drawer->opening_cash,
            'expected_cash' => $expectedCash,
            'actual_cash' => $actualCash,
            'cash_diff' => $diff,
        ]);

        DB::commit();

        return response()->json([
            'ok' => true,
            'expected_cash' => $expectedCash,
            'actual_cash' => $actualCash,
            'cash_diff' => $diff,
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
    }
}


public function reopenDay(Request $request)
{
    abort_unless(auth()->user()?->hasRole('Admin'), 403);

    $data = $request->validate([
        'reason' => 'required|string|max:255',
        'business_date' => 'nullable|date',
        'user_id' => 'nullable|integer',
    ]);

    $date = $data['business_date'] ?? now()->toDateString();
    $userId = $data['user_id'] ?? auth()->id();

    $drawer = \App\Models\CashDrawer::where('user_id', $userId)
        ->where('business_date', $date)
        ->whereNotNull('closed_at')
        ->first();

    if (!$drawer) {
        return response()->json(['ok' => false, 'error' => 'No closed drawer found.'], 422);
    }

    $drawer->closed_at = null;
    $drawer->reopened_at = now();
    $drawer->reopened_by = auth()->id();
    $drawer->reopen_reason = $data['reason'];
    $drawer->save();

    $this->audit('day.reopened', [
        'business_date' => $date,
        'drawer_user_id' => $userId,
        'reason' => $data['reason'],
    ]);

    return response()->json(['ok' => true]);
}




    private function audit(string $action, array $meta = []): void
    {
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action'  => $action,
            'meta'    => $meta,
            'created_at' => now(),
        ]);
    }
}
