@extends('layouts.app')
@section('title', 'Edit Perusahaan')
@section('page-title', 'Edit Perusahaan')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit: {{ $company->name }}</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('companies.update', $company) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $company->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tagline</label>
                            <input type="text" name="tagline" class="form-control @error('tagline') is-invalid @enderror" value="{{ old('tagline', $company->tagline) }}">
                            @error('tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">No. Telepon</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $company->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Alamat</label>
                            <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $company->address) }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $company->email) }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Website</label>
                            <input type="text" name="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $company->website) }}">
                            @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Logo Perusahaan</label>
                            @if($company->logo)
                                <div class="mb-2 d-flex align-items-center gap-3">
                                    <img src="{{ asset('storage/'.$company->logo) }}" style="height:56px;width:56px;object-fit:contain;border:1px solid #e2e8f0;border-radius:.5rem" alt="">
                                    <span class="text-muted" style="font-size:.82rem">Logo saat ini</span>
                                </div>
                            @endif
                            <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                            <div class="form-text">Kosongkan jika tidak ingin mengganti logo. Maks 2MB.</div>
                            @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 mt-2">
                            <p class="fw-semibold text-muted text-uppercase mb-2" style="font-size:.7rem;letter-spacing:.07em">Jam Kerja</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Jam Mulai Kerja</label>
                            <input type="time" name="work_start_time" class="form-control @error('work_start_time') is-invalid @enderror"
                                value="{{ old('work_start_time', $company->work_start_time ? substr($company->work_start_time, 0, 5) : '08:00') }}">
                            @error('work_start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Jam Selesai Kerja</label>
                            <input type="time" name="work_end_time" class="form-control @error('work_end_time') is-invalid @enderror"
                                value="{{ old('work_end_time', $company->work_end_time ? substr($company->work_end_time, 0, 5) : '17:00') }}">
                            @error('work_end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 d-flex gap-2 justify-content-end pt-2">
                            <a href="{{ route('companies.show', $company) }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
