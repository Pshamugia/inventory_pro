<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Product;
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

   // app/Http/Controllers/ReportController.php
public function low()
{
    $products = DB::table('products')
        ->select('products.*')
        ->selectSub(function ($q) {
            $q->from('stock_movements')
              ->selectRaw('SUM(qty_change)')
              ->whereColumn('stock_movements.product_id', 'products.id');
        }, 'soh')
        ->whereRaw('(SELECT SUM(qty_change) FROM stock_movements WHERE stock_movements.product_id = products.id) <= products.reorder_level')
        ->orderBy('name')
        ->get();

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


     public function todaySummary()
    {
        $today = Carbon::today();

        $kpis = DB::table('sales')
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(total),0) as revenue')
            ->whereDate('sold_at', $today)->first();

        $methods = DB::table('sales')
            ->select('payment_method', DB::raw('COUNT(*) as cnt'), DB::raw('COALESCE(SUM(total),0) as amt'))
            ->whereDate('sold_at', $today)
            ->groupBy('payment_method')
            ->get();

        $topItems = DB::table('sale_items as si')
            ->join('products as p','p.id','=','si.product_id')
            ->join('sales as s','s.id','=','si.sale_id')
            ->whereDate('s.sold_at', $today)
            ->select('p.sku','p.name', DB::raw('SUM(si.qty) as qty'), DB::raw('SUM(si.line_total) as amt'))
            ->groupBy('p.sku','p.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        return view('reports.today', compact('kpis','methods','topItems'));
    }


 public function exportTodayCsv()
{
    $today = now()->toDateString();

    $rows = DB::table('sales as s')
        ->leftJoin('users as u', 'u.id', '=', 's.user_id')
        ->selectRaw('s.id, s.sold_at, u.name as cashier, s.payment_method, s.total, s.cash_given, s.change_due')
        ->whereDate('s.sold_at', $today)
        ->orderBy('s.id')
        ->get();

    // ensure folder exists
    $dir = storage_path('app/exports');
    if (!is_dir($dir)) {
        // 0777 is fine on local; omit on Linux if you prefer umask defaults
        mkdir($dir, 0777, true);
    }

    $path = $dir . DIRECTORY_SEPARATOR . "sales_{$today}.csv";
    $f = fopen($path, 'w');

    // (optional) UTF-8 BOM if you plan to open in Excel
    // fwrite($f, "\xEF\xBB\xBF");

    fputcsv($f, ['ID','Sold at','Cashier','Method','Total','Cash given','Change']);
    foreach ($rows as $r) {
        fputcsv($f, [
            $r->id,
            $r->sold_at,
            $r->cashier,
            $r->payment_method,
            number_format($r->total,2,'.',''),
            number_format($r->cash_given ?? 0,2,'.',''),
            number_format($r->change_due ?? 0,2,'.',''),
        ]);
    }
    fclose($f);

    return response()->download($path);
}

}
