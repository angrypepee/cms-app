@extends('layouts.app')

@section('page-title', 'Detail Permohonan Cuti')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <div>
        <h4 class="mb-0 fw-bold">Detail Permohonan Cuti</h4>
        <p class="text-muted small mb-0">{{ $leaveRequest->employee->name }} &mdash; {{ $leaveRequest->leaveType->name }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Informasi Permohonan</h6></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Karyawan</dt>
                    <dd class="col-sm-8 fw-semibold">{{ $leaveRequest->employee->name }}</dd>

                    <dt class="col-sm-4 text-muted">Perusahaan</dt>
                    <dd class="col-sm-8">{{ $leaveRequest->employee->company->name ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted">Tipe Cuti</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-pill" style="background-color:{{ $leaveRequest->leaveType->color ?? '#2563eb' }}">
                            {{ $leaveRequest->leaveType->name }}
                        </span>
                    </dd>

                    <dt class="col-sm-4 text-muted">Tanggal Mulai</dt>
                    <dd class="col-sm-8">{{ $leaveRequest->start_date->translatedFormat('l, d F Y') }}</dd>

                    <dt class="col-sm-4 text-muted">Tanggal Akhir</dt>
                    <dd class="col-sm-8">{{ $leaveRequest->end_date->translatedFormat('l, d F Y') }}</dd>

                    <dt class="col-sm-4 text-muted">Jumlah Hari</dt>
                    <dd class="col-sm-8 fw-semibold">{{ $leaveRequest->days_count }} hari kerja</dd>

                    <dt class="col-sm-4 text-muted">Alasan</dt>
                    <dd class="col-sm-8">{{ $leaveRequest->reason }}</dd>

                    <dt class="col-sm-4 text-muted">Status</dt>
                    <dd class="col-sm-8">
                        <span class="badge {{ $leaveRequest->statusBadgeClass() }} badge-pill">{{ $leaveRequest->statusLabel() }}</span>
                    </dd>

                    <dt class="col-sm-4 text-muted">Diajukan</dt>
                    <dd class="col-sm-8 small">{{ $leaveRequest->created_at->format('d M Y H:i') }}</dd>

                    @if($leaveRequest->reviewed_at)
                        <dt class="col-sm-4 text-muted">Direview oleh</dt>
                        <dd class="col-sm-8">{{ $leaveRequest->reviewer->name ?? '-' }} <span class="text-muted small">({{ $leaveRequest->reviewed_at->format('d M Y H:i') }})</span></dd>
                    @endif

                    @if($leaveRequest->admin_notes)
                        <dt class="col-sm-4 text-muted">Catatan Admin</dt>
                        <dd class="col-sm-8 fst-italic">{{ $leaveRequest->admin_notes }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    @if($leaveRequest->status === 'pending')
    <div class="col-lg-5">
        <div class="card border-success">
            <div class="card-header bg-success bg-opacity-10">
                <h6 class="card-title mb-0 text-success">Setujui Permohonan</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leaves.approve', $leaveRequest) }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Catatan (opsional)</label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Catatan untuk karyawan..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Setujui permohonan cuti ini?')">
                        <i class="bi bi-check-lg me-1"></i> Setujui
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-danger mt-3">
            <div class="card-header bg-danger bg-opacity-10">
                <h6 class="card-title mb-0 text-danger">Tolak Permohonan</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leaves.reject', $leaveRequest) }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="admin_notes" class="form-control" rows="3" required placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Tolak permohonan cuti ini?')">
                        <i class="bi bi-x-lg me-1"></i> Tolak
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
