<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZReport extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id','business_date','opening_cash',
        'cash_sales','card_sales','cash_received','change_given',
        'expected_cash','actual_cash','cash_diff','closed_at','close_note'
    ];
}
