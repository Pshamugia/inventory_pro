@extends('layouts.app')
@section('content')
<h3>Low Stock</h3>
<table class="table table-striped">
  <thead><tr><th>SKU</th><th>Name</th><th>Reorder ≤</th><th>On Hand</th></tr></thead>
  <tbody>
    @foreach($products as $p)
      <tr>
        <td>{{ $p->sku }}</td>
        <td>{{ $p->name }}</td>
        <td>{{ $p->reorder_level }}</td>
        <td>{{ (int)($p->soh ?? 0) }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
