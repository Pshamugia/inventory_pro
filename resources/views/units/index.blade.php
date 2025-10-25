@extends('layouts.app')

@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between mb-3">
    <h3>Units</h3>
    <a href="{{ route('units.create') }}" class="btn btn-primary btn-sm">+ Add Unit</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-bordered">
    <thead><tr><th>ID</th><th>Name</th><th>Symbol</th><th>Actions</th></tr></thead>
    <tbody>
      @forelse($units as $u)
        <tr>
          <td>{{ $u->id }}</td>
          <td>{{ $u->name }}</td>
          <td>{{ $u->symbol }}</td>
          <td>
            <form method="POST" action="{{ route('units.destroy',$u) }}">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-muted">No units yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
