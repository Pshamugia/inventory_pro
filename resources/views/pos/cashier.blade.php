@extends('layouts.app')

@section('content')
<style>
    .pos-wrap {
        max-width: 1600px;
    }

    .pos-left {
        border-right: 1px solid #eee;
    }

    .product-btn {
        min-height: 72px
    }

    .cart-table td,
    .cart-table th {
        vertical-align: middle;
    }

    .keypad button {
        min-width: 64px;
        min-height: 56px
    }
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


                    <!-- Cash / Change row -->
                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="d-flex align-items-center gap-2">
                            <label for="cashGiven" class="small text-muted mb-0">Cash given</label>
                            <input id="cashGiven" type="number" step="0.01" class="form-control form-control-sm" style="width:140px" placeholder="0.00">
                        </div>
                        <div class="ms-auto">
                            <span class="small text-muted">Change:</span>
                            <span id="changeDue" class="fw-semibold">₾ 0.00</span>
                        </div>
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
                                <button class="btn btn-outline-danger" id="qtyBack">⌫</button>
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
  <div style="font-family: ui-sans-serif,system-ui,Arial; width: 220px; font-size:12px;">
    <div style="text-align:center;">
      <img src="{{ asset('logo.png') }}" style="height:40px;margin:6px auto;display:block" onerror="this.remove()">
      <div style="font-weight:700">{{ config('app.company.name') }}</div>
      <div>Tax: {{ config('app.company.tax') }}</div>
      <div style="margin-bottom:6px">{{ config('app.company.addr') }}</div>
      <div style="border-top:1px dashed #000;border-bottom:1px dashed #000;padding:4px 0;margin:6px 0">
        {{ now()->format('Y-m-d H:i') }} • Cashier: {{ auth()->user()->name }}
      </div>
    </div>

    <table style="width:100%">
      <tbody data-lines></tbody>
    </table>

    <div style="border-top:1px dashed #000;margin-top:6px;padding-top:6px">
      <div style="display:flex;justify-content:space-between">
        <span>Total</span><strong data-total>₾ 0.00</strong>
      </div>
      <div style="display:flex;justify-content:space-between">
        <span>Paid</span><span data-paid>₾ 0.00</span>
      </div>
      <div style="display:flex;justify-content:space-between">
        <span>Change</span><span data-change>₾ 0.00</span>
      </div>
      <div style="display:flex;justify-content:space-between">
        <span>Method</span><span data-method>—</span>
      </div>
    </div>

    <div style="text-align:center;margin-top:10px">
      <div>Thanks for your purchase! 💚</div>
      <div style="font-size:11px;margin-top:6px">Powered by Inventory-Pro</div>
    </div>
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

  // cash / change elements
  const cashGivenEl = document.getElementById('cashGiven');
  const changeDueEl = document.getElementById('changeDue');

  // --- helpers ---
  const fmt = (n) => '₾ ' + Number(n || 0).toFixed(2);
  const sum = (arr, prop) => arr.reduce((t, x) => t + Number(x[prop] || 0), 0);

  function currentTotal(){
    return cart.reduce((t, r) => t + (Number(r.qty) * Number(r.price)), 0);
  }

  function renderCart(){
    cartBody.innerHTML = '';
    let total = 0;
    cart.forEach((row, i) => {
      const line = Number(row.qty) * Number(row.price);
      total += line;

      const tr = document.createElement('tr');
      if (i === selectedIndex) tr.classList.add('table-active');

      tr.innerHTML =
        '<td>' +
          '<div class="fw-semibold">' + escapeHtml(row.name) + '</div>' +
          '<div class="small text-muted">' + (row.sku ? escapeHtml(row.sku) : '') + '</div>' +
        '</td>' +
        '<td>' +
          '<input type="text" class="form-control form-control-sm text-center" value="' + row.qty + '" data-idx="' + i + '" data-role="qty">' +
        '</td>' +
        '<td class="text-end">' + fmt(row.price) + '</td>' +
        '<td class="text-end">' + fmt(line) + '</td>' +
        '<td><button class="btn btn-sm btn-link text-danger" data-idx="' + i + '" data-role="del">✕</button></td>';

      tr.addEventListener('click', () => { selectedIndex = i; renderCart(); });
      cartBody.appendChild(tr);
    });

    grandEl.textContent = fmt(total);

    // live change recompute if cash is typed
    updateChange();
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
  if (grid) {
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
  }

  // --- search ---
  async function doSearch(){
    const q = (searchInp.value || '').trim();
    if (!q) return;
    const url = "{{ route('pos.search') }}" + "?q=" + encodeURIComponent(q);
    const res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
    const items = await res.json();

    grid.innerHTML = '';
    items.forEach(p => {
      const stockClass = (Number(p.soh) <= 0) ? 'text-danger' : 'text-muted';
      const col = document.createElement('div');
      col.className = 'col-6 col-md-4 col-xl-3';
      col.innerHTML =
        '<button class="btn btn-outline-secondary w-100 product-btn" ' +
          'data-id="' + p.id + '" data-name="' + escapeHtml(p.name) + '" data-sku="' + (p.sku||'') + '" data-price="' + p.price + '">' +
          '<div class="small text-muted">' + (p.sku||'') + '</div>' +
          '<div class="fw-semibold text-wrap">' + escapeHtml(p.name) + '</div>' +
          '<div class="small mt-1">₾ ' + Number(p.price).toFixed(2) + '</div>' +
          '<div class="small ' + stockClass + '">Stock: ' + (p.soh ?? 0) + '</div>' +
        '</button>';
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
      let v = String(e.target.value || '').replace(',', '.');
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

  // --- live change calculator ---
  function updateChange(){
    const total = currentTotal();
    const given = Number(cashGivenEl.value || 0);
    const change = Math.max(0, given - total);
    changeDueEl.textContent = fmt(change);
  }
  cashGivenEl.addEventListener('input', updateChange);

  // --- pay & print ---
  async function submitSale(){
    if (cart.length === 0) return alert('Cart is empty.');

    const total = currentTotal();

    // prevent underpayment when cash
    if (paymentMethod.value === 'cash') {
      const given = Number(cashGivenEl.value || 0);
      if (given < total) {
        return alert('Cash given is less than total.');
      }
    }

    const payload = {
      items: cart.map(x => ({ product_id: x.product_id, qty: x.qty, price: x.price })),
      payment_method: paymentMethod.value,
      cash_given: Number(cashGivenEl.value || 0)
    };

    const res = await fetch("{{ route('pos.sale') }}", {
      method: 'POST',
      headers: {
        'Content-Type':'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With':'XMLHttpRequest'
      },
      body: JSON.stringify(payload)
    });

    let json;
    try {
      json = await res.json();
    } catch (e) {
      return alert('Server response was not JSON.');
    }

    if (!json || json.ok !== true) {
      return alert('Error saving sale: ' + (json && json.error ? json.error : 'Unknown'));
    }

    // SUCCESS branch
    try {
printReceipt(cart, json.total, {
  cash_given: json.cash_given,
  change:     json.change,
  payment_method: json.payment_method
});
      cart = [];
      selectedIndex = -1;
      renderCart();
      cashGivenEl.value = '';
      updateChange();

      const info = document.getElementById('lastSaleInfo');
      const card = document.getElementById('lastSaleCard');
      const extra = (typeof json.cash_given !== 'undefined' && typeof json.change !== 'undefined')
        ? ` • Cash given: ₾ ${Number(json.cash_given).toFixed(2)} • Change: ₾ ${Number(json.change).toFixed(2)}`
        : '';
      info.textContent = `Sale #${json.sale_id} • ${ (json.payment_method || 'cash').toUpperCase() } • Total ₾ ${Number(json.total).toFixed(2)} • ${json.time}${extra}`;
      card.classList.remove('d-none');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (e) {
      alert('Saved, but printing failed: ' + e.message);
    }
  }
  payBtn.addEventListener('click', submitSale);

  // --- print ---
  function printReceipt(items, total, meta = {}) {
  const tpl = document.getElementById('receiptTpl').content.cloneNode(true);
  const linesEl = tpl.querySelector('[data-lines]');
  const totalEl = tpl.querySelector('[data-total]');
  const paidEl  = tpl.querySelector('[data-paid]');
  const chgEl   = tpl.querySelector('[data-change]');
  const mthEl   = tpl.querySelector('[data-method]');

  items.forEach(it => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="padding-right:6px;">
        <div><strong>${escapeHtml(it.name)}</strong></div>
        <div style="font-size:11px;color:#555">${escapeHtml(it.sku || '')}</div>
      </td>
      <td style="text-align:right;white-space:nowrap">
        ${Number(it.qty).toFixed(2)} × ${Number(it.price).toFixed(2)} =
        <strong>${(it.qty * it.price).toFixed(2)}</strong>
      </td>
    `;
    linesEl.appendChild(tr);
  });

  totalEl.textContent = '₾ ' + Number(total).toFixed(2);
  paidEl.textContent  = '₾ ' + Number(meta.cash_given || 0).toFixed(2);
  chgEl.textContent   = '₾ ' + Number(meta.change || 0).toFixed(2);
  mthEl.textContent   = meta.payment_method || '-';

  const w = window.open('', '_blank', 'width=260,height=700');
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