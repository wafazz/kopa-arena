@extends('layouts.landing')
@section('title', 'Checkout - Kopa Arena')

@push('styles')
<style>
.checkout-section { padding: 140px 0 60px; min-height: 80vh; }
.checkout-card { background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 5px 25px rgba(0,0,0,0.08); }
.checkout-card h4 { font-weight: 700; margin-bottom: 20px; }
.order-summary { background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 5px 25px rgba(0,0,0,0.08); position: sticky; top: 100px; }
.order-summary h4 { font-weight: 700; margin-bottom: 20px; }
.summary-item { display: flex; justify-content: space-between; padding: 8px 0; font-size: 0.9rem; border-bottom: 1px solid #f0f0f0; }
.summary-item:last-child { border-bottom: none; }
.summary-total { display: flex; justify-content: space-between; padding: 15px 0; border-top: 2px solid #e9ecef; font-weight: 700; font-size: 1.2rem; margin-top: 10px; }
.btn-place-order { background: var(--ka-primary); color: #fff; border: none; border-radius: 12px; padding: 14px 30px; font-weight: 700; width: 100%; font-size: 1.05rem; transition: all 0.3s; }
.btn-place-order:hover { background: var(--ka-primary-dark); color: #fff; }
</style>
@endpush

@section('content')
<section class="checkout-section">
    <div class="container">
        <div class="mb-3">
            <a href="{{ route('shop.cart') }}" class="text-decoration-none" style="color:var(--ka-primary);font-weight:600;">
                <i class="fas fa-arrow-left me-1"></i> Back to Cart
            </a>
        </div>
        <h2 class="fw-bold mb-4">Checkout</h2>

        <form action="{{ route('shop.processCheckout') }}" method="POST">
            @csrf
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="checkout-card mb-4">
                        <h4><i class="fas fa-user me-2" style="color:var(--ka-primary);"></i>Customer Details</h4>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}">
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card mb-4">
                        <h4><i class="fas fa-truck me-2" style="color:var(--ka-primary);"></i>Delivery Method</h4>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="delivery_method" value="pickup" id="deliveryPickup" {{ old('delivery_method', 'pickup') === 'pickup' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="deliveryPickup">
                                    <i class="fas fa-store me-1"></i> Pickup at Branch
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="delivery_method" value="shipping" id="deliveryShipping" {{ old('delivery_method') === 'shipping' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="deliveryShipping">
                                    <i class="fas fa-shipping-fast me-1"></i> Shipping
                                </label>
                            </div>
                        </div>

                        <div id="pickupInfo" class="{{ old('delivery_method') === 'shipping' ? 'd-none' : '' }}">
                            <div class="alert alert-success">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <strong>Pickup at:</strong> {{ $branch->name ?? 'Branch' }} - {{ $branch->address ?? 'Address will be provided' }}
                            </div>
                        </div>

                        <div id="shippingFields" class="{{ old('delivery_method') === 'shipping' ? '' : 'd-none' }}">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Address <span class="text-danger">*</span></label>
                                <textarea name="shipping_address" class="form-control" rows="2">{{ old('shipping_address') }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">City <span class="text-danger">*</span></label>
                                    <input type="text" name="shipping_city" class="form-control" value="{{ old('shipping_city') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">State <span class="text-danger">*</span></label>
                                    <input type="text" name="shipping_state" class="form-control" value="{{ old('shipping_state') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Postcode <span class="text-danger">*</span></label>
                                    <input type="text" name="shipping_postcode" class="form-control" value="{{ old('shipping_postcode') }}" maxlength="10">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card">
                        <h4><i class="fas fa-credit-card me-2" style="color:var(--ka-primary);"></i>Payment</h4>
                        @if($senangpayEnabled)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" value="online" id="payOnline" checked>
                            <label class="form-check-label fw-bold" for="payOnline">Online Payment (SenangPay)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="cash" id="payCash">
                            <label class="form-check-label fw-bold" for="payCash">Pay at Branch (Cash)</label>
                        </div>
                        @else
                        <input type="hidden" name="payment_method" value="cash">
                        <p class="text-muted">Pay at branch upon pickup/delivery.</p>
                        @endif
                        <div class="mb-3 mt-3">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any special instructions...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="order-summary">
                        <h4>Order Summary</h4>
                        @foreach($cart as $item)
                        <div class="summary-item">
                            <span>{{ $item['name'] }}@if($item['variation_name']) ({{ $item['variation_name'] }})@endif x{{ $item['quantity'] }}</span>
                            <span class="fw-bold">RM {{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                        </div>
                        @endforeach
                        <div class="summary-total">
                            <span>Total</span>
                            <span style="color:var(--ka-primary);">RM {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <button type="submit" class="btn-place-order mt-3">
                            <i class="fas fa-lock me-2"></i> Place Order
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.querySelectorAll('input[name="delivery_method"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.getElementById('pickupInfo').classList.toggle('d-none', this.value === 'shipping');
        document.getElementById('shippingFields').classList.toggle('d-none', this.value === 'pickup');
    });
});
</script>
@endpush
