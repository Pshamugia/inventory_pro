@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Dashboard</h3>
  <div class="d-flex gap-2">
    @if (Route::has('products.index'))
      <a href="{{ route('products.index') }}" class="btn btn-outline-primary">Products</a>
    @endif
 
      <a href="{{ route('pos') }}" class="btn btn-success">Open POS</a>
 
  </div>
</div>

{{-- KPI CARDS --}}
@php
  $countProducts    = (int)($counts['products']   ?? 0);
  $countWarehouses  = (int)($counts['warehouses'] ?? 0);
  $lowStockCount    = (int)($lowStockCount        ?? 0);
  $stockValue       = (float)($stockValue         ?? 0);
  $todayOrders      = (int)($todayKpi->orders     ?? 0);
  $todayRevenue     = (float)($todayKpi->revenue  ?? 0);
@endphp

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted">Products</div>
        <div class="h3 mb-0">{{ number_format($countProducts) }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted">Warehouses</div>
        <div class="h3 mb-0">{{ number_format($countWarehouses) }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted">Low Stock Alerts</div>
        <div class="h3 mb-0">{{ number_format($lowStockCount) }}</div>
        @if (Route::has('reports.low'))
          <div><a href="{{ route('reports.low') }}" class="small">View list</a></div>
        @endif
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted">Stock Value (cost)</div>
        <div class="h3 mb-0">₾{{ number_format($stockValue, 2) }}</div>
      </div>
    </div>
  </div>
</div>

{{-- SALES SNAPSHOT --}}
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted">Today Orders</div>
        <div class="h3 mb-0">{{ number_format($todayOrders) }}</div>
        <hr>
        <div class="text-muted">Today Revenue</div>
        <div class="h3 mb-0">₾{{ number_format($todayRevenue, 2) }}</div>
      </div>
    </div>
  </div>

  {{-- Simple “no-internet-needed” placeholder instead of a chart.
       If you want a chart later, replace this card body with a <canvas>
       and include Chart.js via CDN or a local file. --}}
  <div class="col-md-8">
    <div class="card shadow-sm h-100">
      <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="text-muted">Last 7 Days Revenue</div>
          @if (Route::has('reports.sales'))
            <a href="{{ route('reports.sales') }}" class="small">Sales report</a>
          @endif
        </div>
        @php
          $labels = $labels ?? [];
          $series = $series ?? [];
          $last7  = collect($labels)->zip($series);
        @endphp
        <table class="table table-sm mb-0">
          <thead><tr><th>Date</th><th class="text-end">Revenue</th></tr></thead>
          <tbody>
          @forelse($last7 as [$d,$rev])
            <tr><td>{{ $d }}</td><td class="text-end">₾{{ number_format((float)$rev,2) }}</td></tr>
          @empty
            <tr><td colspan="2" class="text-muted">No sales yet.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- TOP PRODUCTS + RECENT SALES --}}
<div class="row g-3">
  <div class="col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title mb-3">Top Products (30 days)</h5>
        <table class="table table-sm">
          <thead><tr><th>SKU</th><th>Name</th><th class="text-end">Qty</th></tr></thead>
          <tbody>
          @forelse(($topProducts ?? []) as $row)
            <tr>
              <td>{{ $row->product->sku ?? '—' }}</td>
              <td>{{ $row->product->name ?? 'Unknown' }}</td>
              <td class="text-end">{{ (int)($row->qty ?? 0) }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-muted">No sales yet.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title mb-3">Recent Sales</h5>
        <table class="table table-sm">
          <thead><tr><th>Ref</th><th>Date</th><th class="text-end">Items</th><th class="text-end">Total</th></tr></thead>
          <tbody>
          @forelse(($recentSales ?? []) as $s)
            <tr>
              <td>{{ $s->reference }}</td>
              <td>{{ $s->sold_at }}</td>
              <td class="text-end">{{ (int)($s->items_count ?? 0) }}</td>
              <td class="text-end">₾{{ number_format((float)$s->total,2) }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-muted">No sales yet.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
