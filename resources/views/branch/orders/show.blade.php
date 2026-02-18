@extends('layouts.admin')
@section('title', 'Order #' . $order->order_number . ' - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Order #{{ $order->order_number }}</h2>
            <a href="{{ route('branch.orders.index') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-arrow-left"></i>back</a>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-8">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h5 class="mb-3">Order Items</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                {{ $item->product_name }}
                                @if($item->variation_name)
                                    <br><small class="text-muted">{{ $item->variation_name }}</small>
                                @endif
                            </td>
                            <td>RM {{ number_format($item->price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>RM {{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Subtotal</td>
                            <td>RM {{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        @if($order->shipping_fee > 0)
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Shipping</td>
                            <td>RM {{ number_format($order->shipping_fee, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">Total</td>
                            <td>RM {{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h5 class="mb-3">Update Status</h5>
                <form action="{{ route('branch.orders.update-status', $order) }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach(['pending','paid','processing','shipped','completed','cancelled'] as $s)
                                <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tracking Number</label>
                        <input type="text" name="tracking_number" class="form-control" value="{{ $order->tracking_number }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="au-btn au-btn--green">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h5 class="mb-3">Customer Info</h5>
                <table class="table table-borderless table-sm">
                    <tr><td class="text-muted">Name</td><td>{{ $order->customer_name }}</td></tr>
                    <tr><td class="text-muted">Phone</td><td>{{ $order->customer_phone }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $order->customer_email ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h5 class="mb-3">Delivery & Payment</h5>
                <table class="table table-borderless table-sm">
                    <tr><td class="text-muted">Delivery</td><td>{{ ucfirst($order->delivery_method) }}</td></tr>
                    @if($order->delivery_method === 'shipping')
                    <tr><td class="text-muted">Address</td><td>{{ $order->shipping_address }}<br>{{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postcode }}</td></tr>
                    @endif
                    @if($order->tracking_number)
                    <tr><td class="text-muted">Tracking</td><td>{{ $order->tracking_number }}</td></tr>
                    @endif
                    <tr><td class="text-muted">Payment</td><td>{{ ucfirst($order->payment_type) }}</td></tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            @if($order->payment_status === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-warning text-dark">Unpaid</span>
                            @endif
                        </td>
                    </tr>
                    @if($order->paid_at)
                    <tr><td class="text-muted">Paid At</td><td>{{ $order->paid_at->format('d M Y H:i') }}</td></tr>
                    @endif
                    @if($order->transaction_id)
                    <tr><td class="text-muted">Transaction</td><td>{{ $order->transaction_id }}</td></tr>
                    @endif
                    @if($order->notes)
                    <tr><td class="text-muted">Notes</td><td>{{ $order->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        @php
            $statusColors = ['pending'=>'secondary','paid'=>'info','processing'=>'primary','shipped'=>'warning','completed'=>'success','cancelled'=>'danger'];
        @endphp
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h5 class="mb-3">Order Status</h5>
                <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}" style="font-size:1rem;padding:8px 16px;">{{ ucfirst($order->status) }}</span>
                <div class="text-muted small mt-2">Created: {{ $order->created_at->format('d M Y H:i') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
