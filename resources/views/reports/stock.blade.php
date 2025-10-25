@extends('layouts.app')
@section('content')
<h3>Stock on Hand</h3>
<table class="table table-striped">
  <thead><tr><th>SKU</th><th>Name</th><th>Cat</th><th>Unit</th><th>On Hand</th></tr></thead>
  <tbody>
    @foreach($products as $p)
      <tr>
        <td>{{ $p->sku }}</td>
        <td>{{ $p->name }}</td>
        <td>{{ $p->category->name ?? '—' }}</td>
        <td>{{ $p->unit->short_name ?? '—' }}</td>
        <td>{{ (int)($p->soh ?? 0) }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
