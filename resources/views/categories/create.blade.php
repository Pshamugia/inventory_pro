@extends('layouts.app')

@section('content')
<div class="container mt-4">
  <h3>Add Category</h3>
  <form method="POST" action="{{ route('categories.store') }}">
    @csrf
    <div class="mb-3">
      <label for="name" class="form-label">Category Name</label>
      <input type="text" name="name" id="name" class="form-control" required>
    </div>
    <button class="btn btn-primary">Save</button>
  </form>
</div>
@endsection
