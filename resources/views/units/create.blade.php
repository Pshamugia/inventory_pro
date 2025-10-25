@extends('layouts.app')

@section('content')
<div class="container mt-4">
  <h3>Add Unit</h3>
  <form method="POST" action="{{ route('units.store') }}">
    @csrf
    <div class="mb-3">
      <label for="name" class="form-label">Unit Name</label>
      <input type="text" name="name" id="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="symbol" class="form-label">Symbol (optional)</label>
      <input type="text" name="symbol" id="symbol" class="form-control">
    </div>
    <button class="btn btn-primary">Save</button>
  </form>
</div>
@endsection
