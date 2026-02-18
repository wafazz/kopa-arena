@extends('layouts.admin')
@section('title', 'Edit Profile - Kopa Arena')

@push('styles')
<style>
.edit-card {
    background: linear-gradient(135deg, #1a2332 0%, #0d1b2a 100%);
    border: 1px solid rgba(45, 212, 137, 0.15);
    border-radius: 16px;
    padding: 30px;
}
.edit-card .title-2 { margin-bottom: 25px; }
.avatar-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 3px solid rgba(45, 212, 137, 0.4);
    object-fit: cover;
    background: #1f2940;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 12px;
}
.avatar-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.avatar-preview .avatar-icon {
    font-size: 36px;
    color: #8e9ab5;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Edit Profile</h2>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="edit-card">
            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('patch')

                <div class="mb-4">
                    <label class="form-label">Profile Image</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-preview" id="avatarPreview">
                            @if($user->profile_image)
                                <img src="{{ asset($user->profile_image) }}" alt="{{ $user->name }}" id="previewImg">
                            @else
                                <i class="fas fa-user avatar-icon" id="previewIcon"></i>
                            @endif
                        </div>
                        <div>
                            <input type="file" name="profile_image" id="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/jpg,image/jpeg,image/png">
                            <small class="text-muted d-block mt-1">JPG, JPEG or PNG. Max 2MB.</small>
                            @error('profile_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="au-btn au-btn--green">Save Changes</button>
                    <a href="{{ route('profile.show') }}" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('profile_image').addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(ev) {
        var container = document.getElementById('avatarPreview');
        var icon = document.getElementById('previewIcon');
        var img = document.getElementById('previewImg');
        if (icon) icon.remove();
        if (img) {
            img.src = ev.target.result;
        } else {
            img = document.createElement('img');
            img.id = 'previewImg';
            img.src = ev.target.result;
            img.alt = 'Preview';
            container.appendChild(img);
        }
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
