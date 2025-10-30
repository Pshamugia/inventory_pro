<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['reference','customer_id','sold_at','total', 'user_id', 'payment_method', 'cash_given', 'change_due', 
];

   public function items() { return $this->hasMany(\App\Models\SaleItem::class); }
public function user()  { return $this->belongsTo(\App\Models\User::class); }

}
