@extends('layouts.app')

@section('content')
<div class="container py-4">

  <div class="d-flex align-items-center mb-4">
    <h3 class="mb-0">Admin Dashboard</h3>
  </div>

  {{-- KPIs --}}
  <div class="row g-3 mb-4">

    <div class="col-md-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <div class="text-muted">Today’s Sales</div>
              <div class="fs-4 fw-bold">{{ number_format($kpis['todaySales'], 2) }} ₾</div>
            </div>
            <div class="opacity-50">
              {{-- money icon --}}
              <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor" class="text-secondary">
                <path d="M3 6h18v12H3zM7 9h2v6H7zM11 9h2v6h-2zM15 9h2v6h-2z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <div class="text-muted">Products</div>
              <div class="fs-4 fw-bold">{{ $kpis['products'] }}</div>
            </div>
            <div class="opacity-50">
              {{-- box icon --}}
              <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor" class="text-secondary">
                <path d="M21 8l-9-5-9 5 9 5 9-5zm-9 7L3 10v6l9 5 9-5v-6l-9 5z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <div class="text-muted">Low stock (≤ 5)</div>
              <div class="fs-4 fw-bold">{{ $kpis['lowStock'] }}</div>
            </div>
            <div class="opacity-50">
              {{-- warning icon --}}
              <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor" class="text-secondary">
                <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- Sales (7 days) --}}
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white">
      <strong>Sales — last 7 days</strong>
    </div>
    <div class="card-body">
      <canvas id="sales7" height="120"></canvas>
    </div>
  </div>

  <div class="row g-3">

    {{-- Recent Sales --}}
    <div class="col-lg-7">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white"><strong>Recent Sales</strong></div>
        <div class="card-body p-0">
          <table class="table mb-0 align-middle">
            <thead class="small text-muted">
              <tr>
                <th>#</th><th>Date</th><th>Cashier</th><th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
            @forelse($recentSales as $s)
              <tr>
                <td>{{ $s->id }}</td>
                <td>{{ optional($s->created_at)->format('Y-m-d H:i') }}</td>
                <td>{{ $s->user->name ?? '—' }}</td>
                <td class="text-end">{{ number_format($s->total,2) }} ₾</td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted py-4">No sales yet</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Low Stock --}}
    <div class="col-lg-5">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white"><strong>Low stock (≤ 5)</strong></div>
        <div class="card-body p-0">
          <table class="table mb-0 align-middle">
            <thead class="small text-muted">
              <tr><th>SKU</th><th>Name</th><th class="text-end">Qty</th></tr>
            </thead>
            <tbody>
              @forelse($lowProducts as $p)
                <tr>
                  <td>{{ $p->sku ?? $p->id }}</td>
                  <td>{{ $p->name }}</td>
                  <td class="text-end">{{ (int) $p->quantity }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted py-4">All good 👍</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

</div>
@endsection

@push('scripts')
  {{-- OFFLINE Chart.js (make sure this file exists) --}}
  <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
  <script>
    (function () {
      const el = document.getElementById('sales7');
      if (!el || typeof Chart === 'undefined') return;

      const labels = @json($labels, JSON_UNESCAPED_UNICODE);
      const data   = @json($series, JSON_NUMERIC_CHECK);

      new Chart(el, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: '₾ Sales',
            data: data,
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          scales: { y: { beginAtZero: true } }
        }
      });
    })();
  </script>
@endpush
