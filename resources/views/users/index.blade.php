@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Users</h3>
  <a href="{{ route('users.create') }}" class="btn btn-success">Add User</a>
</div>

@if(session('ok'))
  <div class="alert alert-success">{{ session('ok') }}</div>
@endif

<table class="table table-striped">
  <thead>
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Roles</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($users as $u)
      <tr>
        <td>{{ $u->name }}</td>
        <td>{{ $u->email }}</td>
        <td>{{ method_exists($u,'getRoleNames') ? $u->getRoleNames()->implode(', ') : '—' }}</td>
        <td>
          <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-primary">Edit</a>
          <form action="{{ route('users.destroy', $u) }}" method="POST" style="display:inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
