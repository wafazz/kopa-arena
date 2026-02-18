@extends('layouts.admin')
@section('title', 'Orders - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Orders</h2>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-md-12">
        <div class="table-responsive table--no-card m-b-40">
            <table class="table table-borderless table-striped table-earning" data-datatable>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Total (RM)</th>
                        <th>Payment</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td><strong>{{ $order->order_number }}</strong></td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            @if($order->payment_status === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-warning text-dark">Unpaid</span>
                            @endif
                        </td>
                        <td>{{ ucfirst($order->delivery_method) }}</td>
                        <td>
                            @php
                                $statusColors = ['pending'=>'secondary','paid'=>'info','processing'=>'primary','shipped'=>'warning','completed'=>'success','cancelled'=>'danger'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('branch.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
