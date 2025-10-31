@extends('layouts.app')
@section('content')
<div class="container">
  <h3>Edit User</h3>
  <form method="POST" action="{{ route('users.update', $user) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Password (leave empty to keep current)</label>
      <input type="password" name="password" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Role</label>
      <select name="role" class="form-select" required>
        @foreach(['Admin','Manager','Cashier'] as $r)
          <option value="{{ $r }}" @if($user->roles->pluck('name')->contains($r)) selected @endif>{{ $r }}</option>
        @endforeach
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
