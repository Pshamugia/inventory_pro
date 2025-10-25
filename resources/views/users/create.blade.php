@extends('layouts.app')
@section('content')
<h3>New User</h3>
<form method="POST" action="{{ route('users.store') }}" class="row g-3">@csrf
  <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
  <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Password</label><input name="password" type="password" class="form-control" required></div>
  <div class="col-md-4">
    <label class="form-label">Role</label>
    <select name="role" class="form-select" required>
      <option>Admin</option><option>Manager</option><option>Cashier</option>
    </select>
  </div>
  <div class="col-12"><button class="btn btn-primary">Save</button></div>
</form>
@endsection
