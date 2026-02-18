@extends('layouts.landing')
@section('title', 'Order Confirmed - Kopa Arena')

@push('styles')
<style>
.success-section { padding: 140px 0 60px; min-height: 80vh; }
.success-card { background: #fff; border-radius: 16px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); max-width: 700px; margin: 0 auto; text-align: center; }
.success-icon { width: 80px; height: 80px; background: rgba(26,135,84,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
.success-icon i { font-size: 2rem; color: var(--ka-primary); }
.order-detail-table { text-align: left; margin-top: 30px; }
.order-detail-table td { padding: 8px 12px; }
.order-detail-table .label { font-weight: 600; color: #6c757d; width: 40%; }
</style>
@endpush

@section('content')
<section class="success-section">
    <div class="container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="fw-bold mb-2">Order Placed!</h2>
            <p class="text-muted">Thank you for your order. Here are your order details.</p>

            <div class="order-detail-table">
                <table class="table table-borderless">
                    <tr>
                        <td class="label">Order Number</td>
                        <td class="fw-bold">{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Branch</td>
                        <td>{{ $order->branch->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Customer</td>
                        <td>{{ $order->customer_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Phone</td>
                        <td>{{ $order->customer_phone }}</td>
                    </tr>
                    <tr>
                        <td class="label">Delivery</td>
                        <td>{{ ucfirst($order->delivery_method) }}</td>
                    </tr>
                    @if($order->delivery_method === 'shipping')
                    <tr>
                        <td class="label">Ship To</td>
                        <td>{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postcode }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">Payment</td>
                        <td>
                            @if($order->payment_status === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-warning text-dark">Unpaid</span>
                            @endif
                            ({{ ucfirst($order->payment_type) }})
                        </td>
                    </tr>
                </table>

                <h5 class="fw-bold text-start mt-4 mb-3">Items</h5>
                <table class="table text-start">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}@if($item->variation_name) <small class="text-muted">({{ $item->variation_name }})</small>@endif</td>
                            <td>{{ $item->quantity }}</td>
                            <td class="text-end">RM {{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="2">Total</td>
                            <td class="text-end" style="color:var(--ka-primary);">RM {{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4">
                <a href="{{ route('shop.index') }}" class="btn-hero" style="font-size:0.95rem;padding:12px 30px;">
                    <i class="fas fa-shopping-bag me-1"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
