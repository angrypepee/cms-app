@extends('layouts.app')
@section('title', 'Kontrak Saya')
@section('page-title', 'Kontrak Saya')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible py-2 mb-4" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible py-2 mb-4" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($contracts->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-file-earmark-text fs-1 d-block mb-2 opacity-25"></i>
            <div class="fw-semibold mb-1">Belum ada kontrak kerja</div>
            <div style="font-size:.85rem">Kontrak Anda akan muncul di sini setelah dibuatkan oleh admin.</div>
        </div>
    </div>
@else

{{-- Summary strip --}}
@php
    $unsigned  = $contracts->filter(fn($c) => !$c->isSignedByEmployee())->count();
    $fullySigned = $contracts->filter(fn($c) => $c->isFullySigned())->count();
    $adminSigned = $contracts->filter(fn($c) => $c->isSigned())->count();
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:40px;height:40px;border-radius:.65rem;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#2563eb"><i class="bi bi-file-earmark-text-fill"></i></div>
                <div><div class="fw-bold" style="font-size:1.4rem;line-height:1;color:#1e293b">{{ $contracts->count() }}</div><div class="text-muted" style="font-size:.74rem">Total Kontrak</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:40px;height:40px;border-radius:.65rem;background:#fefce8;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#ca8a04"><i class="bi bi-pen"></i></div>
                <div><div class="fw-bold" style="font-size:1.4rem;line-height:1;color:#1e293b">{{ $unsigned }}</div><div class="text-muted" style="font-size:.74rem">Perlu Tanda Tangan</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:40px;height:40px;border-radius:.65rem;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#2563eb"><i class="bi bi-patch-check"></i></div>
                <div><div class="fw-bold" style="font-size:1.4rem;line-height:1;color:#1e293b">{{ $adminSigned }}</div><div class="text-muted" style="font-size:.74rem">Ditandatangani Admin</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:40px;height:40px;border-radius:.65rem;background:#f0fdf4;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#16a34a"><i class="bi bi-patch-check-fill"></i></div>
                <div><div class="fw-bold" style="font-size:1.4rem;line-height:1;color:#1e293b">{{ $fullySigned }}</div><div class="text-muted" style="font-size:.74rem">Selesai (Kedua Pihak)</div></div>
            </div>
        </div>
    </div>
</div>

@foreach($contracts as $contract)
@php
    $p1Signed  = $contract->isSigned();
    $p2Signed  = $contract->isSignedByEmployee();
    $fullySigned = $contract->isFullySigned();
    $isCancelled = $contract->isCancelled();
    $isRejected  = $contract->isRejected();
    $isActive    = $contract->isActive();
@endphp
<div class="card mb-4">

    {{-- Header gradient --}}
    <div style="background:linear-gradient(135deg,{{ $isCancelled ? '#7f1d1d 0%,#991b1b 60%,#b91c1c' : ($isRejected ? '#78350f 0%,#92400e 60%,#b45309' : '  #1e3a8a 0%,#1d4ed8 60%,#2563eb') }} 100%);padding:1rem 1.5rem;color:#fff;border-radius:.5rem .5rem 0 0;position:relative;overflow:hidden">
        <div style="position:absolute;right:-20px;top:-20px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.07)"></div>
        <div class="d-flex align-items-start justify-content-between gap-3 position-relative" style="z-index:1">
            <div>
                <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.14em;opacity:.6">Surat Perjanjian Kerja</div>
                <div style="font-size:1.05rem;font-weight:700;line-height:1.2;margin-top:.1rem">{{ $contract->contract_number }}</div>
                @if($contract->contract_date)
                    <div style="font-size:.72rem;opacity:.7;margin-top:.1rem">
                        <i class="bi bi-calendar3 me-1"></i>{{ $contract->contract_date->isoFormat('D MMMM YYYY') }}
                        @if($contract->location) &nbsp;&bull;&nbsp; {{ $contract->location }} @endif
                    </div>
                @endif
            </div>
            <div class="text-end" style="flex-shrink:0">
                @if($isCancelled)
                    <span style="display:inline-flex;align-items:center;gap:.3rem;padding:.22em .65em;border-radius:50rem;font-size:.65rem;font-weight:700;background:rgba(252,165,165,.2);color:#fca5a5;border:1px solid rgba(252,165,165,.35)">
                        <i class="bi bi-x-circle-fill"></i>Dibatalkan Admin
                    </span>
                @elseif($isRejected)
                    <span style="display:inline-flex;align-items:center;gap:.3rem;padding:.22em .65em;border-radius:50rem;font-size:.65rem;font-weight:700;background:rgba(253,224,71,.15);color:#fde68a;border:1px solid rgba(253,224,71,.35)">
                        <i class="bi bi-hand-thumbs-down-fill"></i>Anda Menolak
                    </span>
                @elseif($fullySigned)
                    <span style="display:inline-flex;align-items:center;gap:.3rem;padding:.22em .65em;border-radius:50rem;font-size:.65rem;font-weight:700;background:rgba(134,239,172,.22);color:#bbf7d0;border:1px solid rgba(134,239,172,.35)">
                        <i class="bi bi-patch-check-fill"></i>Kedua Pihak Menandatangani
                    </span>
                @elseif(!$p2Signed)
                    <span style="display:inline-flex;align-items:center;gap:.3rem;padding:.22em .65em;border-radius:50rem;font-size:.65rem;font-weight:700;background:rgba(253,224,71,.2);color:#fde68a;border:1px solid rgba(253,224,71,.35)">
                        <i class="bi bi-hourglass-split"></i>Menunggu Tanda Tangan Anda
                    </span>
                @else
                    <span style="display:inline-flex;align-items:center;gap:.3rem;padding:.22em .65em;border-radius:50rem;font-size:.65rem;font-weight:700;background:rgba(147,197,253,.18);color:#bfdbfe;border:1px solid rgba(147,197,253,.3)">
                        <i class="bi bi-check-circle"></i>Anda Sudah Menandatangani
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-4">

            {{-- Info kontrak --}}
            <div class="col-md-6">
                <div class="border rounded p-3 h-100" style="font-size:.85rem">
                    <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Info Kontrak</div>
                    @if($contract->project_name)
                        <div><span class="text-muted">Pekerjaan:</span> <strong>{{ $contract->project_name }}</strong></div>
                    @endif
                    @if($contract->start_date)
                        <div class="mt-1"><span class="text-muted">Periode:</span>
                            <strong>{{ $contract->start_date->isoFormat('D MMM YYYY') }}</strong>
                            <span class="text-muted mx-1">→</span>
                            <strong>{{ $contract->end_date?->isoFormat('D MMM YYYY') ?? 'Permanen' }}</strong>
                        </div>
                    @endif
                    @if($contract->duration_text)
                        <div class="mt-1"><span class="text-muted">Durasi:</span> <strong>{{ $contract->duration_text }}</strong></div>
                    @endif
                    @if($contract->first_party_company)
                        <div class="mt-1"><span class="text-muted">Pemberi Kerja:</span> <strong>{{ $contract->first_party_company }}</strong></div>
                    @endif
                    @if($contract->penandatangan_p1_name)
                        <div class="mt-1"><span class="text-muted">Penandatangan P1:</span> {{ $contract->penandatangan_p1_name }}
                            @if($contract->penandatangan_p1_position)
                                <span class="text-muted">({{ $contract->penandatangan_p1_position }})</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Status tanda tangan --}}
            <div class="col-md-6">
                <div class="border rounded p-3 h-100" style="font-size:.85rem">
                    <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Status Tanda Tangan</div>

                    {{-- P1 status --}}
                    <div class="d-flex align-items-center gap-2 mb-2">
                        @if($p1Signed)
                            <i class="bi bi-patch-check-fill text-success fs-5"></i>
                        @else
                            <i class="bi bi-circle text-muted fs-5"></i>
                        @endif
                        <div>
                            <div class="fw-medium">Pihak Pertama ({{ $contract->first_party_company ?? 'Pemberi Kerja' }})</div>
                            @if($p1Signed)
                                <div class="text-success" style="font-size:.78rem">
                                    <i class="bi bi-check me-1"></i>Ditandatangani {{ $contract->signed_at?->isoFormat('D MMM YYYY') }}
                                    @if($contract->signer) oleh {{ $contract->signer->name }} @endif
                                </div>
                            @else
                                <div class="text-muted" style="font-size:.78rem">Belum ditandatangani</div>
                            @endif
                        </div>
                    </div>

                    {{-- P2 status --}}
                    <div class="d-flex align-items-center gap-2">
                        @if($p2Signed)
                            <i class="bi bi-patch-check-fill text-success fs-5"></i>
                        @else
                            <i class="bi bi-circle text-warning fs-5"></i>
                        @endif
                        <div>
                            <div class="fw-medium">Pihak Kedua (Anda)</div>
                            @if($p2Signed)
                                <div class="text-success" style="font-size:.78rem">
                                    <i class="bi bi-check me-1"></i>Ditandatangani {{ $contract->signed_at_employee?->isoFormat('D MMM YYYY') }}
                                </div>
                                @if($contract->signature_number_employee)
                                    <div class="text-muted" style="font-size:.7rem;font-family:monospace">{{ $contract->signature_number_employee }}</div>
                                @endif
                            @else
                                <div class="text-warning" style="font-size:.78rem">Menunggu tanda tangan Anda</div>
                            @endif
                        </div>
                    </div>

                    {{-- QR karyawan --}}
                    @if($p2Signed && $contract->signature_qr_employee)
                        <div class="mt-3 text-center">
                            <div class="text-muted mb-1" style="font-size:.72rem">QR Verifikasi Tanda Tangan Anda</div>
                            <img src="{{ $contract->signature_qr_employee }}" alt="QR"
                                style="width:100px;height:100px;border:1px solid #e2e8f0;border-radius:.5rem;padding:.3rem;background:#fff">
                        </div>
                    @endif
                </div>
            </div>

            {{-- Aksi --}}
            <div class="col-12">
                @if($isCancelled)
                    <div class="alert alert-danger py-2 mb-0" style="font-size:.85rem">
                        <i class="bi bi-x-circle me-1"></i><strong>Kontrak dibatalkan oleh admin.</strong>
                        @if($contract->rejection_reason) {{ $contract->rejection_reason }} @endif
                        @if($contract->rejected_at) — {{ $contract->rejected_at->isoFormat('D MMM YYYY') }} @endif
                    </div>
                @elseif($isRejected)
                    <div class="alert alert-warning py-2 mb-0" style="font-size:.85rem">
                        <i class="bi bi-hand-thumbs-down me-1"></i><strong>Anda menolak kontrak ini.</strong>
                        @if($contract->rejection_reason) Alasan: {{ $contract->rejection_reason }} @endif
                        @if($contract->rejected_at) — {{ $contract->rejected_at->isoFormat('D MMM YYYY') }} @endif
                    </div>
                @else
                <div class="d-flex gap-2 flex-wrap">
                    @if(!$p2Signed && $isActive)
                        <form method="POST" action="{{ route('my.contracts.sign', $contract) }}"
                            onsubmit="return confirm('Anda yakin ingin menandatangani kontrak ini secara digital?\n\nNomor: {{ $contract->contract_number }}\n\nPastikan Anda telah membaca dan menyetujui seluruh isi kontrak.')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-pen me-1"></i>Tandatangani Kontrak Ini
                            </button>
                        </form>
                        <button class="btn btn-outline-danger" type="button"
                            data-bs-toggle="modal"
                            data-bs-target="#rejectModal-{{ $contract->id }}">
                            <i class="bi bi-hand-thumbs-down me-1"></i>Tolak Kontrak
                        </button>
                    @endif
                    @if($contract->file_path)
                        <a href="{{ route('contract-documents.download', $contract) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-download me-1"></i>Unduh File Kontrak
                        </a>
                    @endif
                    <button class="btn btn-outline-primary" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#contract-detail-{{ $contract->id }}">
                        <i class="bi bi-eye me-1"></i>Lihat Isi Kontrak
                    </button>
                </div>
                @endif
            </div>

            {{-- Collapsible detail isi kontrak --}}
            <div class="col-12 collapse" id="contract-detail-{{ $contract->id }}">
                <div class="border rounded p-4 bg-light" style="font-size:.88rem;line-height:1.75">
                    @if($contract->first_party_name || $contract->first_party_company)
                    <div class="mb-3">
                        <strong>PIHAK PERTAMA:</strong>
                        {{ $contract->first_party_name }}
                        @if($contract->first_party_position) selaku {{ $contract->first_party_position }} @endif
                        dari {{ $contract->first_party_company }}
                    </div>
                    @endif
                    <div class="mb-3">
                        <strong>PIHAK KEDUA:</strong> {{ $employee->name }}
                        @if($contract->second_party_ktp) (KTP: {{ $contract->second_party_ktp }}) @endif
                    </div>
                    @if($contract->scope_of_work)
                        <div class="mb-3"><strong>Ruang Lingkup:</strong><br>{!! nl2br(e(strip_tags($contract->scope_of_work))) !!}</div>
                    @endif
                    @if($contract->rights_obligations)
                        <div class="mb-3"><strong>Hak &amp; Kewajiban:</strong><br>{!! nl2br(e(strip_tags($contract->rights_obligations))) !!}</div>
                    @endif
                    @if($contract->nda_terms)
                        <div class="mb-3"><strong>Kerahasiaan:</strong><br>{!! nl2br(e(strip_tags($contract->nda_terms))) !!}</div>
                    @endif
                    @if($contract->dispute_terms)
                        <div class="mb-3"><strong>Penyelesaian Perselisihan:</strong><br>{!! nl2br(e(strip_tags($contract->dispute_terms))) !!}</div>
                    @endif
                    @if($contract->notes)
                        <div class="mb-3"><strong>Catatan:</strong><br>{!! nl2br(e(strip_tags($contract->notes))) !!}</div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modal: Tolak Kontrak --}}
@if($isActive && !$p2Signed)
<div class="modal fade" id="rejectModal-{{ $contract->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('my.contracts.reject', $contract) }}">
            @csrf @method('PATCH')
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-hand-thumbs-down me-2 text-danger"></i>Tolak Kontrak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 mb-3" style="font-size:.85rem">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Penolakan akan memberitahu admin bahwa Anda tidak menyetujui kontrak <strong>{{ $contract->contract_number }}</strong>. Admin akan menghubungi Anda untuk tindak lanjut.
                    </div>
                    <div>
                        <label class="form-label fw-medium">Alasan Penolakan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea name="reason" class="form-control" rows="3"
                            placeholder="Tuliskan alasan Anda menolak kontrak ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-hand-thumbs-down me-1"></i>Ya, Tolak Kontrak</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@endforeach

@endif
@endsection
