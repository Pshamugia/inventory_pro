<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
protected $fillable = ['category_id','unit_id','sku','name', 'photo', 'cost_price','sale_price','reorder_level','track_stock'];


public function category(){ return $this->belongsTo(Category::class); }
public function unit(){ return $this->belongsTo(Unit::class); }
public function movements(){ return $this->hasMany(StockMovement::class); }


public function stockOnHand(?int $warehouseId = null): int
{
$q = $this->movements();
if ($warehouseId) $q->where('warehouse_id', $warehouseId);
return (int) $q->sum('qty_change');
}
}
