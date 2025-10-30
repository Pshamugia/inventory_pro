{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name', 'Inventory Pro') }}</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script>
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  </script>

  {{-- Your assets --}}
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/datatables/datatables.min.css') }}">


  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
@php
  $u = Auth::user();               // current user or null
  $role = $u->role ?? null;        // 'Admin' | 'Manager' | 'Cashier' | null
  $isAdmin   = ($role === 'Admin');
  $isManager = ($role === 'Manager');
  $isCashier = ($role === 'Cashier');
@endphp

@php
  use Illuminate\Support\Facades\Auth;

  $u = Auth::user();
  $role = strtolower((string)($u->role ?? ''));
  // normalize legacy names if needed
  $map  = ['owner' => 'admin', 'superadmin' => 'admin'];
  $role = $map[$role] ?? $role;

  $isAdmin   = $u?->hasRole(['admin'])   ?? false;
  $isManager = $u?->hasRole(['manager']) ?? false;
  $isCashier = $u?->hasRole(['cashier']) ?? false;
@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ route('dashboard') }}">Inventory Pro</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto">
        @auth
          {{-- Dashboard (all roles) --}}
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               href="{{ route('dashboard') }}">Dashboard</a>
          </li>

          {{-- Catalog (Admin + Manager; Cashier sees Products list via direct URL but no buttons) --}}
          @if($isAdmin || $isManager)
            <li class="nav-item">
              <a class="nav-link {{ request()->is('products*') ? 'active' : '' }}"
                 href="{{ route('products.index') }}">Products</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('categories*') ? 'active' : '' }}"
                 href="{{ route('categories.index') }}">Categories</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('units*') ? 'active' : '' }}"
                 href="{{ route('units.index') }}">Units</a>
            </li>
          @endif

          {{-- POS (Admin + Manager + Cashier) --}}
          @if($isAdmin || $isManager || $isCashier)
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('pos') ? 'active' : '' }}"
                 href="{{ route('pos') }}">POS</a>
            </li>
          @endif

          {{-- Reports (Admin + Manager) --}}
          @if(($isAdmin || $isManager) && Route::has('reports.sales'))
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle {{ request()->is('reports*') ? 'active' : '' }}"
                 href="#" role="button" data-bs-toggle="dropdown">Reports</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('reports.sales') }}">Sales</a></li>
                <li><a class="dropdown-item" href="{{ route('reports.stock') }}">Stock</a></li>
                <li><a class="dropdown-item" href="{{ route('reports.low') }}">Low stock</a></li>
              </ul>
            </li>
          @endif

          {{-- Users (Admin only) --}}
          @if($isAdmin && Route::has('users.index'))
            <li class="nav-item">
              <a class="nav-link {{ request()->is('users*') ? 'active' : '' }}"
                 href="{{ route('users.index') }}">Users</a>
            </li>
          @endif
        @endauth
      </ul>

      <ul class="navbar-nav ms-auto">
        @guest
          @if(Route::has('login'))
            <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
          @endif
          @if(Route::has('register'))
            <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
          @endif
        @endguest

        @auth
          {{-- User dropdown --}}
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              {{ $u->name ?? 'User' }} <small class="text-muted">({{ ucfirst($role ?: '—') }})</small>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              {{-- Optional: add a profile/settings route later --}}
              @if($isAdmin && Route::has('users.index'))
                <li><a class="dropdown-item" href="{{ route('users.index') }}">Manage Users</a></li>
                <li><hr class="dropdown-divider"></li>
              @endif
              <li>
                <form method="POST" action="{{ route('logout') }}" class="px-3">
                  @csrf
                  <button class="btn btn-sm btn-outline-danger w-100">Logout</button>
                </form>
              </li>
            </ul>
          </li>
        @endauth
      </ul>
    </div>
  </div>
</nav>


{{-- Main content --}}
<div class="container py-4">
  {{-- Flash for 403s (optional UX nicety) --}}
  @if(session('forbidden'))
    <div class="alert alert-danger">{{ session('forbidden') }}</div>
  @endif

  @yield('content')
</div>


{{-- Scripts --}}
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/datatables.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
