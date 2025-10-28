<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['reference','customer_id','sold_at','total'];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
