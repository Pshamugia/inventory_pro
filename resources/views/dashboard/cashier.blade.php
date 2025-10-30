@extends('layouts.app')

@section('content')
<style>
  .pos-wrap {max-width: 1600px;}
  .pos-left {border-right: 1px solid #eee;}
  .product-btn{min-height:72px}
  .cart-table td, .cart-table th {vertical-align: middle;}
  .keypad button{min-width:64px; min-height:56px}
</style>

<div class="container-fluid pos-wrap py-3">
  <div class="row">
    {{-- LEFT: Search + Product grid --}}
    <div class="col-lg-7 pos-left">
      <div class="d-flex gap-2 mb-3">
        <input id="searchInput" class="form-control form-control-lg" placeholder="Search by name or SKU… (Enter to search)">
        <button id="searchBtn" class="btn btn-primary btn-lg">Search</button>
      </div>

      <div class="row g-2" id="productsGrid">
        @foreach($starter as $p)
          <div class="col-6 col-md-4 col-xl-3">
            <button class="btn btn-outline-secondary w-100 product-btn"
                    data-id="{{ $p->id }}" data-name="{{ $p->name }}"
                    data-sku="{{ $p->sku }}" data-price="{{ (float)$p->sale_price }}">
              <div class="small text-muted">{{ $p->sku }}</div>
              <div class="fw-semibold text-wrap">{{ $p->name }}</div>
              <div class="small mt-1">₾ {{ number_format((float)$p->sale_price,2) }}</div>
            </button>
          </div>
        @endforeach
      </div>
    </div>

    {{-- RIGHT: Cart + Pay --}}
    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <div class="d-flex justify-content-between align-items-center">
            <strong>Cart</strong>
            <div class="d-flex gap-2">
              <select id="paymentMethod" class="form-select form-select-sm">
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="other">Other</option>
              </select>
              <button id="clearCart" class="btn btn-sm btn-outline-secondary">Clear</button>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0 cart-table">
            <thead class="table-light">
              <tr>
                <th>Item</th>
                <th width="110">Qty</th>
                <th width="120" class="text-end">Price</th>
                <th width="120" class="text-end">Line</th>
                <th width="40"></th>
              </tr>
            </thead>
            <tbody id="cartBody"></tbody>
          </table>
        </div>
        <div class="card-footer bg-white">
          <div class="d-flex justify-content-between">
            <div class="fw-bold fs-5">Total:</div>
            <div class="fw-bold fs-5" id="grandTotal">₾ 0.00</div>
          </div>

          <div class="row g-2 mt-3">
            <div class="col-8">
              <div class="keypad d-flex flex-wrap gap-2">
                <button class="btn btn-outline-secondary" data-key="7">7</button>
                <button class="btn btn-outline-secondary" data-key="8">8</button>
                <button class="btn btn-outline-secondary" data-key="9">9</button>
                <button class="btn btn-outline-secondary" data-key="4">4</button>
                <button class="btn btn-outline-secondary" data-key="5">5</button>
                <button class="btn btn-outline-secondary" data-key="6">6</button>
                <button class="btn btn-outline-secondary" data-key="1">1</button>
                <button class="btn btn-outline-secondary" data-key="2">2</button>
                <button class="btn btn-outline-secondary" data-key="3">3</button>
                <button class="btn btn-outline-secondary" data-key="0">0</button>
                <button class="btn btn-outline-secondary" data-key=".">.</button>
                <button class="btn btn-outline-danger"    id="qtyBack">⌫</button>
              </div>
              <div class="small text-muted mt-1">Tip: click a cart row, then use keypad to set quantity.</div>
            </div>
            <div class="col-4 d-grid">
              <button id="payBtn" class="btn btn-success btn-lg">Pay & Print</button>
            </div>
          </div>
        </div>
      </div>

      {{-- Quick last receipts (optional simple list) --}}
      <div class="card shadow-sm mt-3 d-none" id="lastSaleCard">
        <div class="card-body small" id="lastSaleInfo"></div>
      </div>
    </div>
  </div>
</div>

{{-- Simple receipt template (printed via a new window) --}}
<template id="receiptTpl">
  <div style="font-family: ui-sans-serif, system-ui; width: 280px;">
    <h3 style="text-align:center; margin:0 0 8px 0;">Inventory Pro</h3>
    <div style="text-align:center; font-size:12px; margin-bottom:8px;">
      {{ now()->format('Y-m-d H:i') }} • Cashier: {{ auth()->user()->name }}
    </div>
    <table style="width:100%; font-size:12px;">
      <tbody data-lines></tbody>
    </table>
    <hr>
    <div style="display:flex; justify-content:space-between; font-weight:bold;">
      <span>Total</span><span data-total>₾ 0.00</span>
    </div>
    <div style="margin-top:8px; font-size:12px; text-align:center;">Thanks!</div>
  </div>
</template>

@push('scripts')
<script>
(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // --- cart state ---
  let cart = []; // [{product_id, name, sku, price, qty}]
  let selectedIndex = -1;

  const grid      = document.getElementById('productsGrid');
  const searchInp = document.getElementById('searchInput');
  const searchBtn = document.getElementById('searchBtn');
  const cartBody  = document.getElementById('cartBody');
  const grandEl   = document.getElementById('grandTotal');
  const payBtn    = document.getElementById('payBtn');
  const clearBtn  = document.getElementById('clearCart');
  const paymentMethod = document.getElementById('paymentMethod');

  // --- helpers ---
  const fmt = (n) => '₾ ' + (Number(n||0).toFixed(2));

  function renderCart(){
    cartBody.innerHTML = '';
    let total = 0;
    cart.forEach((row, i) => {
      const line = row.qty * row.price;
      total += line;

      const tr = document.createElement('tr');
      if (i === selectedIndex) tr.classList.add('table-active');

      tr.innerHTML = `
        <td>
          <div class="fw-semibold">${row.name}</div>
          <div class="small text-muted">${row.sku ?? ''}</div>
        </td>
        <td>
          <input type="text" class="form-control form-control-sm text-center" value="${row.qty}" data-idx="${i}" data-role="qty">
        </td>
        <td class="text-end">${fmt(row.price)}</td>
        <td class="text-end">${fmt(line)}</td>
        <td>
          <button class="btn btn-sm btn-link text-danger" data-idx="${i}" data-role="del">✕</button>
        </td>
      `;

      tr.addEventListener('click', () => { selectedIndex = i; renderCart(); });
      cartBody.appendChild(tr);
    });
    grandEl.textContent = fmt(total);
  }

  function addToCart(prod){
    const found = cart.findIndex(x => x.product_id === prod.id);
    if (found >= 0) {
      cart[found].qty = Number(cart[found].qty) + 1;
      selectedIndex = found;
    } else {
      cart.push({ product_id: prod.id, name: prod.name, sku: prod.sku, price: Number(prod.price), qty: 1 });
      selectedIndex = cart.length - 1;
    }
    renderCart();
  }

  // --- bind product buttons (starter grid) ---
  grid.addEventListener('click', (e) => {
    const btn = e.target.closest('.product-btn');
    if (!btn) return;
    addToCart({
      id:   Number(btn.dataset.id),
      name: btn.dataset.name,
      sku:  btn.dataset.sku,
      price:Number(btn.dataset.price)
    });
  });

  // --- search ---
  async function doSearch(){
    const q = searchInp.value.trim();
    if (!q) return;
    const url = `{{ route('pos.search') }}?q=` + encodeURIComponent(q);
    const res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
    const items = await res.json();

    grid.innerHTML = '';
    items.forEach(p => {
      const col = document.createElement('div');
      col.className = 'col-6 col-md-4 col-xl-3';
      col.innerHTML = `
        <button class="btn btn-outline-secondary w-100 product-btn"
                data-id="${p.id}" data-name="${p.name}" data-sku="${p.sku}" data-price="${p.price}">
          <div class="small text-muted">${p.sku}</div>
          <div class="fw-semibold text-wrap">${p.name}</div>
          <div class="small mt-1">₾ ${Number(p.price).toFixed(2)}</div>
          <div class="small ${p.soh<=0 ? 'text-danger' : 'text-muted'}">Stock: ${p.soh}</div>
        </button>
      `;
      grid.appendChild(col);
    });
  }
  searchBtn.addEventListener('click', doSearch);
  searchInp.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ doSearch(); }});

  // --- cart events (qty edit / delete) ---
  cartBody.addEventListener('input', (e)=>{
    const role = e.target.dataset.role;
    if (role === 'qty') {
      const idx = Number(e.target.dataset.idx);
      let v = e.target.value.replace(',', '.');
      v = v === '' ? 0 : Number(v);
      if (isNaN(v) || v < 0) v = 0;
      cart[idx].qty = v;
      renderCart();
    }
  });
  cartBody.addEventListener('click', (e)=>{
    const role = e.target.dataset.role;
    if (role === 'del') {
      const idx = Number(e.target.dataset.idx);
      cart.splice(idx,1);
      selectedIndex = -1;
      renderCart();
    }
  });

  // --- keypad for quantity ---
  document.querySelector('.keypad').addEventListener('click',(e)=>{
    const key = e.target.dataset.key;
    if (!key && e.target.id!=='qtyBack') return;
    if (selectedIndex < 0) return;

    let cur = String(cart[selectedIndex].qty ?? '0');
    if (e.target.id==='qtyBack') {
      cur = cur.slice(0,-1);
      if (cur === '' || cur === '0') cur = '0';
    } else {
      cur = (cur === '0') ? key : (cur + key);
    }
    if (cur === '.') cur = '0.';
    cart[selectedIndex].qty = Number(cur);
    renderCart();
  });

  // --- clear cart ---
  clearBtn.addEventListener('click', ()=>{
    cart = [];
    selectedIndex = -1;
    renderCart();
  });

  // --- pay & print ---
  async function submitSale(){
    if (cart.length === 0) return alert('Cart is empty.');
    const payload = {
      items: cart.map(x => ({ product_id: x.product_id, qty: x.qty, price: x.price })),
      payment_method: paymentMethod.value,
    };
    const res = await fetch(`{{ route('pos.sale') }}`, {
      method: 'POST',
      headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With':'XMLHttpRequest' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (!json.ok) {
      return alert('Error saving sale: ' + (json.error || 'Unknown'));
    }

    printReceipt(cart, json.total);
    cart = [];
    selectedIndex = -1;
    renderCart();

    // show mini confirmation
    const info = document.getElementById('lastSaleInfo');
    const card = document.getElementById('lastSaleCard');
    info.textContent = `Sale #${json.sale_id} saved • Total ${json.total} • ${json.time}`;
    card.classList.remove('d-none');
    window.scrollTo({ top:0, behavior:'smooth' });
  }
  payBtn.addEventListener('click', submitSale);

  // --- print ---
  function printReceipt(items, total){
    const tpl = document.getElementById('receiptTpl').content.cloneNode(true);
    const linesEl = tpl.querySelector('[data-lines]');
    const totalEl = tpl.querySelector('[data-total]');

    items.forEach(it=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${escapeHtml(it.name)} × ${it.qty}</td>
        <td style="text-align:right;">${(it.qty * it.price).toFixed(2)}</td>
      `;
      linesEl.appendChild(tr);
    });
    totalEl.textContent = '₾ ' + Number(total).toFixed(2);

    const w = window.open('', '_blank', 'width=320,height=600');
    w.document.write('<!doctype html><html><head><meta charset="utf-8"><title>Receipt</title></head><body>');
    w.document.body.appendChild(tpl);
    w.document.write('</body></html>');
    w.document.close();
    w.focus();
    w.print();
    w.close();
  }

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  // initial render
  renderCart();
})();
</script>
@endpush
@endsection
