@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h3 class="mb-3">Today’s Summary</h3>

  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="card"><div class="card-body">
        <div class="text-muted">Orders</div>
        <div class="fs-3 fw-bold">{{ $kpis->orders }}</div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card"><div class="card-body">
        <div class="text-muted">Revenue</div>
        <div class="fs-3 fw-bold">₾ {{ number_format($kpis->revenue,2) }}</div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card"><div class="card-body">
        <div class="text-muted">Avg order</div>
        <div class="fs-3 fw-bold">₾ {{ number_format($kpis->orders ? $kpis->revenue/$kpis->orders : 0,2) }}</div>
      </div></div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header bg-white"><strong>By payment method</strong></div>
        <div class="card-body p-0">
          <table class="table mb-0">
            <thead><tr><th>Method</th><th>Orders</th><th class="text-end">Amount</th></tr></thead>
            <tbody>
              @foreach($methods as $m)
                <tr>
                  <td>{{ $m->payment_method ?? '—' }}</td>
                  <td>{{ $m->cnt }}</td>
                  <td class="text-end">₾ {{ number_format($m->amt,2) }}</td>
                </tr>
              @endforeach
              @if($methods->isEmpty())
                <tr><td colspan="3" class="text-center text-muted py-4">No data</td></tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header bg-white"><strong>Top products (qty)</strong></div>
        <div class="card-body p-0">
          <table class="table mb-0">
            <thead><tr><th>SKU</th><th>Name</th><th>Qty</th><th class="text-end">Amount</th></tr></thead>
            <tbody>
              @foreach($topItems as $t)
                <tr>
                  <td>{{ $t->sku }}</td>
                  <td>{{ $t->name }}</td>
                  <td>{{ number_format($t->qty,2) }}</td>
                  <td class="text-end">₾ {{ number_format($t->amt,2) }}</td>
                </tr>
              @endforeach
              @if($topItems->isEmpty())
                <tr><td colspan="4" class="text-center text-muted py-4">No data</td></tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
