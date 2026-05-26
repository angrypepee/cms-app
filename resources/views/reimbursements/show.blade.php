@extends('layouts.app')

@section('title', 'Detail Reimbursement')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('reimbursements.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <h4 class="mb-0 fw-bold"><i class="bi bi-receipt me-2 text-primary"></i>Detail Reimbursement</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            @foreach($errors->all() as $err) {{ $err }}<br> @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Info + Actions --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="card-title"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi</span>
                    <span class="badge badge-pill {{ $reimbursement->statusBadgeClass() }} fs-6">
                        {{ $reimbursement->statusLabel() }}
                    </span>
                </div>
                <div class="card-body px-4 py-3">
                    <dl class="row mb-0 gy-2" style="font-size:.88rem">
                        <dt class="col-5 text-muted fw-normal">Karyawan</dt>
                        <dd class="col-7 mb-0">
                            <a href="{{ route('employees.show', $reimbursement->employee) }}" class="fw-semibold text-decoration-none">
                                {{ $reimbursement->employee?->name }}
                            </a>
                            <div class="text-muted" style="font-size:.75rem">{{ $reimbursement->employee?->employee_id }}</div>
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Judul</dt>
                        <dd class="col-7 mb-0 fw-semibold">{{ $reimbursement->title }}</dd>

                        <dt class="col-5 text-muted fw-normal">Kategori</dt>
                        <dd class="col-7 mb-0">{{ $reimbursement->categoryLabel() }}</dd>

                        <dt class="col-5 text-muted fw-normal">Jumlah</dt>
                        <dd class="col-7 mb-0 fw-bold text-success fs-6">
                            Rp {{ number_format($reimbursement->amount, 0, ',', '.') }}
                        </dd>

                        @if($reimbursement->description)
                        <dt class="col-5 text-muted fw-normal">Deskripsi</dt>
                        <dd class="col-7 mb-0">{{ $reimbursement->description }}</dd>
                        @endif

                        <dt class="col-5 text-muted fw-normal">Diajukan Oleh</dt>
                        <dd class="col-7 mb-0">{{ $reimbursement->submitter?->name }}</dd>

                        <dt class="col-5 text-muted fw-normal">Approver</dt>
                        <dd class="col-7 mb-0">{{ $reimbursement->approver?->name }}</dd>

                        <dt class="col-5 text-muted fw-normal">Tanggal Pengajuan</dt>
                        <dd class="col-7 mb-0">{{ $reimbursement->created_at->format('d M Y H:i') }}</dd>

                        @if($reimbursement->isApproved() || $reimbursement->isRejected())
                        <dt class="col-5 text-muted fw-normal">Diproses Oleh</dt>
                        <dd class="col-7 mb-0">{{ $reimbursement->reviewer?->name ?? '-' }}</dd>

                        <dt class="col-5 text-muted fw-normal">Tanggal Proses</dt>
                        <dd class="col-7 mb-0">{{ $reimbursement->reviewed_at?->format('d M Y H:i') ?? '-' }}</dd>
                        @endif

                        @if($reimbursement->isApproved() && $reimbursement->payment_date)
                        <dt class="col-5 text-muted fw-normal">Tanggal Bayar</dt>
                        <dd class="col-7 mb-0">{{ $reimbursement->payment_date->format('d M Y') }}</dd>
                        @endif

                        @if($reimbursement->isRejected() && $reimbursement->rejection_reason)
                        <dt class="col-5 text-muted fw-normal">Alasan Penolakan</dt>
                        <dd class="col-7 mb-0 text-danger">{{ $reimbursement->rejection_reason }}</dd>
                        @endif

                        @if($reimbursement->hasTransferProof())
                        <dt class="col-5 text-muted fw-normal">Bukti Transfer</dt>
                        <dd class="col-7 mb-0">
                            <a href="{{ route('reimbursements.transfer-proof', $reimbursement) }}"
                               target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-eye me-1"></i>Lihat
                            </a>
                            <a href="{{ route('reimbursements.transfer-proof', $reimbursement) }}?download=1"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download me-1"></i>Download
                            </a>
                        </dd>
                        @endif
                    </dl>
                </div>

                {{-- Actions --}}
                @if($reimbursement->isPending())
                <div class="card-footer d-flex gap-2">
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="bi bi-check-circle me-1"></i>Setujui
                    </button>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle me-1"></i>Tolak
                    </button>
                </div>
                @endif
            </div>

            @if(auth()->user()->isAdmin())
            <div class="mt-3">
                <form method="POST" action="{{ route('reimbursements.destroy', $reimbursement) }}"
                      onsubmit="return confirm('Hapus reimbursement ini secara permanen?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash3 me-1"></i>Hapus
                    </button>
                </form>
            </div>
            @endif
        </div>

        {{-- Documents --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="bi bi-paperclip me-2 text-primary"></i>Dokumen Pendukung</span>
                </div>
                @if($reimbursement->documents->isEmpty())
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-folder2-open fs-1 d-block mb-2 opacity-25"></i>
                        Tidak ada dokumen pendukung.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Nama</th><th>Ukuran</th><th>Diunggah</th><th class="text-end">Aksi</th></tr></thead>
                            <tbody>
                                @foreach($reimbursement->documents as $doc)
                                <tr>
                                    <td>
                                        <span class="fw-medium" style="font-size:.88rem">{{ $doc->label }}</span>
                                        <div class="text-muted" style="font-size:.75rem">{{ $doc->original_name }}</div>
                                    </td>
                                    <td class="text-muted" style="font-size:.82rem">{{ $doc->fileSizeFormatted() }}</td>
                                    <td class="text-muted" style="font-size:.8rem">
                                        {{ $doc->created_at->format('d M Y') }}<br>
                                        <span style="font-size:.72rem">{{ $doc->uploader?->name ?? '-' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            @if($doc->isViewable())
                                                <a href="{{ route('reimbursements.documents.show', [$reimbursement, $doc]) }}"
                                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye me-1"></i>Lihat
                                                </a>
                                            @endif
                                            <a href="{{ route('reimbursements.documents.show', [$reimbursement, $doc]) }}?download=1"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="bi bi-check-circle me-2 text-success"></i>Setujui Reimbursement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('reimbursements.approve', $reimbursement) }}"
                  enctype="multipart/form-data">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p class="text-muted mb-3" style="font-size:.88rem">
                        Setujui reimbursement <strong>{{ $reimbursement->title }}</strong>
                        — Rp {{ number_format($reimbursement->amount, 0, ',', '.') }}
                        atas nama <strong>{{ $reimbursement->employee?->name }}</strong>?
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-medium" style="font-size:.88rem">Bukti Transfer <span class="text-muted">(opsional)</span></label>
                        <input type="file" name="transfer_proof"
                               class="form-control form-control-sm @error('transfer_proof') is-invalid @enderror"
                               accept=".pdf,.jpg,.jpeg,.png,.webp">
                        @error('transfer_proof')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label fw-medium" style="font-size:.88rem">Tanggal Pembayaran <span class="text-muted">(opsional)</span></label>
                        <input type="date" name="payment_date"
                               class="form-control form-control-sm @error('payment_date') is-invalid @enderror"
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

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="bi bi-x-circle me-2 text-danger"></i>Tolak Reimbursement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('reimbursements.reject', $reimbursement) }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p class="text-muted mb-3" style="font-size:.88rem">
                        Tolak reimbursement <strong>{{ $reimbursement->title }}</strong>
                        dari <strong>{{ $reimbursement->employee?->name }}</strong>?
                    </p>
                    <div>
                        <label class="form-label fw-medium">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control @error('rejection_reason') is-invalid @enderror"
                                  rows="3" required placeholder="Jelaskan alasan penolakan...">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
@if($errors->has('transfer_proof') || $errors->has('payment_date'))
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('approveModal')).show();
    });
@elseif($errors->has('rejection_reason'))
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    });
@endif
</script>
@endpush
