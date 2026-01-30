@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">Add Product</h3>

    @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

<form id="productForm" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="row g-3">
        @csrf

        <div class="col-md-3">
            <label class="form-label">SKU</label>
            <input name="sku" class="form-control" value="{{ old('sku') }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="{{ old('name') }}" required>
        </div>


        <div class="col-md-4">
            <label class="form-label">Photo</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
        </div>

        {{-- CATEGORY + ADD --}}
        <div class="col-md-3">
            <label class="form-label">Category</label>
            <div class="input-group">
                @php
                $selectedCategory = old('category_id', session('new_category_id'));
                @endphp
                <select name="category_id" id="categorySelect" class="form-select" required>
                    <option value="">-- choose --</option>
                    @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected($selectedCategory==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-outline-secondary"
                    data-bs-toggle="modal" data-bs-target="#addCategoryModal"
                    title="Add new category">+</button>
            </div>
            @error('category_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        {{-- UNIT + ADD --}}
        <div class="col-md-3">
            <label class="form-label">Unit</label>
            <div class="input-group">
                @php
                $selectedUnit = old('unit_id', session('new_unit_id'));
                @endphp
                <select name="unit_id" id="unitSelect" class="form-select" required>
                    <option value="">-- choose --</option>
                    @foreach($units as $u)
                    <option value="{{ $u->id }}" @selected($selectedUnit==$u->id)">
                        {{ $u->name }}@if($u->symbol) ({{ $u->symbol }}) @endif
                    </option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-outline-secondary"
                    data-bs-toggle="modal" data-bs-target="#addUnitModal"
                    title="Add new unit">+</button>
            </div>
            @error('unit_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Cost Price</label>
            <input name="cost_price" type="number" step="0.01" class="form-control"
                value="{{ old('cost_price') }}" required>
            @error('cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Sale Price</label>
            <input name="sale_price" type="number" step="0.01" class="form-control"
                value="{{ old('sale_price') }}" required>
            @error('sale_price')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>


     <div class="col-md-3">
    <label class="form-label">Initial Stock</label>
    <input name="initial_stock"
           type="number"
           class="form-control"
           value="{{ old('initial_stock', 0) }}"
           min="0">
</div>



        <div class="col-md-3">
            <label class="form-label">Reorder Level</label>
            <input name="reorder_level" type="number" class="form-control"
                value="{{ old('reorder_level') }}">
            @error('reorder_level')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 mt-2">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>

{{-- =====================  MODALS (no JS fetch; plain POST)  ===================== --}}

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('categories.quick-back') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Unit Modal --}}
<div class="modal fade" id="addUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('units.quick-back') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Piece, Kilogram, Liter" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Symbol (optional)</label>
                    <input type="text" name="symbol" class="form-control" placeholder="pcs, kg, l, box">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection