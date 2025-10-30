<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;


class DashboardController extends Controller
{
    public function index()
    {
        $today      = Carbon::today();
        $weekStart  = Carbon::now()->subDays(6)->startOfDay(); // last 7 days window
        $lowThresh  = 5;

        // KPIs
        $totalProducts = Product::count();

        // today revenue & orders (computed from sale_items to avoid "total" column mismatch)
        $todayRevenue = (float) SaleItem::whereDate('created_at', $today)->sum('line_total');
        $todayOrders  = Sale::whereDate('created_at', $today)->count();
     $lowStockCnt = DB::table('products')
    ->leftJoin('stock_movements as sm', 'sm.product_id', '=', 'products.id')
    ->select('products.id')
    ->selectRaw('COALESCE(SUM(sm.qty_change), 0) as soh')
    ->groupBy('products.id')
    ->having('soh', '<=', $lowThresh)
    ->count();

        $kpis = [
            'todaySales' => $todayRevenue,   // ₾
            'products'   => $totalProducts,
            'lowStock'   => $lowStockCnt,
        ];

        // Sales chart (last 7 days: labels & series)
        $raw = SaleItem::selectRaw('DATE(created_at) as d, SUM(line_total) as amt')
            ->where('created_at', '>=', $weekStart)
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('amt','d'); // ['2025-10-24' => 123.45, ...]

        $period = collect(range(0, 6))->map(fn ($i) => Carbon::now()->subDays(6 - $i)->toDateString());
        $labels = $period->map(fn($d) => Carbon::parse($d)->format('M d'))->values();
        $series = $period->map(fn($d) => (float) ($raw[$d] ?? 0))->values();

        // Recent sales (10) with computed total and user
        $recent = Sale::with('user')->latest()->take(10)->get();
        $recentSales = $recent->map(function ($s) {
            $s->total = (float) $s->items()->sum('line_total');
            return $s;
        });

        // Low stock list (top 10)
     $lowProducts = DB::table('products')
    ->leftJoin('stock_movements as sm', 'sm.product_id', '=', 'products.id')
    ->select('products.id','products.sku','products.name')
    ->selectRaw('COALESCE(SUM(sm.qty_change), 0) as quantity')
    ->groupBy('products.id','products.sku','products.name')
    ->having('quantity', '<=', $lowThresh)
    ->orderBy('quantity')
    ->limit(10)
    ->get();

        return view('dashboard.admin', compact(
            'kpis','labels','series','recentSales','lowProducts'
        ));
    }
}
