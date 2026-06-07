@extends('layouts.app')
@section('title', 'Dokumen Kontrak - ' . $contractDocument->contract_number)
@section('page-title', 'Dokumen Kontrak')

@push('styles')
<style>
.contract-doc {
    max-width: 860px; margin: 0 auto;
    background: #fff; border-radius: .85rem;
    box-shadow: 0 6px 32px rgba(0,0,0,.12);
    overflow: hidden; border: 1px solid #e2e8f0;
}
.contract-header {
    background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 60%, #2563eb 100%);
    padding: 1.75rem 2rem; color: #fff; position: relative; overflow: hidden;
}
.contract-header::before {
    content:''; position:absolute; right:-50px; top:-50px;
    width:180px; height:180px; border-radius:50%;
    background:rgba(255,255,255,.07);
}
.contract-header::after {
    content:''; position:absolute; right:60px; bottom:-60px;
    width:120px; height:120px; border-radius:50%;
    background:rgba(255,255,255,.05);
}
.contract-header .company-logo {
    width:64px; height:64px; border-radius:.65rem;
    object-fit:contain; background:#fff; padding:6px;
    box-shadow:0 2px 8px rgba(0,0,0,.2); flex-shrink:0;
}
.contract-header .logo-placeholder {
    width:64px; height:64px; border-radius:.65rem; flex-shrink:0;
    background:rgba(255,255,255,.18); display:flex; align-items:center;
    justify-content:center; font-size:1.7rem; font-weight:700;
}
.contract-header .company-name  { font-size:1.15rem; font-weight:700; line-height:1.25; }
.contract-header .company-meta  { font-size:.73rem; opacity:.72; margin-top:.25rem; line-height:1.6; }
.contract-header .doc-label     { font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.14em; opacity:.65; }
.contract-header .doc-title     { font-size:1.35rem; font-weight:800; letter-spacing:.01em; line-height:1.15; margin-top:.1rem; }
.contract-header .doc-num       { font-size:.7rem; font-family:monospace; opacity:.55; margin-top:.2rem; }
.contract-header .doc-date      { font-size:.78rem; opacity:.75; margin-top:.1rem; }
.contract-badge-signed   { display:inline-flex; align-items:center; gap:.3rem; padding:.28em .75em; border-radius:50rem; font-size:.68rem; font-weight:700; letter-spacing:.05em; margin-top:.4rem; background:rgba(134,239,172,.22); color:#bbf7d0; border:1px solid rgba(134,239,172,.38); }
.contract-badge-unsigned { display:inline-flex; align-items:center; gap:.3rem; padding:.28em .75em; border-radius:50rem; font-size:.68rem; font-weight:700; letter-spacing:.05em; margin-top:.4rem; background:rgba(253,224,71,.2); color:#fde68a; border:1px solid rgba(253,224,71,.35); }
.contract-body { padding: 1.75rem 2rem; }
.contract-section-label {
    font-size:.62rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.1em; color:#94a3b8; margin-bottom:.75rem;
    display:flex; align-items:center; gap:.4rem;
}
.contract-section-label::after {
    content:''; flex:1; height:1px; background:#e2e8f0;
}
</style>
@endpush

@section('content')
@php
    $renderContractHtml = function ($value) {
        if (blank($value)) {
            return '-';
        }

        return strip_tags($value) !== $value
            ? $value
            : nl2br(e($value));
    };
@endphp

{{-- ── Action bar ─────────────────────────────────────── --}}
@php [$statusLabel, $statusColor] = $contractDocument->statusBadge(); @endphp

{{-- Status banner for cancelled/rejected --}}
@if($contractDocument->isCancelled() || $contractDocument->isRejected())
<div class="alert alert-{{ $contractDocument->isCancelled() ? 'danger' : 'warning' }} d-flex align-items-start gap-3 mb-4" role="alert">
    <i class="bi bi-{{ $contractDocument->isCancelled() ? 'x-circle-fill' : 'exclamation-triangle-fill' }} fs-5 flex-shrink-0 mt-1"></i>
    <div>
        <div class="fw-semibold">Kontrak {{ $contractDocument->isCancelled() ? 'Dibatalkan' : 'Ditolak' }}</div>
        @if($contractDocument->rejection_reason)
            <div style="font-size:.88rem">{{ $contractDocument->rejection_reason }}</div>
        @endif
        @if($contractDocument->rejected_at)
            <div class="text-muted" style="font-size:.8rem">
                {{ $contractDocument->rejected_at->isoFormat('D MMMM YYYY, HH:mm') }} WIB
                @if($contractDocument->rejectedBy) — oleh {{ $contractDocument->rejectedBy->name }} @endif
            </div>
        @endif
    </div>
</div>
@endif

<div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
    {{-- Status badge --}}
    <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }}" style="font-size:.78rem;padding:.35em .75em">{{ $statusLabel }}</span>

    @if($contractDocument->isActive())
        @if(auth()->user()->canSign() && !$contractDocument->isSigned())
            <form method="POST" action="{{ route('contract-documents.sign', $contractDocument) }}" onsubmit="return confirm('Tandatangani kontrak ini atas nama Anda?')">
                @csrf @method('PATCH')
                <button class="btn btn-success btn-sm"><i class="bi bi-pen me-1"></i>Tanda Tangani Digital</button>
            </form>
        @elseif($contractDocument->isSigned() && auth()->user()->canSign())
            <form method="POST" action="{{ route('contract-documents.unsign', $contractDocument) }}" onsubmit="return confirm('Batalkan tanda tangan kontrak ini?')">
                @csrf @method('PATCH')
                <button class="btn btn-outline-warning btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>Batal Tanda Tangan</button>
            </form>
        @endif
        @unless($contractDocument->isSigned())
            <a href="{{ route('contract-documents.edit', $contractDocument) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endunless
    @endif

    @if($contractDocument->file_path)
        <a href="{{ route('contract-documents.download', $contractDocument) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i>Unduh File</a>
    @endif

    {{-- Cancel (admin only, any status except already cancelled) --}}
    @if(!$contractDocument->isCancelled() && auth()->user()->isAdmin())
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelContractModal">
            <i class="bi bi-x-circle me-1"></i>Batalkan Kontrak
        </button>
    @endif

    {{-- Hard delete (admin, only non-signed & non-cancelled) --}}
    @if(auth()->user()->isAdmin())
        @if($contractDocument->isCancelled() || $contractDocument->isSigned() || $contractDocument->isSignedByEmployee())
            <button class="btn btn-outline-danger btn-sm" disabled title="Hanya bisa menghapus kontrak yang belum ditandatangani">
                <i class="bi bi-trash3 me-1"></i>Hapus
            </button>
        @else
            <form method="POST" action="{{ route('contract-documents.destroy', $contractDocument) }}" class="d-inline"
                onsubmit="return confirm('⚠️ Hapus permanen dokumen kontrak ini?\n\nTindakan ini TIDAK DAPAT DIBATALKAN dan akan menghapus:\n• Data kontrak\n• File yang terlampir\n\nLanjutkan?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3 me-1"></i>Hapus</button>
            </form>
        @endif
    @endif

    <a href="{{ route('contract-documents.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

