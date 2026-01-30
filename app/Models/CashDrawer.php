<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashDrawer extends Model
{
    protected $fillable = [
        'user_id',
        'business_date',
        'opening_cash',
        'closed_at',
    ];
}
