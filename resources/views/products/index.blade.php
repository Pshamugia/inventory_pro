@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Products</h3>
  <a href="{{ route('products.create') }}" class="btn btn-success">Add Product</a>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-striped align-middle">
  <thead>
    <tr>
      <th>Image</th>
      <th>SKU</th>
      <th>Name</th>
      <th>Category</th>
      <th>Unit</th>
      <th>Sale Price</th>
      <th>Stock (all)</th>
      <th style="width:160px;">Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($products as $p)
      <tr>
        <td>
          @if($p->photo)
            <img src="{{ asset($p->photo) }}" width="80" alt="{{ $p->name }}">
          @endif
        </td>
        <td>{{ $p->sku }}</td>
        <td>{{ $p->name }}</td>
        <td>{{ $p->category->name }}</td>
        <td>{{ $p->unit->short_name }}</td>
        <td>{{ number_format($p->sale_price,2) }}</td>
        <td>{{ $p->stockOnHand() }}</td>
        <td>
          <a href="{{ route('products.edit', $p->id) }}" class="btn btn-sm btn-primary">Edit</a>

          <form action="{{ route('products.destroy', $p->id) }}"
                method="POST"
                style="display:inline-block"
                onsubmit="return confirm('Delete this product? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

{{ $products->links() }}
@endsection
