@extends('layouts.admin')
@section('title', 'Products - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Products</h2>
            <a href="{{ route('products.create') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-plus"></i>add product</a>
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
                        <th>Image</th>
                        <th>Name</th>
                        <th>Branch</th>
                        <th>Category</th>
                        <th>Price (RM)</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>
                            @if($product->image)
                                <img src="{{ asset($product->image) }}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->branch->name ?? '-' }}</td>
                        <td>{{ $product->category->name ?? '-' }}</td>
                        <td>{{ number_format($product->getEffectivePrice(), 2) }}</td>
                        <td>{{ $product->getEffectiveStock() }}</td>
                        <td>
                            @if($product->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($product->status === 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @else
                                <span class="badge bg-danger">Out of Stock</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" data-confirm-delete>
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
