@extends('layouts.landing')
@section('title', 'Shop - Kopa Arena')

@push('styles')
<style>
.shop-hero { background: linear-gradient(135deg, var(--ka-dark) 0%, #1b4332 100%); padding: 140px 0 60px; color: #fff; }
.shop-hero h1 { font-size: 2.5rem; font-weight: 800; }
.product-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 5px 25px rgba(0,0,0,0.06); transition: all 0.3s; border: 1px solid #f0f0f0; height: 100%; }
.product-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.12); }
.product-card img { width: 100%; height: 220px; object-fit: cover; }
.product-card .card-body { padding: 20px; }
.product-card .product-name { font-weight: 700; font-size: 1rem; color: var(--ka-dark); text-decoration: none; display: block; margin-bottom: 5px; }
.product-card .product-name:hover { color: var(--ka-primary); }
.product-card .product-branch { font-size: 0.8rem; color: #6c757d; margin-bottom: 8px; }
.product-card .product-price { font-size: 1.15rem; font-weight: 700; color: var(--ka-primary); }
.product-card .product-price small { font-size: 0.75rem; font-weight: 400; color: #999; }
.filter-section { padding: 30px 0; background: var(--ka-light); }
.no-img-placeholder { width: 100%; height: 220px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #adb5bd; font-size: 3rem; }
</style>
@endpush

@section('content')
<section class="shop-hero">
    <div class="container text-center">
        <h1>Kopa Arena <span style="color:var(--ka-primary);">Shop</span></h1>
        <p class="text-white-50">Jerseys, balls, accessories and more</p>
    </div>
</section>

<section class="filter-section">
    <div class="container">
        <form method="GET" action="{{ route('shop.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search products...">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Branch</label>
                <select name="branch" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-find w-100"><i class="fas fa-search me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        @if($products->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <p class="text-muted fs-5">No products found.</p>
            </div>
        @else
        <div class="row g-4">
            @foreach($products as $product)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="product-card">
                    <a href="{{ route('shop.show', $product->slug) }}">
                        @if($product->image)
                            <img src="{{ asset($product->image) }}" alt="{{ $product->name }}">
                        @else
                            <div class="no-img-placeholder"><i class="fas fa-image"></i></div>
                        @endif
                    </a>
                    <div class="card-body">
                        <div class="product-branch"><i class="fas fa-store me-1"></i>{{ $product->branch->name ?? '' }}</div>
                        <a href="{{ route('shop.show', $product->slug) }}" class="product-name">{{ $product->name }}</a>
                        @if($product->category)
                            <span class="badge bg-light text-dark mb-2" style="font-size:0.75rem;">{{ $product->category->name }}</span>
                        @endif
                        <div class="product-price">
                            RM {{ number_format($product->getEffectivePrice(), 2) }}
                            @if($product->has_variation)
                                <small>from</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
@endsection