{{-- Modal: Batalkan Kontrak --}}
<div class="modal fade" id="cancelContractModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('contract-documents.cancel', $contractDocument) }}">
            @csrf @method('PATCH')
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger"><i class="bi bi-x-circle me-2"></i>Batalkan Kontrak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 mb-3" style="font-size:.85rem">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Pembatalan akan mengubah status kontrak menjadi <strong>Dibatalkan</strong> dan karyawan tidak dapat lagi menandatanganinya.
                    </div>
                    <div>
                        <label class="form-label fw-medium">Alasan Pembatalan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea name="reason" class="form-control" rows="3"
                            placeholder="Jelaskan alasan pembatalan kontrak ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i>Ya, Batalkan Kontrak</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Contract Document ───────────────────────────────── --}}
<div class="contract-doc mb-4">

    {{-- Header --}}
    <div class="contract-header">
        @php
            $logoPath = $appLogo ? storage_path('app/public/' . $appLogo) : null;
            $hasLogo  = $logoPath && file_exists($logoPath);
            $firstLetter = strtoupper(substr($appName, 0, 1));

            $firstPartyMeta = array_filter([
                $contractDocument->first_party_address ?? null,
            ]);
        @endphp
        <div class="d-flex align-items-center justify-content-between gap-3 position-relative" style="z-index:1">
            {{-- Left: logo + company --}}
            <div class="d-flex align-items-center gap-3">
                @if($hasLogo)
                    <img src="{{ asset('storage/' . $appLogo) }}" class="company-logo" alt="{{ $appName }}">
                @else
                    <div class="logo-placeholder">{{ $firstLetter }}</div>
                @endif
                <div>
                    <div class="company-name">{{ $contractDocument->first_party_company ?? $appName }}</div>
                    @if($contractDocument->first_party_name)
                        <div class="company-meta">{{ $contractDocument->first_party_name }}{{ $contractDocument->first_party_position ? ' · ' . $contractDocument->first_party_position : '' }}</div>
                    @endif
                    @if($contractDocument->first_party_address)
                        <div class="company-meta">{{ $contractDocument->first_party_address }}</div>
                    @endif
                </div>
            </div>
            {{-- Right: doc type + number + date + status --}}
            <div class="text-end flex-shrink-0">
                <div class="doc-label">Surat Perjanjian Kerja</div>
                <div class="doc-title">{{ $contractDocument->contract_number }}</div>
                <div class="doc-date">
                    {{ $contractDocument->contract_date?->isoFormat('D MMMM YYYY') ?? '-' }}
                    @if($contractDocument->location)
                        · {{ $contractDocument->location }}
                    @endif
                </div>
                @if($contractDocument->isSigned())
                    <span class="contract-badge-signed"><i class="bi bi-patch-check-fill"></i> Ditandatangani</span>
                @else
                    <span class="contract-badge-unsigned"><i class="bi bi-hourglass-split"></i> Belum Ditandatangani</span>
                @endif
            </div>
        </div>
    </div>

    <div class="contract-body">

        {{-- Para pihak --}}
        <div class="contract-section-label">Para Pihak</div>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100" style="font-size:.85rem">
                    <div class="fw-semibold mb-1" style="color:#1d4ed8">PIHAK PERTAMA</div>
                    <div class="fw-semibold">{{ $contractDocument->first_party_name ?? '-' }}</div>
                    <div class="text-muted">{{ $contractDocument->first_party_position ?? '' }}</div>
                    <div>{{ $contractDocument->first_party_company ?? '-' }}</div>
                    @if($contractDocument->first_party_address)
                        <div class="text-muted mt-1">{{ $contractDocument->first_party_address }}</div>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100" style="font-size:.85rem">
                    <div class="fw-semibold mb-1" style="color:#1d4ed8">PIHAK KEDUA</div>
                    <div class="fw-semibold">{{ $contractDocument->second_party_name ?? '-' }}</div>
                    @if($contractDocument->second_party_ktp)
                        <div class="text-muted">KTP: {{ $contractDocument->second_party_ktp }}</div>
                    @endif
                    @if($contractDocument->second_party_address)
                        <div class="text-muted mt-1">{{ $contractDocument->second_party_address }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Info kontrak --}}
        <div class="contract-section-label">Informasi Kontrak</div>
        <div class="row g-3 mb-4" style="font-size:.85rem">
            <div class="col-md-4">
                <div class="text-muted">Nama Proyek / Pekerjaan</div>
                <div class="fw-semibold">{{ $contractDocument->project_name ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted">Durasi</div>
                <div class="fw-semibold">{{ $contractDocument->duration_text ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted">Periode</div>
                <div class="fw-semibold">
                    {{ $contractDocument->start_date?->isoFormat('D MMM YYYY') ?? '-' }}
                    <span class="text-muted mx-1">s/d</span>
                    {{ $contractDocument->end_date?->isoFormat('D MMM YYYY') ?? 'Permanen' }}
                </div>
            </div>
            @if(auth()->user()->isAdmin())
            <div class="col-md-4">
                <div class="text-muted">Nilai Kontrak</div>
                <div class="fw-semibold text-success">
                    {{ $contractDocument->contract_value ? 'Rp ' . number_format($contractDocument->contract_value, 0, ',', '.') : '-' }}
                </div>
                @if($contractDocument->contract_value_text)
                    <div class="text-muted" style="font-size:.78rem">{{ $contractDocument->contract_value_text }}</div>
                @endif
            </div>
            <div class="col-md-4">
                <div class="text-muted">Metode Pembayaran</div>
                <div class="fw-semibold">{{ $contractDocument->paymentLabel() }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted">Rekening Pembayaran</div>
                <div class="fw-semibold">{{ $contractDocument->bank_name ?? '-' }}</div>
                <div class="text-muted" style="font-size:.78rem">
                    {{ $contractDocument->bank_account ?? '' }}
                    @if($contractDocument->bank_account_name)
                        · {{ $contractDocument->bank_account_name }}
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Isi pasal --}}
        @foreach([
            ['Pasal 1', 'Ruang Lingkup Pekerjaan', $contractDocument->scope_of_work],
            ['Pasal 2', 'Hak &amp; Kewajiban Para Pihak', $contractDocument->rights_obligations],
            ['Pasal 3', 'Hak Kekayaan Intelektual (HKI)', $contractDocument->hki_terms],
            ['Pasal 4', 'Kerahasiaan / NDA', $contractDocument->nda_terms],
            ['Pasal 5', 'Berakhirnya Perintah Kerja &amp; Sanksi', $contractDocument->sanctions_terms],
            ['Pasal 6', 'Penyelesaian Perselisihan', $contractDocument->dispute_terms],
        ] as [$pasal, $judul, $isi])
            <div class="contract-section-label">
                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.65rem;letter-spacing:.03em">{{ $pasal }}</span>
                {!! $judul !!}
            </div>
            <div class="mb-4" style="font-size:.88rem;line-height:1.75">{!! $renderContractHtml($isi) !!}</div>
        @endforeach

        {{-- Lampiran pembayaran --}}
        @if($contractDocument->payment_terms)
            <div class="contract-section-label">
                <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:.65rem;letter-spacing:.03em">Lampiran</span>
                Rincian / Termin Pembayaran
            </div>
            <div class="mb-4" style="font-size:.88rem;line-height:1.75">{!! $renderContractHtml($contractDocument->payment_terms) !!}</div>
        @endif

        {{-- Catatan --}}
        @if($contractDocument->notes)
            <div class="contract-section-label">Catatan / Ketentuan Tambahan</div>
            <div class="mb-4" style="font-size:.88rem;line-height:1.75">{!! $renderContractHtml($contractDocument->notes) !!}</div>
        @endif

        {{-- Blok Tanda Tangan Fisik --}}
        <div class="contract-section-label">Tanda Tangan Para Pihak</div>
        <div class="row g-0 mb-4">
            <div class="col-6 text-center" style="padding:1.5rem 1rem;border:1px solid #e2e8f0;border-radius:.5rem 0 0 .5rem">
                <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Pihak Pertama</div>
                <div class="fw-semibold" style="font-size:.9rem">{{ $contractDocument->first_party_company ?? $appName }}</div>
                <div class="text-muted" style="font-size:.8rem;margin-bottom:3rem">{{ $contractDocument->contract_date?->isoFormat('D MMMM YYYY') }}</div>
                {{-- Signature space --}}
                @if($contractDocument->isSigned() && $contractDocument->signature_qr_data_uri)
                    <img src="{{ $contractDocument->signature_qr_data_uri }}" alt="QR Verifikasi"
                        style="width:80px;height:80px;margin-bottom:.5rem">
                @else
                    <div style="height:60px;border-bottom:1px solid #94a3b8;margin:0 2rem .5rem;margin-bottom:.25rem"></div>
                @endif
                <div class="fw-semibold mt-2" style="font-size:.88rem">{{ $contractDocument->penandatangan_p1_name ?? $contractDocument->first_party_name ?? '—' }}</div>
                <div class="text-muted" style="font-size:.78rem">{{ $contractDocument->penandatangan_p1_position ?? $contractDocument->first_party_position ?? '' }}</div>
                @if($contractDocument->isSigned())
                    <div class="mt-2">
                        <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.7rem">
                            <i class="bi bi-patch-check-fill me-1"></i>Ditandatangani {{ $contractDocument->signed_at?->isoFormat('D MMM YYYY') }}
                        </span>
                    </div>
                    @if($contractDocument->signature_number)
                        <div class="text-muted mt-1" style="font-size:.65rem;font-family:monospace;word-break:break-all">{{ $contractDocument->signature_number }}</div>
                    @endif
                @endif
            </div>
            <div class="col-6 text-center" style="padding:1.5rem 1rem;border:1px solid #e2e8f0;border-top:1px solid #e2e8f0;border-left:none;border-radius:0 .5rem .5rem 0">
                <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Pihak Kedua</div>
                <div class="fw-semibold" style="font-size:.9rem">{{ $contractDocument->employee->name ?? $contractDocument->second_party_name ?? '—' }}</div>
                <div class="text-muted" style="font-size:.8rem;margin-bottom:3rem">{{ $contractDocument->contract_date?->isoFormat('D MMMM YYYY') }}</div>
                {{-- Signature space / employee QR --}}
                @if($contractDocument->isSignedByEmployee() && $contractDocument->signature_qr_employee)
                    <img src="{{ $contractDocument->signature_qr_employee }}" alt="QR Karyawan"
                        style="width:80px;height:80px;margin-bottom:.5rem">
                @else
                    <div style="height:60px;border-bottom:1px solid #94a3b8;margin:0 2rem;margin-bottom:.25rem"></div>
                @endif
                <div class="fw-semibold mt-2" style="font-size:.88rem">{{ $contractDocument->second_party_name ?? $contractDocument->employee->name ?? '—' }}</div>
                @if($contractDocument->second_party_ktp)
                    <div class="text-muted" style="font-size:.78rem">KTP: {{ $contractDocument->second_party_ktp }}</div>
                @endif
                @if($contractDocument->isSignedByEmployee())
                    <div class="mt-2">
                        <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.7rem">
                            <i class="bi bi-patch-check-fill me-1"></i>Ditandatangani {{ $contractDocument->signed_at_employee?->isoFormat('D MMM YYYY') }}
                        </span>
                    </div>
                    @if($contractDocument->signature_number_employee)
                        <div class="text-muted mt-1" style="font-size:.65rem;font-family:monospace;word-break:break-all">{{ $contractDocument->signature_number_employee }}</div>
                    @endif
                @else
                    <div class="mt-2">
                        <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:.7rem">
                            <i class="bi bi-hourglass me-1"></i>Belum Ditandatangani
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tanda tangan digital (QR detail) --}}
        @if($contractDocument->isSigned())
            <div class="contract-section-label">Verifikasi Tanda Tangan Digital</div>
            <div class="border rounded p-3 bg-light" style="font-size:.85rem">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <div class="text-muted">Ditandatangani secara digital oleh</div>
                        <div class="fw-semibold">{{ $contractDocument->signer?->name ?? '-' }}</div>
                        <div class="text-muted">{{ $contractDocument->signed_at?->isoFormat('D MMMM YYYY, HH:mm') }} WIB</div>
                        @if($contractDocument->signature_number)
                            <div class="text-muted mt-2" style="font-size:.75rem">Nomor Tanda Tangan</div>
                            <div class="fw-semibold" style="font-size:.78rem;word-break:break-all;font-family:monospace">{{ $contractDocument->signature_number }}</div>
                        @endif
                    </div>
                    @if($contractDocument->signature_qr_data_uri)
                        <div class="col-auto">
                            <img src="{{ $contractDocument->signature_qr_data_uri }}" alt="QR Verifikasi"
                                style="width:110px;height:110px;border:1px solid #e2e8f0;border-radius:.65rem;padding:.4rem;background:#fff">
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</div>
@endsection