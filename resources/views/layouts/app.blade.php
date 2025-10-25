<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ config('app.name') }}</title>
<link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/datatables.min.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
  window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
</script>

</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
<div class="container-fluid">
<a class="navbar-brand" href="/">Inventory Pro</a>
<div class="collapse navbar-collapse">
<ul class="navbar-nav me-auto">
     <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
        @if(Route::has('products.index'))
          <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Products</a></li>
        @endif
        @if(Route::has('pos'))
          <li class="nav-item"><a class="nav-link" href="{{ route('pos') }}">POS</a></li>
        @endif

        <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}">Categories</a></li>

<li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Products</a></li>
</ul>
<form method="POST" action="{{ route('logout') }}" class="d-flex">@csrf<button class="btn btn-outline-light">Logout</button></form>
</div>
</div>
</nav>
<div class="container py-4">@yield('content')</div>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/datatables.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>