@extends('layouts.landing')
@section('title', $product->name . ' - Kopa Arena Shop')

@push('styles')
<style>
.shop-detail { padding: 140px 0 60px; }
.product-gallery img { width: 100%; border-radius: 12px; object-fit: cover; }
.main-image { height: 400px; margin-bottom: 15px; }
.thumb-image { width: 80px; height: 80px; border-radius: 8px; cursor: pointer; border: 2px solid transparent; transition: border-color 0.3s; }
.thumb-image:hover, .thumb-image.active { border-color: var(--ka-primary); }
.product-info h1 { font-size: 1.8rem; font-weight: 800; color: var(--ka-dark); }
.product-info .price { font-size: 1.6rem; font-weight: 700; color: var(--ka-primary); margin: 15px 0; }
.product-info .branch-badge { display: inline-block; background: rgba(26,135,84,0.1); color: var(--ka-primary); padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
.variation-btn { border: 2px solid #dee2e6; background: #fff; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
.variation-btn:hover, .variation-btn.active { border-color: var(--ka-primary); background: rgba(26,135,84,0.05); color: var(--ka-primary); }
.variation-btn.out-of-stock { opacity: 0.5; cursor: not-allowed; text-decoration: line-through; }
.btn-add-cart { background: var(--ka-primary); color: #fff; border: none; border-radius: 12px; padding: 14px 40px; font-weight: 700; font-size: 1.05rem; transition: all 0.3s; }
.btn-add-cart:hover { background: var(--ka-primary-dark); color: #fff; transform: translateY(-2px); }
.btn-add-cart:disabled { opacity: 0.5; cursor: not-allowed; }
.related-section { padding: 60px 0; background: var(--ka-light); }
.no-img-lg { width: 100%; height: 400px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #adb5bd; font-size: 4rem; border-radius: 12px; }
</style>
@endpush

@section('content')
<section class="shop-detail">
    <div class="container">
        <div class="mb-3">
            <a href="{{ route('shop.index') }}" class="text-decoration-none" style="color:var(--ka-primary);font-weight:600;">
                <i class="fas fa-arrow-left me-1"></i> Back to Shop
            </a>
        </div>
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="product-gallery">
                    @if($product->image)
                        <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="main-image" id="mainImage">
                    @else
                        <div class="no-img-lg" id="mainImage"><i class="fas fa-image"></i></div>
                    @endif
                    @if($product->images->count())
                    <div class="d-flex gap-2 flex-wrap">
                        @if($product->image)
                        <img src="{{ asset($product->image) }}" class="thumb-image active" onclick="changeImage(this)">
                        @endif
                        @foreach($product->images as $img)
                        <img src="{{ asset($img->image) }}" class="thumb-image" onclick="changeImage(this)">
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-6">
                <div class="product-info">
                    <span class="branch-badge mb-3"><i class="fas fa-store me-1"></i>{{ $product->branch->name ?? '' }}</span>
                    @if($product->category)
                        <span class="badge bg-light text-dark ms-2">{{ $product->category->name }}</span>
                    @endif
                    <h1 class="mt-3">{{ $product->name }}</h1>
                    <div class="price" id="displayPrice">RM {{ number_format($product->getEffectivePrice(), 2) }}</div>

                    @if($product->description)
                        <p class="text-muted mb-4">{{ $product->description }}</p>
                    @endif

                    <form id="addToCartForm">
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="variation_id" id="variationId" value="">

                        @if($product->has_variation && $product->variations->count())
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Option:</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($product->variations as $v)
                                <button type="button"
                                    class="variation-btn {{ $v->stock < 1 ? 'out-of-stock' : '' }}"
                                    data-id="{{ $v->id }}"
                                    data-price="{{ $v->price }}"
                                    data-stock="{{ $v->stock }}"
                                    {{ $v->stock < 1 ? 'disabled' : '' }}>
                                    {{ $v->name }}
                                    @if($v->stock < 1) (Out of Stock) @endif
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-bold">Quantity:</label>
                            <div class="d-flex align-items-center gap-3">
                                <button type="button" class="btn btn-outline-secondary" id="qtyMinus">-</button>
                                <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="{{ $product->has_variation ? 0 : $product->stock }}" class="form-control text-center" style="width:80px;">
                                <button type="button" class="btn btn-outline-secondary" id="qtyPlus">+</button>
                                <span class="text-muted small" id="stockInfo">
                                    @if(!$product->has_variation)
                                        {{ $product->stock }} available
                                    @endif
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn-add-cart" id="addCartBtn" {{ $product->has_variation ? 'disabled' : '' }}>
                            <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                        </button>
                    </form>

                    @if($product->weight > 0)
                    <div class="mt-4 text-muted small">
                        <i class="fas fa-weight-hanging me-1"></i> Weight: {{ $product->weight }} kg
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@if($related->count())
<section class="related-section">
    <div class="container">
        <h3 class="fw-bold mb-4">More from this branch</h3>
        <div class="row g-4">
            @foreach($related as $r)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="product-card" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 5px 25px rgba(0,0,0,0.06);border:1px solid #f0f0f0;">
                    <a href="{{ route('shop.show', $r->slug) }}">
                        @if($r->image)
                            <img src="{{ asset($r->image) }}" alt="{{ $r->name }}" style="width:100%;height:200px;object-fit:cover;">
                        @else
                            <div style="width:100%;height:200px;background:#e9ecef;display:flex;align-items:center;justify-content:center;color:#adb5bd;font-size:2rem;"><i class="fas fa-image"></i></div>
                        @endif
                    </a>
                    <div style="padding:15px;">
                        <a href="{{ route('shop.show', $r->slug) }}" style="font-weight:700;color:var(--ka-dark);text-decoration:none;">{{ $r->name }}</a>
                        <div style="color:var(--ka-primary);font-weight:700;margin-top:8px;">RM {{ number_format($r->getEffectivePrice(), 2) }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

@push('scripts')
<script>
function changeImage(el) {
    var main = document.getElementById('mainImage');
    main.src = el.src;
    document.querySelectorAll('.thumb-image').forEach(function(t) { t.classList.remove('active'); });
    el.classList.add('active');
}

// Variation selection
document.querySelectorAll('.variation-btn:not(.out-of-stock)').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.variation-btn').forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        document.getElementById('variationId').value = this.dataset.id;
        document.getElementById('displayPrice').textContent = 'RM ' + parseFloat(this.dataset.price).toFixed(2);
        document.getElementById('qtyInput').max = this.dataset.stock;
        document.getElementById('qtyInput').value = 1;
        document.getElementById('stockInfo').textContent = this.dataset.stock + ' available';
        document.getElementById('addCartBtn').disabled = false;
    });
});

// Quantity controls
document.getElementById('qtyMinus').addEventListener('click', function() {
    var inp = document.getElementById('qtyInput');
    if (parseInt(inp.value) > 1) inp.value = parseInt(inp.value) - 1;
});
document.getElementById('qtyPlus').addEventListener('click', function() {
    var inp = document.getElementById('qtyInput');
    var max = parseInt(inp.max) || 9999;
    if (parseInt(inp.value) < max) inp.value = parseInt(inp.value) + 1;
});

// Add to cart AJAX
document.getElementById('addToCartForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    fetch('{{ route("shop.cart.add") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: formData
    })
    .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
    .then(function(res) {
        if (res.ok) {
            Swal.fire({ icon: 'success', title: 'Added!', text: res.data.message, confirmButtonColor: '#1a8754', timer: 1500 });
            var badge = document.getElementById('cartBadge');
            if (badge) { badge.textContent = res.data.cart_count; badge.style.display = 'inline-block'; }
        } else {
            Swal.fire({ icon: 'error', title: 'Oops!', text: res.data.message, confirmButtonColor: '#dc3545' });
        }
    });
});
</script>
@endpush
