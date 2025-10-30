<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema; // ← add at the top with the other uses

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;

use App\Models\SaleItem;
use App\Models\StockMovement;

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
        $data = $request->validate([
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty'        => 'required|numeric|min:0.001',
            'items.*.price'      => 'required|numeric|min:0',
            'payment_method'     => 'required|string|max:50',
            'cash_given'         => 'nullable|numeric|min:0',
            'paid'               => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $sale = new Sale();
            $sale->user_id        = auth()->id(); // cashier id
            $sale->payment_method = $data['payment_method'];
            $sale->total          = 0;
            $sale->cash_given     = $data['cash_given'] ?? 0;
            $sale->change_due     = 0;
            $sale->save();

            $total = 0;

            foreach ($data['items'] as $row) {
                $product = Product::find($row['product_id']);

                // ✅ prevent negative stock
                if ($product->stockOnHand() < $row['qty']) {
                    throw new \Exception("Not enough stock for {$product->name}");
                }

                $lineTotal = $row['qty'] * $row['price'];
                $total += $lineTotal;

                SaleItem::create([
                    'sale_id'     => $sale->id,
                    'product_id'  => $row['product_id'],
                    'qty'         => $row['qty'],
                    'unit_price'  => $row['price'],
                    'line_total'  => $lineTotal,
                ]);

                // record stock movement
                StockMovement::create([
                    'product_id' => $row['product_id'],
                    'qty_change' => -$row['qty'],
                    'reason'     => 'sale',
                    'reference_type' => 'Sale',
                    'reference_id' => $sale->id,
                ]);
            }

            $sale->total = $total;
            $sale->change_due = max(0, ($sale->cash_given - $total));
            $sale->save();

            DB::commit();

            return response()->json([
                'ok'             => true,
                'sale_id'        => $sale->id,
                'total'          => (float) $total,
                'time'           => now()->format('Y-m-d H:i'),
                'payment_method' => $sale->payment_method,
                'cash_given'     => (float) ($sale->cash_given ?? 0),
                'change'         => (float) ($sale->change_due ?? 0),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
