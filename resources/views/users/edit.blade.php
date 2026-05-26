@extends('layouts.app')
@section('title', 'Edit Role — '.$user->name)
@section('page-title', 'Edit Role Pengguna')
@section('breadcrumb', 'Pengguna / Edit Role')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-shield-lock me-2 text-primary"></i>{{ $user->name }}</span>
            </div>
            <div class="card-body">

                {{-- User info summary --}}
                <div class="d-flex align-items-center gap-3 mb-4 p-3" style="background:#f8fafc;border-radius:.65rem;border:1px solid #e2e8f0">
                    <div style="width:44px;height:44px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <span style="color:#fff;font-weight:700;font-size:.9rem">{{ strtoupper(substr($user->name,0,1)) }}</span>
                    </div>
                    <div>
                        <div class="fw-semibold" style="font-size:.9rem">{{ $user->name }}</div>
                        <div class="text-muted" style="font-size:.78rem">{{ $user->email }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-medium">Role / Hak Akses</label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror">
                            @foreach($roles as $role)
                                <option value="{{ $role->value }}" {{ old('role', $user->role?->value) === $role->value ? 'selected' : '' }}>
                                    {{ $role->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text mt-2">
                            <span class="d-block mb-1"><strong>Administrator</strong> — Akses penuh + kelola pengguna + tanda tangan</span>
                            <span class="d-block mb-1"><strong>HRD / Staff</strong> — Buat & kelola slip gaji</span>
                            <span class="d-block"><strong>Signature Admin</strong> — Hanya dapat menandatangani slip yang sudah published</span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Jabatan / Title <span class="text-muted fw-normal">(ditampilkan di slip)</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $user->title) }}" placeholder="cth: Manager HRD, Direktur Keuangan">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
