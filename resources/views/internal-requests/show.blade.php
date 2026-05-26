@extends('layouts.app')

@section('page-title', 'Detail Permohonan')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('internal-requests.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <div>
        <h4 class="mb-0 fw-bold">Detail Permohonan</h4>
        <p class="text-muted small mb-0">{{ $internalRequest->employee->name }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Informasi Permohonan</h6></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Karyawan</dt>
                    <dd class="col-sm-8 fw-semibold">{{ $internalRequest->employee->name }}</dd>

                    <dt class="col-sm-4 text-muted">Perusahaan</dt>
                    <dd class="col-sm-8">{{ $internalRequest->employee->company->name ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted">Tipe</dt>
                    <dd class="col-sm-8">
                        <span class="badge {{ $internalRequest->typeBadgeClass() }} badge-pill">{{ $internalRequest->typeLabel() }}</span>
                    </dd>

                    <dt class="col-sm-4 text-muted">Subjek</dt>
                    <dd class="col-sm-8 fw-semibold">{{ $internalRequest->subject }}</dd>

                    <dt class="col-sm-4 text-muted">Pesan</dt>
                    <dd class="col-sm-8" style="white-space:pre-wrap">{{ $internalRequest->message }}</dd>

                    <dt class="col-sm-4 text-muted">Status</dt>
                    <dd class="col-sm-8">
                        <span class="badge {{ $internalRequest->statusBadgeClass() }} badge-pill">{{ $internalRequest->statusLabel() }}</span>
                    </dd>

                    <dt class="col-sm-4 text-muted">Diajukan</dt>
                    <dd class="col-sm-8 small">{{ $internalRequest->created_at->format('d M Y H:i') }}</dd>

                    @if($internalRequest->responded_at)
                        <dt class="col-sm-4 text-muted">Direspons oleh</dt>
                        <dd class="col-sm-8">{{ $internalRequest->responder->name ?? '-' }}
                            <span class="text-muted small">({{ $internalRequest->responded_at->format('d M Y H:i') }})</span>
                        </dd>

                        <dt class="col-sm-4 text-muted">Balasan Admin</dt>
                        <dd class="col-sm-8" style="white-space:pre-wrap">{{ $internalRequest->admin_response }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    @if($internalRequest->status === 'pending' || $internalRequest->status === 'diproses')
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Balas Permohonan</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('internal-requests.respond', $internalRequest) }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Balasan <span class="text-danger">*</span></label>
                        <textarea name="admin_response" class="form-control @error('admin_response') is-invalid @enderror"
                            rows="5" required placeholder="Tulis balasan...">{{ old('admin_response', $internalRequest->admin_response) }}</textarea>
                        @error('admin_response')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Update Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="diproses" @selected(old('status', $internalRequest->status)=='diproses')>Diproses</option>
                            <option value="selesai"  @selected(old('status', $internalRequest->status)=='selesai')>Selesai</option>
                            <option value="ditolak"  @selected(old('status', $internalRequest->status)=='ditolak')>Ditolak</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-reply me-1"></i> Kirim Balasan
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
