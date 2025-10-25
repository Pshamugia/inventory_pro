@extends('layouts.app')
@section('content')
<div class="row justify-content-center">
<div class="col-md-4">
<div class="card">
<div class="card-header">Login</div>
<div class="card-body">
<form method="POST" action="/login">@csrf
<div class="mb-3"><label>Email</label><input name="email" class="form-control" type="email" required></div>
<div class="mb-3"><label>Password</label><input name="password" class="form-control" type="password" required></div>
<button class="btn btn-primary w-100">Login</button>
</form>
</div>
</div>
</div>
</div>
@endsection