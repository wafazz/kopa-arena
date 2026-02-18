@extends('layouts.admin')
@section('title', 'Edit Product - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Edit Product</h2>
            <a href="{{ route('branch.products.index') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-arrow-left"></i>back</a>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <form action="{{ route('branch.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">No Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Main Image</label>
                            @if($product->image)
                                <div class="mb-2">
                                    <img src="{{ asset($product->image) }}" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Add Gallery Images</label>
                            <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                        </div>
                    </div>

                    @if($product->images->count())
                    <div class="mb-3">
                        <label class="form-label">Current Gallery</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($product->images as $img)
                            <div class="position-relative" style="width:80px;">
                                <img src="{{ asset($img->image) }}" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
                                <div class="form-check mt-1">
                                    <input type="checkbox" name="remove_images[]" value="{{ $img->id }}" class="form-check-input" id="rmImg{{ $img->id }}">
                                    <label class="form-check-label small text-danger" for="rmImg{{ $img->id }}">Remove</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" name="weight" class="form-control" value="{{ old('weight', $product->weight) }}" step="0.01" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="out_of_stock" {{ old('status', $product->status) === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input type="hidden" name="has_variation" value="0">
                                <input type="checkbox" name="has_variation" value="1" class="form-check-input" id="hasVariation" {{ old('has_variation', $product->has_variation) ? 'checked' : '' }}>
                                <label class="form-check-label" for="hasVariation">Has Variations</label>
                            </div>
                        </div>
                    </div>

                    <div id="noVariationFields" class="{{ old('has_variation', $product->has_variation) ? 'd-none' : '' }}">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price (RM) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}" step="0.01" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stock <span class="text-danger">*</span></label>
                                <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                            </div>
                        </div>
                    </div>

                    <div id="variationFields" class="{{ old('has_variation', $product->has_variation) ? '' : 'd-none' }}">
                        <h5 class="mb-3">Variations</h5>
                        <table class="table table-bordered" id="variationTable">
                            <thead>
                                <tr>
                                    <th>Name <span class="text-danger">*</span></th>
                                    <th>SKU</th>
                                    <th>Price (RM) <span class="text-danger">*</span></th>
                                    <th>Stock <span class="text-danger">*</span></th>
                                    <th style="width:60px;"></th>
                                </tr>
                            </thead>
                            <tbody id="variationBody">
                                @foreach($product->variations as $i => $v)
                                <tr>
                                    <td>
                                        <input type="hidden" name="variations[{{ $i }}][id]" value="{{ $v->id }}">
                                        <input type="text" name="variations[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $v->name }}" required>
                                    </td>
                                    <td><input type="text" name="variations[{{ $i }}][sku]" class="form-control form-control-sm" value="{{ $v->sku }}"></td>
                                    <td><input type="number" name="variations[{{ $i }}][price]" class="form-control form-control-sm" value="{{ $v->price }}" step="0.01" min="0" required></td>
                                    <td><input type="number" name="variations[{{ $i }}][stock]" class="form-control form-control-sm" value="{{ $v->stock }}" min="0" required></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-var"><i class="fas fa-times"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-success" id="addVariation">
                            <i class="fas fa-plus"></i> Add Variation
                        </button>
                    </div>

                    <hr class="my-4">
                    <button type="submit" class="au-btn au-btn--green">Update Product</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var varIdx = {{ $product->variations->count() }};
document.getElementById('hasVariation').addEventListener('change', function() {
    document.getElementById('noVariationFields').classList.toggle('d-none', this.checked);
    document.getElementById('variationFields').classList.toggle('d-none', !this.checked);
});
document.getElementById('addVariation').addEventListener('click', function() {
    var row = '<tr>' +
        '<td><input type="text" name="variations[' + varIdx + '][name]" class="form-control form-control-sm" required></td>' +
        '<td><input type="text" name="variations[' + varIdx + '][sku]" class="form-control form-control-sm"></td>' +
        '<td><input type="number" name="variations[' + varIdx + '][price]" class="form-control form-control-sm" step="0.01" min="0" required></td>' +
        '<td><input type="number" name="variations[' + varIdx + '][stock]" class="form-control form-control-sm" min="0" value="0" required></td>' +
        '<td><button type="button" class="btn btn-sm btn-outline-danger remove-var"><i class="fas fa-times"></i></button></td>' +
        '</tr>';
    document.getElementById('variationBody').insertAdjacentHTML('beforeend', row);
    varIdx++;
});
document.getElementById('variationBody').addEventListener('click', function(e) {
    var btn = e.target.closest('.remove-var');
    if (btn) btn.closest('tr').remove();
});
</script>
@endpush
