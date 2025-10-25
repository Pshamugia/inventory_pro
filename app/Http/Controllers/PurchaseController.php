<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
public function store(Request $r, InventoryService $inv){
$data = $r->validate([
'supplier_id'=>'nullable|exists:suppliers,id',
'purchased_at'=>'required|date',
'items'=>'required|array|min:1',
'items.*.product_id'=>'required|exists:products,id',
'items.*.warehouse_id'=>'required|exists:warehouses,id',
'items.*.qty'=>'required|integer|min:1',
'items.*.unit_cost'=>'required|numeric|min:0',
]);


DB::transaction(function() use ($data,$inv){
$purchase = Purchase::create([
'reference' => 'PUR-'.now()->format('Ymd-His'),
'supplier_id' => $data['supplier_id'] ?? null,
'purchased_at'=> $data['purchased_at'],
'total' => 0,
]);


$total = 0;
foreach($data['items'] as $line){
$lineTotal = $line['qty'] * $line['unit_cost'];
$pi = PurchaseItem::create([
'purchase_id'=>$purchase->id,
'product_id'=>$line['product_id'],
'warehouse_id'=>$line['warehouse_id'],
'qty'=>$line['qty'],
'unit_cost'=>$line['unit_cost'],
'line_total'=>$lineTotal,
]);
$total += $lineTotal;
$inv->receive($pi->product_id, $pi->warehouse_id, $pi->qty, $pi);
}


$purchase->update(['total'=>$total]);
});


return back()->with('ok','Purchase recorded');
}
}
