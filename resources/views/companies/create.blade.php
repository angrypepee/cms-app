@extends('layouts.app')
@section('title', 'Tambah Perusahaan')
@section('page-title', 'Tambah Perusahaan')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-building-add me-2 text-primary"></i>Data Perusahaan</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('companies.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="PT Contoh Makmur Sejahtera" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tagline</label>
                            <input type="text" name="tagline" class="form-control @error('tagline') is-invalid @enderror" value="{{ old('tagline') }}" placeholder="Slogan perusahaan">
                            @error('tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">No. Telepon</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="021-12345678">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Alamat</label>
                            <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="Alamat lengkap perusahaan">{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="info@perusahaan.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Website</label>
                            <input type="text" name="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website') }}" placeholder="https://www.perusahaan.com">
                            @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Logo Perusahaan</label>
                            <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                            <div class="form-text">Format: JPG, PNG, SVG. Maks 2MB.</div>
                            @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 d-flex gap-2 justify-content-end pt-2">
                            <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan Perusahaan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
