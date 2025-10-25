<!-- resources/views/auth/register.blade.php -->
@extends('layouts.app')
@section('content')
<div class="row justify-content-center"><div class="col-md-4">
  <div class="card">
    <div class="card-header">Register</div>
    <div class="card-body">
      <form method="POST" action="{{ route('register') }}">@csrf
        <div class="mb-3"><label>Name</label><input name="name" class="form-control" required></div>
        <div class="mb-3"><label>Email</label><input name="email" type="email" class="form-control" required></div>
        <div class="mb-3"><label>Password</label><input name="password" type="password" class="form-control" required></div>
        <div class="mb-3"><label>Confirm Password</label><input name="password_confirmation" type="password" class="form-control" required></div>
        <button class="btn btn-primary w-100">Create</button>
      </form>
    </div>
  </div>
</div></div>
@endsection
