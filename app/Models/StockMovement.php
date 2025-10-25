<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
protected $fillable = ['product_id','warehouse_id','qty_change','reason'];


public function product(){ return $this->belongsTo(Product::class); }
public function warehouse(){ return $this->belongsTo(Warehouse::class); }
public function reference(){ return $this->morphTo(); }
}
