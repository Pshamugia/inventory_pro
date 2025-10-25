@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-3">Edit Product</h3>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">SKU</label>
        <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
      </div>

      <div class="col-md-8">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select" required>
          <option value="">— Select —</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(old('category_id',$product->category_id)==$c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Unit</label>
        <select name="unit_id" class="form-select" required>
          <option value="">— Select —</option>
          @foreach($units as $u)
            <option value="{{ $u->id }}" @selected(old('unit_id',$product->unit_id)==$u->id)>{{ $u->name }} ({{ $u->short_name }})</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Cost Price</label>
        <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price',$product->cost_price) }}" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Sale Price</label>
        <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price',$product->sale_price) }}" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Reorder Level</label>
        <input type="number" name="reorder_level" class="form-control" value="{{ old('reorder_level',$product->reorder_level) }}">
      </div>

      <div class="col-md-4">
        <label class="form-label">Photo (optional)</label>
        <input type="file" name="photo" class="form-control">
        @if($product->photo)
          <small class="d-block mt-2">Current:</small>
          <img src="{{ asset($product->photo) }}" alt="{{ $product->name }}" width="120">
        @endif
      </div>
    </div>

    <div class="mt-4 d-flex gap-2">
      <button class="btn btn-primary" type="submit">Save changes</button>
      <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
@endsection
