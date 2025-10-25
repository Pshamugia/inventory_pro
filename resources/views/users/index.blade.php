@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Users</h3>
  <a href="{{ route('users.create') }}" class="btn btn-success">Add User</a>
</div>
<table class="table table-striped">
  <thead><tr><th>Name</th><th>Email</th><th>Roles</th></tr></thead>
  <tbody>
    @foreach($users as $u)
      <tr>
        <td>{{ $u->name }}</td>
        <td>{{ $u->email }}</td>
        <td>{{ method_exists($u,'getRoleNames') ? $u->getRoleNames()->implode(', ') : '—' }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
