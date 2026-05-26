@extends('layouts.app')

@section('page-title', 'Detail Permohonan Cuti')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Detail Permohonan Cuti</h4>
        <p class="text-muted small mb-0">Diajukan {{ $leaveRequest->created_at->isoFormat('dddd, D MMMM YYYY · HH:mm') }}</p>
    </div>
    <a href="{{ route('my.leaves') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title mb-0">
                    <i class="bi bi-calendar-check me-2 text-primary"></i>
                    {{ $leaveRequest->leaveType->name ?? 'Cuti' }}
                </span>
                <span class="badge badge-pill {{ $leaveRequest->statusBadgeClass() }}">{{ $leaveRequest->statusLabel() }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted small">Tanggal Mulai</div>
                        <div class="fw-semibold">{{ $leaveRequest->start_date->isoFormat('dddd, D MMM YYYY') }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Tanggal Selesai</div>
                        <div class="fw-semibold">{{ $leaveRequest->end_date->isoFormat('dddd, D MMM YYYY') }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Durasi</div>
                        <div class="fw-semibold">{{ $leaveRequest->days_count }} hari kerja</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Jenis Cuti</div>
                        <div class="fw-semibold">
                            @if($leaveRequest->leaveType?->color)
                                <span class="legend-dot d-inline-block me-1" style="width:10px;height:10px;border-radius:50%;background:{{ $leaveRequest->leaveType->color }}"></span>
                            @endif
                            {{ $leaveRequest->leaveType->name ?? '-' }}
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Alasan / Keterangan</div>
                        <div class="border rounded p-3 bg-light" style="white-space:pre-wrap">{{ $leaveRequest->reason ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($leaveRequest->status !== 'pending')
            <div class="card mt-4">
                <div class="card-header">
                    <span class="card-title mb-0">
                        <i class="bi bi-clipboard-check me-2 {{ $leaveRequest->status === 'approved' ? 'text-success' : 'text-danger' }}"></i>
                        Hasil Review
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="text-muted small">Direview Oleh</div>
                            <div class="fw-semibold">{{ $leaveRequest->reviewer?->name ?? '—' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Tanggal Review</div>
                            <div class="fw-semibold">
                                {{ $leaveRequest->reviewed_at ? $leaveRequest->reviewed_at->isoFormat('D MMM YYYY · HH:mm') : '—' }}
                            </div>
                        </div>
                        @if($leaveRequest->admin_notes)
                        <div class="col-12">
                            <div class="text-muted small">Catatan Admin</div>
                            <div class="border rounded p-3 bg-light" style="white-space:pre-wrap">{{ $leaveRequest->admin_notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <span class="card-title mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi</span>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">ID Permohonan</span>
                        <span class="font-monospace">#{{ str_pad($leaveRequest->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Diajukan</span>
                        <span>{{ $leaveRequest->created_at->isoFormat('D MMM YYYY') }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Status</span>
                        <span class="badge badge-pill {{ $leaveRequest->statusBadgeClass() }}">{{ $leaveRequest->statusLabel() }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Durasi</span>
                        <span class="fw-semibold">{{ $leaveRequest->days_count }} hari</span>
                    </li>
                </ul>
            </div>
            @if($leaveRequest->status === 'pending')
                <div class="card-footer bg-transparent">
                    <form method="POST" action="{{ route('my.leaves.destroy', $leaveRequest) }}"
                          onsubmit="return confirm('Batalkan permohonan cuti ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm w-100">
                            <i class="bi bi-x-circle me-1"></i> Batalkan Permohonan
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
