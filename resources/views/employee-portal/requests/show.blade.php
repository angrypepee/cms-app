@extends('layouts.app')

@section('page-title', 'Detail Permohonan')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('my.requests') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <h4 class="mb-0 fw-bold">Detail Permohonan</h4>
</div>

<div class="card" style="max-width:720px">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0">{{ $internalRequest->subject }}</h6>
            <span class="badge {{ $internalRequest->statusBadgeClass() }} badge-pill">{{ $internalRequest->statusLabel() }}</span>
        </div>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4 text-muted">Tipe</dt>
            <dd class="col-sm-8">
                <span class="badge {{ $internalRequest->typeBadgeClass() }} badge-pill">{{ $internalRequest->typeLabel() }}</span>
            </dd>

            <dt class="col-sm-4 text-muted">Diajukan</dt>
            <dd class="col-sm-8 small">{{ $internalRequest->created_at->format('d M Y H:i') }}</dd>

            <dt class="col-sm-4 text-muted">Pesan</dt>
            <dd class="col-sm-8" style="white-space:pre-wrap">{{ $internalRequest->message }}</dd>

            @if($internalRequest->admin_response)
                <dt class="col-sm-4 text-muted">Balasan Admin</dt>
                <dd class="col-sm-8">
                    <div class="alert alert-info py-2 small mb-0" style="white-space:pre-wrap">{{ $internalRequest->admin_response }}</div>
                    @if($internalRequest->responded_at)
                        <div class="text-muted small mt-1">{{ $internalRequest->responded_at->format('d M Y H:i') }}</div>
                    @endif
                </dd>
            @else
                <dt class="col-sm-4 text-muted">Balasan Admin</dt>
                <dd class="col-sm-8 text-muted fst-italic">Belum ada balasan</dd>
            @endif
        </dl>
    </div>
</div>
@endsection
