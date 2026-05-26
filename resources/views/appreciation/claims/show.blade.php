@extends('layouts.app')
@section('title', 'Detail Permohonan — '.$claim->title)
@section('page-title', 'Detail Permohonan Apresiasi')
@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible mb-4" role="alert">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Main claim info --}}
        <div class="card mb-4">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-file-earmark-text me-2"></i>{{ $claim->title }}</span>
                <span class="badge badge-pill {{ $claim->statusBadgeClass() }}">{{ $claim->statusLabel() }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Nominal</dt>
                    <dd class="col-sm-8 fw-bold text-success fs-5">Rp {{ number_format($claim->amount, 0, ',', '.') }}</dd>

                    <dt class="col-sm-4 text-muted">Pengaju</dt>
                    <dd class="col-sm-8">{{ $claim->submitter->name }}</dd>

                    <dt class="col-sm-4 text-muted">Tanggal Pengajuan</dt>
                    <dd class="col-sm-8">{{ $claim->created_at->format('d M Y H:i') }}</dd>

                    @if($claim->description)
                    <dt class="col-sm-4 text-muted">Deskripsi</dt>
                    <dd class="col-sm-8" style="white-space:pre-wrap">{{ $claim->description }}</dd>
                    @endif

                    @if($claim->reviewer)
                    <dt class="col-sm-4 text-muted">Ditinjau oleh</dt>
                    <dd class="col-sm-8">{{ $claim->reviewer->name }} <span class="text-muted" style="font-size:.8rem">({{ $claim->reviewed_at?->format('d M Y H:i') }})</span></dd>
                    @endif

                    @if($claim->isApproved() && $claim->payment_date)
                    <dt class="col-sm-4 text-muted">Tanggal Transfer</dt>
                    <dd class="col-sm-8">{{ $claim->payment_date->format('d M Y') }}</dd>
                    @endif

                    @if($claim->isApproved() && $claim->hasTransferProof())
                    <dt class="col-sm-4 text-muted">Bukti Transfer</dt>
                    <dd class="col-sm-8">
                        <a href="{{ route('appreciation.claims.transfer-proof', [$appreciation, $claim]) }}"
                           target="_blank" class="btn btn-sm btn-outline-success me-1">
                            <i class="bi bi-receipt me-1"></i>Lihat
                        </a>
                        <a href="{{ route('appreciation.claims.transfer-proof', [$appreciation, $claim]) }}?download=1"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download"></i>
                        </a>
                    </dd>
                    @endif

                    @if($claim->isRejected() && $claim->rejection_reason)
                    <dt class="col-sm-4 text-muted">Alasan Penolakan</dt>
                    <dd class="col-sm-8 text-danger">{{ $claim->rejection_reason }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Approve / Reject actions (only if pending) --}}
        @if($claim->isPending())
        <div class="card mb-4">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-shield-check me-2"></i>Tindakan Review</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <button type="button" class="btn btn-success w-100"
                            data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="bi bi-check-circle me-1"></i>Setujui
                        </button>
                    </div>
                    <div class="col-md-7">
                        <form method="POST" action="{{ route('appreciation.claims.reject', [$appreciation, $claim]) }}">
                            @csrf @method('PATCH')
                            <div class="mb-2">
                                <input type="text" name="rejection_reason" class="form-control form-control-sm @error('rejection_reason') is-invalid @enderror"
                                    placeholder="Alasan penolakan..." required>
                                @error('rejection_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-danger w-100"><i class="bi bi-x-circle me-1"></i>Tolak</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Documents --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-paperclip me-2"></i>Dokumen Bukti</span>
            </div>

            @if($claim->documents->isEmpty())
                <div class="card-body text-center py-4 text-muted">
                    <i class="bi bi-file-earmark fs-1 d-block mb-2 opacity-25"></i>Belum ada dokumen.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Nama File</th>
                                <th>Ukuran</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($claim->documents as $doc)
                            <tr>
                                <td class="fw-medium">{{ $doc->label }}</td>
                                <td class="text-muted" style="font-size:.82rem">{{ $doc->original_name }}</td>
                                <td class="text-muted" style="font-size:.82rem">{{ $doc->fileSizeFormatted() }}</td>
                                <td class="text-end">
                                    <a href="{{ route('appreciation.claims.documents.show', [$appreciation, $claim, $doc]) }}"
                                        target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Lihat
                                    </a>
                                    <a href="{{ route('appreciation.claims.documents.show', [$appreciation, $claim, $doc]) }}?download=1"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <form method="POST"
                                        action="{{ route('appreciation.claims.documents.destroy', [$appreciation, $claim, $doc]) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Hapus dokumen ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Add document form --}}
            <div class="card-body border-top">
                <p class="fw-medium mb-2" style="font-size:.85rem">Tambah Dokumen</p>
                <form method="POST" action="{{ route('appreciation.claims.documents.store', [$appreciation, $claim]) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="label" class="form-control form-control-sm @error('label') is-invalid @enderror"
                                placeholder="Label" value="{{ old('label') }}" required>
                            @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <input type="file" name="file" class="form-control form-control-sm @error('file') is-invalid @enderror"
                                accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" required>
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-upload"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Right sidebar --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="text-muted mb-2" style="font-size:.78rem;text-transform:uppercase">Karyawan</h6>
                <div class="fw-semibold">{{ $appreciation->employee->name }}</div>
                <div class="text-muted" style="font-size:.83rem">{{ $appreciation->employee->employee_id }}</div>
                <a href="{{ route('appreciation.show', $appreciation) }}" class="btn btn-sm btn-outline-secondary mt-2">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke Anggaran
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2" style="font-size:.78rem;text-transform:uppercase">Sisa Anggaran {{ $appreciation->year }}</h6>
                <div class="fw-bold text-success">Rp {{ number_format($appreciation->remainingAmount(), 0, ',', '.') }}</div>
                <div class="text-muted" style="font-size:.78rem">dari Rp {{ number_format($appreciation->total_amount, 0, ',', '.') }}</div>
            </div>
        </div>

        @if($claim->isPending())
        <div class="mt-3">
            <form method="POST" action="{{ route('appreciation.claims.destroy', [$appreciation, $claim]) }}"
                onsubmit="return confirm('Tarik dan hapus permohonan ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Batalkan Permohonan</button>
            </form>
        </div>
        @endif
    </div>
</div>

@if($claim->isPending())
{{-- ── Approve Modal ── --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="bi bi-check-circle-fill me-2 text-success"></i>Setujui Permohonan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('appreciation.claims.approve', [$appreciation, $claim]) }}"
                  enctype="multipart/form-data">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-success py-2 mb-3" style="font-size:.85rem">
                        <i class="bi bi-info-circle me-1"></i>
                        Anda akan menyetujui permohonan <strong>"{{ $claim->title }}"</strong>
                        sebesar <strong>Rp {{ number_format($claim->amount, 0, ',', '.') }}</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">
                            Bukti Transfer
                            <span class="text-muted fw-normal" style="font-size:.8rem">(opsional)</span>
                        </label>
                        <input type="file" name="transfer_proof"
                               class="form-control @error('transfer_proof') is-invalid @enderror"
                               accept=".pdf,.jpg,.jpeg,.png,.webp">
                        @error('transfer_proof')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">PDF atau gambar (jpg, png, webp), maks. 10 MB</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-medium">
                            Tanggal Transfer
                            <span class="text-muted fw-normal" style="font-size:.8rem">(opsional)</span>
                        </label>
                        <input type="date" name="payment_date"
                               class="form-control @error('payment_date') is-invalid @enderror"
                               value="{{ old('payment_date') }}">
                        @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($errors->any())
    new bootstrap.Modal(document.getElementById('approveModal')).show();
    @endif
});
</script>
@endpush
@endif

@endsection
