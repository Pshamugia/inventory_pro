<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Adjustment;
use App\Services\InventoryService;

class AdjustmentController extends Controller
{
    // POST /adjustments
    public function store(Request $r, InventoryService $inv)
    {
        $data = $r->validate([
            'product_id'   => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'qty_change'   => 'required|integer|not_in:0',
            'note'         => 'nullable|string|max:500',
        ]);

        $adj = Adjustment::create($data);

        // Write to stock ledger (+ or -)
        if ($data['qty_change'] > 0) {
            $inv->receive($data['product_id'], $data['warehouse_id'], $data['qty_change'], $adj);
        } else {
            $inv->issue($data['product_id'], $data['warehouse_id'], abs($data['qty_change']), $adj);
        }

        return back()->with('ok', 'Adjustment saved');
    }
}
