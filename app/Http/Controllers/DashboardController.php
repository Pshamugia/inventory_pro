<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\{Product, Category, Warehouse, Sale, SaleItem};

class DashboardController extends Controller
{
    // Remove the constructor; route middleware will protect it.

    public function index()
    {
        $today   = Carbon::today();
        $start7  = Carbon::today()->subDays(6);
        $start30 = Carbon::today()->subDays(29);

        $counts = [
            'products'   => Product::count(),
            'categories' => Category::count(),
            'warehouses' => Warehouse::count(),
            'salesToday' => Sale::whereDate('sold_at', $today)->count(),
        ];

        $todayKpi = Sale::whereDate('sold_at', $today)
            ->selectRaw('COALESCE(SUM(total),0) as revenue, COUNT(*) as orders')
            ->first();

        $days = Sale::selectRaw('DATE(sold_at) as d, COALESCE(SUM(total),0) as revenue')
            ->whereBetween('sold_at', [$start7, $today])
            ->groupBy('d')->orderBy('d')
            ->pluck('revenue', 'd');

        $labels = []; $series = [];
        for ($i=0; $i<7; $i++) {
            $d = $start7->copy()->addDays($i)->toDateString();
            $labels[] = $d;
            $series[] = (float)($days[$d] ?? 0);
        }

        $lowStockCount = Product::where('track_stock', true)
    ->withSum('movements as soh', 'qty_change')
    ->get()
    ->filter(fn ($p) => (int)($p->soh ?? 0) <= (int)$p->reorder_level)
    ->count();

        $stockValue = 0;
        foreach (Product::withSum('movements as soh','qty_change')->get() as $p) {
            $stockValue += max(0, (int)($p->soh ?? 0)) * (float)$p->cost_price;
        }

        $topProducts = SaleItem::selectRaw('product_id, SUM(qty) as qty')
            ->whereHas('sale', fn($q)=>$q->whereBetween('sold_at',[$start30,$today]))
            ->with('product:id,name,sku')
            ->groupBy('product_id')->orderByDesc('qty')->limit(5)->get();

        $recentSales = Sale::withCount('items')->latest()->limit(10)->get();

        return view('dashboard', compact(
            'counts','todayKpi','labels','series',
            'lowStockCount','stockValue','topProducts','recentSales'
        ));
    }
}
