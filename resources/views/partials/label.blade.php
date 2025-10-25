<div style="width:220px; padding:6px; border:1px dashed #ccc; margin:6px; text-align:center;">
  <div style="font-weight:600">{{ $product->name }}</div>
  <div>{!! DNS1D::getBarcodeHTML($product->sku, 'C128', 1.6, 48) !!}</div>
  <div style="font-size:12px">SKU: {{ $product->sku }} • ₾{{ number_format($product->sale_price,2) }}</div>
</div>
