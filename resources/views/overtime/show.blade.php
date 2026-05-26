@extends('layouts.app')
@section('title', 'Detail Pengajuan Lembur')
@section('page-title', 'Detail Pengajuan Lembur')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">

        {{-- Back --}}
        <div class="mb-3">
            <a href="{{ route('overtime.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title"><i class="bi bi-clock-history me-2 text-warning"></i>Pengajuan Lembur</span>
                <span class="badge badge-pill {{ $overtimeRequest->statusBadgeClass() }}">{{ $overtimeRequest->statusLabel() }}</span>
            </div>
            <div class="card-body px-4 py-4">
                <dl class="row mb-0" style="row-gap:.6rem">
                    <dt class="col-5 text-muted fw-normal" style="font-size:.84rem">Karyawan</dt>
                    <dd class="col-7 fw-semibold mb-0">{{ $overtimeRequest->employee->name }}</dd>

                    <dt class="col-5 text-muted fw-normal" style="font-size:.84rem">ID Karyawan</dt>
                    <dd class="col-7 font-monospace mb-0" style="font-size:.84rem">{{ $overtimeRequest->employee->employee_id }}</dd>

                    <dt class="col-5 text-muted fw-normal" style="font-size:.84rem">Perusahaan</dt>
                    <dd class="col-7 mb-0" style="font-size:.875rem">{{ $overtimeRequest->employee->company->name ?? '-' }}</dd>

                    <dt class="col-5 text-muted fw-normal" style="font-size:.84rem">Tanggal Lembur</dt>
                    <dd class="col-7 fw-semibold mb-0">{{ $overtimeRequest->date->isoFormat('dddd, D MMMM YYYY') }}</dd>

                    <dt class="col-5 text-muted fw-normal" style="font-size:.84rem">Waktu</dt>
                    <dd class="col-7 fw-semibold mb-0" style="color:#d97706">
                        {{ substr($overtimeRequest->start_time, 0, 5) }} &ndash; {{ substr($overtimeRequest->end_time, 0, 5) }}
                        <span class="text-muted fw-normal" style="font-size:.82rem">({{ $overtimeRequest->durationLabel() }})</span>
                    </dd>

                    <dt class="col-5 text-muted fw-normal" style="font-size:.84rem">Alasan</dt>
                    <dd class="col-7 mb-0" style="font-size:.875rem">{{ $overtimeRequest->reason }}</dd>

                    <dt class="col-5 text-muted fw-normal" style="font-size:.84rem">Diajukan</dt>
                    <dd class="col-7 mb-0" style="font-size:.82rem">{{ $overtimeRequest->created_at->isoFormat('D MMM YYYY, HH:mm') }}</dd>

                    @if($overtimeRequest->reviewed_at)
                    <dt class="col-5 text-muted fw-normal" style="font-size:.84rem">Diproses oleh</dt>
                    <dd class="col-7 mb-0" style="font-size:.875rem">
                        {{ $overtimeRequest->reviewer->name ?? '-' }}
                        <span class="text-muted" style="font-size:.78rem">&bull; {{ $overtimeRequest->reviewed_at->isoFormat('D MMM YYYY, HH:mm') }}</span>
                    </dd>
                    @endif

                    @if($overtimeRequest->admin_notes)
                    <dt class="col-5 text-muted fw-normal" style="font-size:.84rem">Catatan Admin</dt>
                    <dd class="col-7 mb-0" style="font-size:.875rem">{{ $overtimeRequest->admin_notes }}</dd>
                    @endif
                </dl>
            </div>

            @if($overtimeRequest->status === 'pending')
            <div class="card-footer bg-transparent px-4 py-3">
                <form method="POST" action="{{ route('overtime.approve', $overtimeRequest) }}" class="d-inline" id="form-approve">
                    @csrf @method('PATCH')
                    <input type="hidden" name="admin_notes" id="approve-notes">
                </form>
                <form method="POST" action="{{ route('overtime.reject', $overtimeRequest) }}" class="d-inline" id="form-reject">
                    @csrf @method('PATCH')
                    <input type="hidden" name="admin_notes" id="reject-notes">
                </form>

                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <button type="button" class="btn btn-success" onclick="doAction('approve')">
                        <i class="bi bi-check-lg me-1"></i>Setujui
                    </button>
                    <button type="button" class="btn btn-danger" onclick="doAction('reject')">
                        <i class="bi bi-x-lg me-1"></i>Tolak
                    </button>
                    <input type="text" id="action-notes" class="form-control" style="max-width:300px"
                        placeholder="Catatan (opsional)…">
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

@if($overtimeRequest->status === 'pending')
@push('scripts')
<script>
function doAction(action) {
    const notes = document.getElementById('action-notes').value;
    if (action === 'approve') {
        document.getElementById('approve-notes').value = notes;
        document.getElementById('form-approve').submit();
    } else {
        document.getElementById('reject-notes').value = notes;
        document.getElementById('form-reject').submit();
    }
}
</script>
@endpush
@endif
@endsection
