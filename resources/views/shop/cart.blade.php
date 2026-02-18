@extends('layouts.landing')
@section('title', 'Shopping Cart - Kopa Arena')

@push('styles')
<style>
.cart-section { padding: 140px 0 60px; min-height: 80vh; }
.cart-item { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 15px rgba(0,0,0,0.06); display: flex; align-items: center; gap: 20px; }
.cart-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
.cart-item .item-info { flex: 1; }
.cart-item .item-name { font-weight: 700; color: var(--ka-dark); }
.cart-item .item-variation { font-size: 0.85rem; color: #6c757d; }
.cart-item .item-price { font-weight: 700; color: var(--ka-primary); font-size: 1.1rem; min-width: 120px; text-align: right; }
.cart-summary { background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 5px 25px rgba(0,0,0,0.08); position: sticky; top: 100px; }
.cart-summary h4 { font-weight: 700; margin-bottom: 20px; }
.cart-summary .total-row { display: flex; justify-content: space-between; padding: 10px 0; border-top: 2px solid #e9ecef; font-weight: 700; font-size: 1.2rem; }
.qty-control { display: flex; align-items: center; gap: 8px; }
.qty-control input { width: 60px; text-align: center; border: 1px solid #dee2e6; border-radius: 6px; padding: 4px; }
.qty-control button { width: 30px; height: 30px; border: 1px solid #dee2e6; background: #fff; border-radius: 6px; cursor: pointer; }
.no-img-sm { width: 80px; height: 80px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #adb5bd; border-radius: 8px; }
</style>
@endpush

@section('content')
<section class="cart-section">
    <div class="container">
        <div class="mb-3">
            <a href="{{ route('shop.index') }}" class="text-decoration-none" style="color:var(--ka-primary);font-weight:600;">
                <i class="fas fa-arrow-left me-1"></i> Continue Shopping
            </a>
        </div>
        <h2 class="fw-bold mb-4">Shopping Cart</h2>

        @if(empty($cart))
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <p class="text-muted fs-5">Your cart is empty.</p>
                <a href="{{ route('shop.index') }}" class="btn-hero" style="font-size:0.95rem;padding:10px 30px;">Browse Products</a>
            </div>
        @else
        <div class="row g-4">
            <div class="col-lg-8">
                @foreach($cart as $key => $item)
                <div class="cart-item" id="item-{{ $key }}">
                    @if($item['image'])
                        <img src="{{ asset($item['image']) }}" alt="">
                    @else
                        <div class="no-img-sm"><i class="fas fa-image"></i></div>
                    @endif
                    <div class="item-info">
                        <div class="item-name">{{ $item['name'] }}</div>
                        @if($item['variation_name'])
                            <div class="item-variation">{{ $item['variation_name'] }}</div>
                        @endif
                        <div class="text-muted small">{{ $item['branch_name'] }}</div>
                        <div class="qty-control mt-2">
                            <button type="button" onclick="updateQty('{{ $key }}', -1)">-</button>
                            <input type="number" id="qty-{{ $key }}" value="{{ $item['quantity'] }}" min="1" max="{{ $item['max_stock'] }}" onchange="updateQtyDirect('{{ $key }}')">
                            <button type="button" onclick="updateQty('{{ $key }}', 1)">+</button>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeItem('{{ $key }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="item-price" id="price-{{ $key }}">RM {{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                </div>
                @endforeach
            </div>
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h4>Order Summary</h4>
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span id="cartSubtotal">RM {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <a href="{{ route('shop.checkout') }}" class="btn-hero d-block text-center mt-3" style="font-size:1rem;padding:14px;">
                        Proceed to Checkout <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
var cartPrices = @json(collect($cart)->mapWithKeys(fn($item, $key) => [$key => $item['price']]));

function updateQty(key, delta) {
    var inp = document.getElementById('qty-' + key);
    var newQty = parseInt(inp.value) + delta;
    if (newQty < 1 || newQty > parseInt(inp.max)) return;
    inp.value = newQty;
    sendUpdate(key, newQty);
}

function updateQtyDirect(key) {
    var inp = document.getElementById('qty-' + key);
    var val = parseInt(inp.value);
    if (val < 1) { inp.value = 1; val = 1; }
    if (val > parseInt(inp.max)) { inp.value = inp.max; val = parseInt(inp.max); }
    sendUpdate(key, val);
}

function sendUpdate(key, qty) {
    fetch('{{ route("shop.cart.update") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: JSON.stringify({ key: key, quantity: qty })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            document.getElementById('price-' + key).textContent = 'RM ' + (cartPrices[key] * qty).toFixed(2);
            document.getElementById('cartSubtotal').textContent = 'RM ' + data.subtotal;
            var badge = document.getElementById('cartBadge');
            if (badge) badge.textContent = data.cart_count;
        }
    });
}

function removeItem(key) {
    fetch('{{ route("shop.cart.remove") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: JSON.stringify({ key: key })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            document.getElementById('item-' + key).remove();
            document.getElementById('cartSubtotal').textContent = 'RM ' + data.subtotal;
            var badge = document.getElementById('cartBadge');
            if (badge) badge.textContent = data.cart_count;
            if (data.cart_count == 0) location.reload();
        }
    });
}
</script>
@endpush
