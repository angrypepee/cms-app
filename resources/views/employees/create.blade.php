@extends('layouts.app')
@section('title', 'Tambah Karyawan')
@section('page-title', 'Tambah Karyawan')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-person-plus me-2 text-primary"></i>Data Karyawan</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('employees.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Perusahaan <span class="text-danger">*</span></label>
                            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Perusahaan --</option>
                                @foreach($companies as $co)
                                    <option value="{{ $co->id }}" {{ old('company_id') == $co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                                @endforeach
                            </select>
                            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">ID Karyawan</label>
                            <input type="text" class="form-control font-monospace bg-light" value="Akan dibuat otomatis" readonly disabled>
                            <div class="form-text" style="font-size:.72rem"><i class="bi bi-info-circle me-1"></i>Sistem akan menggenerate ID berikutnya (cth. EMP-004) saat disimpan.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Budi Santoso" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Jabatan</label>
                            <select name="position" class="form-select @error('position') is-invalid @enderror">
                                <option value="">— Pilih Jabatan —</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->name }}" {{ old('position') === $pos->name ? 'selected' : '' }}>{{ $pos->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Belum ada? <a href="{{ route('master-data.index', ['tab'=>'positions']) }}" target="_blank">Kelola di Data Master</a></small>
                            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Departemen</label>
                            <select name="department" class="form-select @error('department') is-invalid @enderror">
                                <option value="">— Pilih Departemen —</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->name }}" {{ old('department') === $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Belum ada? <a href="{{ route('master-data.index', ['tab'=>'departments']) }}" target="_blank">Kelola di Data Master</a></small>
                            @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Kategori Karyawan <span class="text-danger">*</span></label>
                            <select name="employee_category" class="form-select @error('employee_category') is-invalid @enderror" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->value }}" {{ old('employee_category', 'tetap') === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                                @endforeach
                            </select>
                            @error('employee_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Gaji Pokok</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill text-muted"></i></span>
                                <input type="text" class="form-control bg-light text-muted" value="Diatur dari Kontrak Kerja" readonly disabled>
                            </div>
                            <div class="form-text" style="font-size:.72rem"><i class="bi bi-info-circle me-1"></i>Setelah karyawan dibuat, buat Kontrak Kerja untuk mengatur gaji.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="karyawan@perusahaan.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">No. Telepon</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="08123456789">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tanggal Bergabung</label>
                            <input type="date" name="join_date" class="form-control @error('join_date') is-invalid @enderror" value="{{ old('join_date') }}">
                            @error('join_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="isActive">Karyawan Aktif</label>
                            </div>
                        </div>
                        <div class="col-12 d-flex gap-2 justify-content-end pt-2">
                            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Karyawan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
