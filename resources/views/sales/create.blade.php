@extends('layouts.app')
@section('content')

<div class="container mt-3">
  <h3>Point of Sale</h3>

  <form id="posForm" method="POST" action="{{ route('sales.store') }}">
    @csrf
    <div class="mb-3">
      <input id="scanBox" class="form-control" placeholder="Scan SKU or search...">
    </div>

    <div class="table-responsive">
      <table class="table" id="lines">
        <thead><tr><th>SKU</th><th>Name</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr></thead>
        <tbody></tbody>
      </table>
    </div>

    <div class="text-end mt-3">
      <h4>Grand Total: ₾<span id="grandTotal">0.00</span></h4>
      <button type="submit" id="btnPay" class="btn btn-success btn-lg" disabled>Pay & Save</button>
    </div>

    <textarea name="items" id="payload" hidden></textarea>
    <input type="hidden" name="total" id="totalField">
  </form>
</div>

<script>
/* PRODUCTS comes from controller: const PRODUCTS = @json($products); */
const PRODUCTS = @json($products);

(function(){
  const form       = document.getElementById('posForm');
  const scanBox    = document.getElementById('scanBox');
  const tbody      = document.querySelector('#lines tbody');
  const grandTotal = document.getElementById('grandTotal');
  const payload    = document.getElementById('payload');   // <textarea name="items">
  const totalField = document.getElementById('totalField'); // <input type="hidden" name="total">
  const btnPay     = document.getElementById('btnPay');

  // 1) Stop the form from submitting when you hit Enter anywhere (except the scan box handler below)
  form.addEventListener('keydown', (e)=>{
    if (e.key === 'Enter' && e.target !== scanBox) {
      e.preventDefault();
    }
  });

  // 2) Scan box: Enter -> add product; DO NOT submit the form
  scanBox.addEventListener('keydown', (e)=>{
    if (e.key !== 'Enter') return;
    e.preventDefault();

    const q = (scanBox.value || '').trim().toLowerCase();
    if (!q) return;

    // SKU exact match first; then name contains
    const p = PRODUCTS.find(x => (x.sku||'').toLowerCase() === q)
          || PRODUCTS.find(x => (x.name||'').toLowerCase().includes(q));

    if (p) addLine(p);
    scanBox.value = '';
    scanBox.focus();
  });

  // 3) Add a row (or bump qty if already in the cart)
  function addLine(p){
    const existing = tbody.querySelector(`tr[data-pid="${p.id}"]`);
    if (existing){
      const qty = existing.querySelector('.qty');
      qty.value = (parseFloat(qty.value || 0) + 1).toFixed(2);
      recalc();
      return;
    }

    const tr = document.createElement('tr');
    tr.dataset.pid = p.id;
    tr.innerHTML = `
      <td>${p.sku || ''}</td>
      <td>${p.name}</td>
      <td class="text-end">
        <input type="number" step="0.01" class="form-control form-control-sm text-end price" value="${p.sale_price ?? 0}">
      </td>
      <td class="text-end">
        <input type="number" step="0.01" class="form-control form-control-sm text-end qty" value="1">
      </td>
      <td class="text-end lineTotal">₾0.00</td>
      <td class="text-end">
        <button type="button" class="btn btn-sm btn-outline-danger del">&times;</button>
      </td>
    `;
    tbody.appendChild(tr);

    tr.querySelectorAll('.price,.qty').forEach(inp => {
      inp.addEventListener('input', recalc);
    });
    tr.querySelector('.del').addEventListener('click', ()=>{
      tr.remove();
      recalc();
    });

    recalc();
  }

  // 4) Recalculate totals and build the payload JSON for the server
  function recalc(){
    let total = 0;
    const items = [];

    tbody.querySelectorAll('tr').forEach(tr=>{
      const pid   = parseInt(tr.dataset.pid, 10);
      const price = parseFloat(tr.querySelector('.price').value || 0);
      const qty   = parseFloat(tr.querySelector('.qty').value || 0);
      const line  = price * qty;

      tr.querySelector('.lineTotal').textContent = '₾' + line.toFixed(2);
      total += line;

      items.push({ product_id: pid, price: price, qty: qty });
    });

    grandTotal.textContent = '₾' + total.toFixed(2);
    totalField.value = total.toFixed(2);
    payload.value = JSON.stringify(items);

    btnPay.disabled = (items.length === 0);
  }

})();
</script>


@endsection
