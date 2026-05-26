@extends('layouts.app')
@section('title', 'Detail Permohonan — '.$claim->title)
@section('page-title', 'Detail Permohonan Apresiasi')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        {{-- Main claim info --}}
        <div class="card mb-4">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-file-earmark-text me-2 text-primary"></i>{{ $claim->title }}</span>
                <span class="badge badge-pill {{ $claim->statusBadgeClass() }}">{{ $claim->statusLabel() }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Nominal</dt>
                    <dd class="col-sm-8 fw-bold text-success fs-5">Rp {{ number_format($claim->amount, 0, ',', '.') }}</dd>

                    <dt class="col-sm-4 text-muted">Tanggal Pengajuan</dt>
                    <dd class="col-sm-8">{{ $claim->created_at->format('d M Y H:i') }}</dd>

                    @if($claim->description)
                    <dt class="col-sm-4 text-muted">Deskripsi</dt>
                    <dd class="col-sm-8" style="white-space:pre-wrap">{{ $claim->description }}</dd>
                    @endif

                    @if($claim->reviewer)
                    <dt class="col-sm-4 text-muted">Ditinjau oleh</dt>
                    <dd class="col-sm-8">
                        {{ $claim->reviewer->name }}
                        <span class="text-muted" style="font-size:.8rem">({{ $claim->reviewed_at?->format('d M Y H:i') }})</span>
                    </dd>
                    @endif

                    @if($claim->isApproved() && $claim->payment_date)
                    <dt class="col-sm-4 text-muted">Tanggal Transfer</dt>
                    <dd class="col-sm-8">{{ $claim->payment_date->format('d M Y') }}</dd>
                    @endif

                    @if($claim->isApproved() && $claim->hasTransferProof())
                    <dt class="col-sm-4 text-muted">Bukti Transfer</dt>
                    <dd class="col-sm-8">
                        <a href="{{ route('my.appreciation.claims.transfer-proof', [$budget, $claim]) }}"
                           target="_blank" class="btn btn-sm btn-outline-success me-1">
                            <i class="bi bi-receipt me-1"></i>Lihat
                        </a>
                        <a href="{{ route('my.appreciation.claims.transfer-proof', [$budget, $claim]) }}?download=1"
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
            @if($claim->isPending())
            <div class="card-footer bg-transparent">
                <form method="POST" action="{{ route('my.appreciation.claims.destroy', [$budget, $claim]) }}"
                      onsubmit="return confirm('Batalkan permohonan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Batalkan Permohonan
                    </button>
                </form>
            </div>
            @endif
        </div>

        {{-- Documents --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-paperclip me-2"></i>Dokumen Pendukung</span>
            </div>
            @if($claim->documents->isEmpty())
                <div class="card-body text-center py-4 text-muted">
                    <i class="bi bi-file-earmark fs-1 d-block mb-2 opacity-25"></i>Belum ada dokumen.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr><th>Label</th><th>Nama File</th><th>Ukuran</th><th class="text-end">Aksi</th></tr>
                        </thead>
                        <tbody>
                            @foreach($claim->documents as $doc)
                            <tr>
                                <td class="fw-medium">{{ $doc->label }}</td>
                                <td class="text-muted" style="font-size:.82rem">{{ $doc->original_name }}</td>
                                <td class="text-muted" style="font-size:.82rem">{{ $doc->fileSizeFormatted() }}</td>
                                <td class="text-end">
                                    <a href="{{ route('my.appreciation.claims.documents.show', [$budget, $claim, $doc]) }}"
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Lihat
                                    </a>
                                    <a href="{{ route('my.appreciation.claims.documents.show', [$budget, $claim, $doc]) }}?download=1"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Right sidebar --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="text-muted mb-3" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em">Informasi Anggaran</h6>
                <div class="mb-2">
                    <div class="text-muted" style="font-size:.78rem">Tahun</div>
                    <div class="fw-semibold">{{ $budget->year }}</div>
                </div>
                <div class="mb-2">
                    <div class="text-muted" style="font-size:.78rem">Total Anggaran</div>
                    <div class="fw-semibold text-primary">Rp {{ number_format($budget->total_amount, 0, ',', '.') }}</div>
                </div>
                <div class="mb-2">
                    <div class="text-muted" style="font-size:.78rem">Sisa Anggaran</div>
                    <div class="fw-semibold text-success">Rp {{ number_format($budget->remainingAmount(), 0, ',', '.') }}</div>
                </div>
                @if($budget->notes)
                <div>
                    <div class="text-muted" style="font-size:.78rem">Catatan</div>
                    <div style="font-size:.85rem">{{ $budget->notes }}</div>
                </div>
                @endif
            </div>
        </div>
        <a href="{{ route('my.appreciation') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>
@endsection
