@extends('layouts.app')

@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between mb-3">
    <h3>Categories</h3>
    <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm">+ Add Category</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-bordered">
    <thead><tr><th>ID</th><th>Name</th><th>Actions</th></tr></thead>
    <tbody>
      @forelse($categories as $c)
        <tr>
          <td>{{ $c->id }}</td>
          <td>{{ $c->name }}</td>
          <td>
            <form method="POST" action="{{ route('categories.destroy', $c) }}">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="3" class="text-muted">No categories yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
