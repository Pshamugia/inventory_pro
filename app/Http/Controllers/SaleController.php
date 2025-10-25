<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::latest()->paginate(20);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        // All products for POS dropdown / search
        $products = Product::select('id','sku','name','sale_price')->orderBy('name')->get();
        return view('sales.create', compact('products'));
    }

    public function store(Request $request)
{
    $items = json_decode($request->input('items', '[]'), true);
    if (!is_array($items) || count($items) === 0) {
        return back()->with('error', 'No items in sale.');
    }

    $total = (float) $request->input('total', 0);

    // Optional: validate each item quickly
    foreach ($items as $i => $row) {
        if (empty($row['product_id']) || $row['qty'] <= 0 || $row['price'] < 0) {
            return back()->with('error', "Invalid item at row ".($i+1));
        }
    }

    DB::transaction(function () use ($items, $total) {
        $sale = \App\Models\Sale::create([
            'reference' => 'S-'.now()->format('YmdHis'),
            'sold_at'   => now(),
            'total'     => $total,
        ]);

        foreach ($items as $row) {
            \App\Models\SaleItem::create([
                'sale_id'    => $sale->id,
                'product_id' => $row['product_id'],
                'qty'        => $row['qty'],
                'price'      => $row['price'],
                'line_total' => $row['qty'] * $row['price'],
            ]);

            // stock deduction
            \App\Models\StockMovement::create([
                'product_id'   => $row['product_id'],
                'warehouse_id' => 1,
                'type'         => 'sale',
                'qty_change'   => -abs($row['qty']),
                'note'         => 'POS '.$sale->reference,
                'moved_at'     => now(),
            ]);
        }
    });

    return redirect()->route('dashboard')->with('success', 'Sale saved!');
}

}
