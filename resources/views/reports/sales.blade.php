@extends('layouts.app')
@section('content')
<h3>Daily Sales</h3>
<form class="row g-2 mb-3" method="GET">
  <div class="col-auto"><input type="date" name="from" value="{{ $from }}" class="form-control"></div>
  <div class="col-auto"><input type="date" name="to"   value="{{ $to }}"   class="form-control"></div>
  <div class="col-auto"><button class="btn btn-primary">Filter</button></div>
</form>
<table class="table table-striped">
  <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead>
  <tbody>
    @foreach($daily as $d)
      <tr>
        <td>{{ $d->sold_at }}</td>
        <td>{{ $d->orders }}</td>
        <td>{{ number_format($d->revenue,2) }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
