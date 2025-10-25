@extends('layouts.app')
@section('content')
<h3>Print Labels — {{ $product->name }}</h3>
<p><button onclick="window.print()" class="btn btn-secondary">Print</button></p>
<div style="display:flex; flex-wrap:wrap;">
  @for($i=0;$i<12;$i++)
    @include('partials.label',['product'=>$product])
  @endfor
</div>
@endsection
