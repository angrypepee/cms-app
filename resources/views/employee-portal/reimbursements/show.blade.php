@extends('layouts.app')

@section('title', 'Detail Reimbursement')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('my.reimbursements') }}" class="btn btn-sm btn-outline-secondary">
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

    <div class="row g-4">
        {{-- Info Card --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <span class="card-title"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi</span>
                </div>
                <div class="card-body px-4 py-3">
                    <dl class="row mb-0 gy-2" style="font-size:.88rem">
                        <dt class="col-5 text-muted fw-normal">Status</dt>
                        <dd class="col-7 mb-0">
                            <span class="badge badge-pill {{ $reimbursement->statusBadgeClass() }}">
                                {{ $reimbursement->statusLabel() }}
                            </span>
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Judul</dt>
                        <dd class="col-7 mb-0 fw-semibold">{{ $reimbursement->title }}</dd>

                        <dt class="col-5 text-muted fw-normal">Kategori</dt>
                        <dd class="col-7 mb-0">{{ $reimbursement->categoryLabel() }}</dd>

                        <dt class="col-5 text-muted fw-normal">Jumlah</dt>
                        <dd class="col-7 mb-0 fw-bold text-success">
                            Rp {{ number_format($reimbursement->amount, 0, ',', '.') }}
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Approver</dt>
                        <dd class="col-7 mb-0">{{ $reimbursement->approver?->name ?? '-' }}</dd>

                        @if($reimbursement->description)
                        <dt class="col-5 text-muted fw-normal">Deskripsi</dt>
                        <dd class="col-7 mb-0">{{ $reimbursement->description }}</dd>
                        @endif

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
                            <a href="{{ route('my.reimbursements.transfer-proof', $reimbursement) }}"
                               target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-eye me-1"></i>Lihat
                            </a>
                            <a href="{{ route('my.reimbursements.transfer-proof', $reimbursement) }}?download=1"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download me-1"></i>Download
                            </a>
                        </dd>
                        @endif
                    </dl>
                </div>
                @if($reimbursement->isPending())
                <div class="card-footer">
                    <form method="POST" action="{{ route('my.reimbursements.destroy', $reimbursement) }}"
                          onsubmit="return confirm('Batalkan permohonan ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Batalkan Permohonan
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        {{-- Documents --}}
        <div class="col-lg-7">
            <div class="card h-100">
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
                            <thead><tr><th>Nama</th><th>Ukuran</th><th class="text-end">Aksi</th></tr></thead>
                            <tbody>
                                @foreach($reimbursement->documents as $doc)
                                <tr>
                                    <td>
                                        <span class="fw-medium" style="font-size:.88rem">{{ $doc->label }}</span>
                                        <div class="text-muted" style="font-size:.75rem">{{ $doc->original_name }}</div>
                                    </td>
                                    <td class="text-muted" style="font-size:.82rem">{{ $doc->fileSizeFormatted() }}</td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            @if($doc->isViewable())
                                                <a href="{{ route('my.reimbursements.documents.show', [$reimbursement, $doc]) }}"
                                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye me-1"></i>Lihat
                                                </a>
                                            @endif
                                            <a href="{{ route('my.reimbursements.documents.show', [$reimbursement, $doc]) }}?download=1"
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
@endsection
