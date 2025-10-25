<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function stock()
    {
        $products = Product::with(['category','unit'])
            ->withSum('movements as soh', 'qty_change')
            ->orderBy('name')->get();
        return view('reports.stock', compact('products'));
    }

    public function low()
    {
        $products = Product::with(['category','unit'])
            ->withSum('movements as soh','qty_change')
            ->having('soh','<=',DB::raw('reorder_level'))
            ->orderBy('name')->get();
        return view('reports.low', compact('products'));
    }

    public function sales(Request $r)
    {
        $from = $r->input('from');
        $to   = $r->input('to');

        $q = Sale::query();
        if ($from) $q->whereDate('sold_at','>=',$from);
        if ($to)   $q->whereDate('sold_at','<=',$to);

        $daily = $q->selectRaw('sold_at, SUM(total) as revenue, COUNT(*) as orders')
                   ->groupBy('sold_at')->orderBy('sold_at','desc')->get();

        return view('reports.sales', compact('daily','from','to'));
    }
}
