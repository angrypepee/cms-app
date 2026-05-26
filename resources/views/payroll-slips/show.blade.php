@extends('layouts.app')
@section('title', 'Slip Gaji - '.$payrollSlip->slip_number)
@section('page-title', 'Detail Slip Gaji')
@section('breadcrumb', 'Slip Gaji')

@push('styles')
<style>
/* ─── Slip Document wrapper ─── */
.slip-doc {
    max-width: 820px; margin: 0 auto;
    background: #fff; border-radius: .85rem;
    box-shadow: 0 6px 32px rgba(0,0,0,.12);
    overflow: hidden; border: 1px solid #e2e8f0;
}

/* ─── Header ─── */
.slip-header {
    background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 60%, #2563eb 100%);
    padding: 1.75rem 2rem; color: #fff; position: relative; overflow: hidden;
}
.slip-header::before {
    content:''; position:absolute; right:-50px; top:-50px;
    width:180px; height:180px; border-radius:50%;
    background:rgba(255,255,255,.07);
}
.slip-header::after {
    content:''; position:absolute; right:60px; bottom:-60px;
    width:120px; height:120px; border-radius:50%;
    background:rgba(255,255,255,.05);
}
.slip-header .company-logo {
    width:64px; height:64px; border-radius:.65rem;
    object-fit:contain; background:#fff; padding:6px;
    box-shadow:0 2px 8px rgba(0,0,0,.2);
}
.slip-header .logo-placeholder {
    width:64px; height:64px; border-radius:.65rem; flex-shrink:0;
    background:rgba(255,255,255,.18); display:flex; align-items:center;
    justify-content:center; font-size:1.7rem;
}
.slip-company-name { font-size:1.15rem; font-weight:700; line-height:1.25; }
.slip-company-meta { font-size:.73rem; opacity:.72; margin-top:.3rem; line-height:1.65; }
.slip-label { font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.14em; opacity:.65; }
.slip-title { font-size:1.55rem; font-weight:800; letter-spacing:.01em; line-height:1; margin-top:.1rem; }
.slip-period-text { font-size:.88rem; font-weight:600; opacity:.82; margin-top:.15rem; }
.slip-num { font-size:.7rem; font-family:monospace; opacity:.55; margin-top:.15rem; }
.badge-status {
    display:inline-flex; align-items:center; gap:.3rem;
    padding:.28em .75em; border-radius:50rem; font-size:.68rem; font-weight:700;
    letter-spacing:.05em; margin-top:.5rem;
}
.badge-status.published { background:rgba(134,239,172,.22); color:#bbf7d0; border:1px solid rgba(134,239,172,.38); }
.badge-status.draft     { background:rgba(253,224,71,.2);   color:#fde68a; border:1px solid rgba(253,224,71,.35); }

/* ─── Section separator label ─── */
.slip-section-label {
    font-size:.62rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.1em; color:#94a3b8; margin-bottom:.75rem;
    display:flex; align-items:center; gap:.4rem;
}
.slip-section-label::after {
    content:''; flex:1; height:1px; background:#e2e8f0; display:block;
}

/* ─── Employee Info Panel ─── */
.slip-emp { padding:1.4rem 2rem; background:#f8fafc; border-bottom:2px solid #e2e8f0; }
.emp-field-label { font-size:.7rem; color:#94a3b8; margin-bottom:.15rem; }
.emp-field-value { font-size:.875rem; font-weight:600; color:#0f172a; }
.emp-name { font-size:1.15rem; font-weight:700; color:#1e3a8a; }

/* ─── Items Section ─── */
.slip-items-section { padding:1.5rem 2rem; border-bottom:1px solid #e2e8f0; }
.items-col-head {
    display:flex; align-items:center; gap:.4rem;
    padding:.5rem .75rem; border-radius:.45rem;
    font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em;
    margin-bottom:.6rem;
}
.items-col-head.income   { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.items-col-head.deduction{ background:#fff1f2; color:#be123c; border:1px solid #fecdd3; }

.items-tbl { width:100%; border-collapse:collapse; }
.items-tbl td {
    padding:.42rem .4rem; font-size:.84rem; color:#334155;
    border-bottom:1px solid #f1f5f9;
}
.items-tbl tbody tr:last-child td { border-bottom:none; }
.items-tbl .amt { text-align:right; font-weight:500; white-space:nowrap; }
.items-tbl tfoot td {
    padding:.6rem .4rem; font-weight:700; font-size:.84rem;
    border-top:1.5px solid; border-bottom:none;
}
.items-tbl tfoot.in-foot td { color:#15803d; border-color:#86efac; }
.items-tbl tfoot.de-foot td { color:#be123c; border-color:#fca5a5; }

/* ─── Calculation Summary ─── */
.slip-calc {
    padding:.9rem 2rem; background:#f8fafc; border-bottom:1px solid #e2e8f0;
    display:flex; align-items:center; justify-content:flex-end;
    gap:.85rem; flex-wrap:wrap;
}
.calc-pill {
    display:flex; flex-direction:column; align-items:flex-end;
}
.calc-pill-label { font-size:.65rem; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; }
.calc-pill-val   { font-weight:700; font-size:.92rem; }
.calc-op         { font-size:1.1rem; color:#cbd5e1; }

/* ─── THP Banner ─── */
.slip-thp {
    background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
    padding:1.5rem 2rem; color:#fff;
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:1rem;
}
.thp-left .thp-eyebrow {
    font-size:.65rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.13em; opacity:.65;
}
.thp-left .thp-period-sub { font-size:.82rem; font-weight:600; opacity:.82; margin-top:.15rem; }
.thp-amount { font-size:2.1rem; font-weight:800; letter-spacing:-.02em; }

/* ─── Notes ─── */
.slip-notes {
    padding:1rem 2rem; background:#fffbeb; border-bottom:1px solid #fde68a;
    display:flex; align-items:flex-start; gap:.75rem;
}

/* ─── Signatures ─── */
.slip-signatures {
    padding:1.5rem 2rem; border-bottom:1px solid #e2e8f0;
    display:flex; gap:1.5rem; justify-content:space-between;
}
.sig-block { flex:1; text-align:center; max-width:200px; }
.sig-title  { font-size:.72rem; color:#64748b; margin-bottom:.5rem; }
.sig-space  { height:58px; border-bottom:1.5px dashed #94a3b8; margin-bottom:.5rem; }
.sig-name   { font-size:.82rem; font-weight:700; color:#1e293b; }
.sig-role   { font-size:.7rem; color:#94a3b8; }

/* ─── Document Footer ─── */
.slip-footer {
    padding:.9rem 2rem; background:#f8fafc;
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:.5rem;
}
.slip-footer-text { font-size:.7rem; color:#94a3b8; line-height:1.55; }
.slip-footer-id   { font-size:.68rem; font-weight:700; color:#cbd5e1;
                    text-transform:uppercase; letter-spacing:.06em; white-space:nowrap; }

/* ─── Print ─── */
@media print {
    #sidebar, #topbar, .no-print { display:none !important; }
    #main-wrapper { margin-left:0 !important; }
    main.p-4 { padding:0 !important; }
    .slip-doc { box-shadow:none !important; border:none !important; border-radius:0 !important; }
    .slip-header, .slip-thp { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
}
</style>
@endpush

@section('content')

{{-- ── Action Bar (no-print) ── --}}
<div class="d-flex flex-wrap gap-2 mb-4 no-print">
    @unless(auth()->user()->isEmployee())
        @if($payrollSlip->status === 'draft')
            <form method="POST" action="{{ route('payroll-slips.publish', $payrollSlip) }}">
                @csrf @method('PATCH')
                <button class="btn btn-success"><i class="bi bi-send me-1"></i>Publish Slip</button>
            </form>
        @else
            <span class="badge d-flex align-items-center px-3 badge-published badge-pill">
                <i class="bi bi-check-circle-fill me-1"></i>Published
            </span>
        @endif
    @endunless

    @auth
    @if(auth()->user()->canSign() && $payrollSlip->status === 'published' && !$payrollSlip->isSigned())
        <form method="POST" action="{{ route('payroll-slips.sign', $payrollSlip) }}"
              onsubmit="return confirm('Tandatangani slip ini atas nama Anda?')">
            @csrf @method('PATCH')
            <button class="btn btn-primary"><i class="bi bi-pen me-1"></i>Tanda Tangani Digital</button>
        </form>
    @elseif($payrollSlip->isSigned())
        <span class="badge d-flex align-items-center px-3 gap-1" style="background:#dcfce7;color:#15803d;border:1px solid #86efac;font-size:.78rem;border-radius:50rem">
            <i class="bi bi-patch-check-fill"></i>Ditandatangani oleh {{ $payrollSlip->signer?->name }}
        </span>
    @endif
    @endauth

    <a href="{{ route('payroll-slips.pdf', $payrollSlip) }}" class="btn btn-outline-danger">
        <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
    </a>
    <button onclick="window.print()" class="btn btn-outline-secondary">
        <i class="bi bi-printer me-1"></i>Cetak
    </button>
    @unless(auth()->user()->isEmployee())
        <a href="{{ route('payroll-slips.edit', $payrollSlip) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
    @endunless
    @if(auth()->user()->isEmployee())
        <a href="{{ route('my.slips') }}" class="btn btn-outline-secondary ms-auto">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    @else
        <a href="{{ route('payroll-slips.index') }}" class="btn btn-outline-secondary ms-auto">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    @endif
</div>

{{-- ══════════════════════════════════════════ --}}
{{--              SLIP DOCUMENT                --}}
{{-- ══════════════════════════════════════════ --}}
<div class="slip-doc">

    {{-- ── HEADER ── --}}
    <div class="slip-header">
        <div class="d-flex align-items-start justify-content-between gap-4" style="position:relative;z-index:1;flex-wrap:nowrap">

            {{-- Left: Company --}}
            <div class="d-flex align-items-center gap-3" style="flex:1 1 auto;min-width:0">
                @if($payrollSlip->company->logo)
                    <img src="{{ asset('storage/'.$payrollSlip->company->logo) }}" class="company-logo flex-shrink-0" alt="{{ $payrollSlip->company->name }}">
                @else
                    <div class="logo-placeholder flex-shrink-0"><i class="bi bi-building-fill"></i></div>
                @endif
                <div style="min-width:0;overflow-wrap:anywhere;word-break:break-word">
                    <div class="slip-company-name">{{ $payrollSlip->company->name }}</div>
                    <div class="slip-company-meta">
                        @if($payrollSlip->company->tagline){{ $payrollSlip->company->tagline }}<br>@endif
                        @if($payrollSlip->company->address){{ $payrollSlip->company->address }}<br>@endif
                        @if($payrollSlip->company->phone || $payrollSlip->company->email)
                            {{ implode('  ·  ', array_filter([$payrollSlip->company->phone, $payrollSlip->company->email])) }}
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Slip Info --}}
            <div class="text-end" style="flex:0 0 auto;min-width:170px;max-width:230px">
                <div class="slip-label">Slip Gaji Karyawan</div>
                <div class="slip-title">{{ $payrollSlip->period_label }}</div>
                <div class="slip-num">{{ $payrollSlip->slip_number }}</div>
                <span class="badge-status {{ $payrollSlip->status }}">
                    <i class="bi bi-{{ $payrollSlip->status === 'published' ? 'check-circle-fill' : 'pencil-square' }}"></i>
                    {{ strtoupper($payrollSlip->status) }}
                </span>
            </div>

        </div>
    </div>
    {{-- /HEADER --}}

    {{-- ── EMPLOYEE INFO ── --}}
    <div class="slip-emp">
        <div class="slip-section-label"><i class="bi bi-person-badge"></i> Informasi Karyawan</div>
        <div class="row g-3">
            <div class="col-sm-6 col-lg-4">
                <div class="emp-field-label">Nama Lengkap</div>
                <div class="emp-name">{{ $payrollSlip->employee->name }}</div>
            </div>
            <div class="col-6 col-sm-3 col-lg-2">
                <div class="emp-field-label">ID Karyawan</div>
                <div class="emp-field-value font-monospace" style="font-size:.8rem">{{ $payrollSlip->employee->employee_id }}</div>
            </div>
            <div class="col-6 col-sm-3 col-lg-3">
                <div class="emp-field-label">Jabatan</div>
                <div class="emp-field-value">{{ $payrollSlip->employee->position ?? '-' }}</div>
            </div>
            <div class="col-6 col-sm-3 col-lg-3">
                <div class="emp-field-label">Departemen</div>
                <div class="emp-field-value">{{ $payrollSlip->employee->department ?? '-' }}</div>
            </div>
            <div class="col-6 col-sm-3 col-lg-2">
                <div class="emp-field-label">Golongan</div>
                <div class="emp-field-value">{{ $payrollSlip->employee->grade ?? '-' }}</div>
            </div>
            <div class="col-sm-4 col-lg-3">
                <div class="emp-field-label">Kategori</div>
                <div class="emp-field-value">
                    @if($payrollSlip->employee->employee_category)
                        <span class="badge bg-{{ $payrollSlip->employee->employee_category->badgeColor() }} bg-opacity-10 text-{{ $payrollSlip->employee->employee_category->badgeColor() }}" style="font-size:.72rem">
                            {{ $payrollSlip->employee->employee_category->label() }}
                        </span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </div>
            </div>
            @if($payrollSlip->employee->bank_name || $payrollSlip->employee->bank_account)
            <div class="col-sm-5 col-lg-4">
                <div class="emp-field-label">Rekening Bank</div>
                <div class="emp-field-value">{{ trim(($payrollSlip->employee->bank_name ?? '') . ' ' . ($payrollSlip->employee->bank_account ?? '')) }}</div>
            </div>
            @endif
            @if($payrollSlip->employee->npwp)
            <div class="col-sm-4 col-lg-3">
                <div class="emp-field-label">NPWP</div>
                <div class="emp-field-value font-monospace" style="font-size:.78rem">{{ $payrollSlip->employee->npwp }}</div>
            </div>
            @endif
            <div class="col-sm-4 col-lg-3">
                <div class="emp-field-label">Tanggal Pembayaran</div>
                <div class="emp-field-value">{{ $payrollSlip->payment_date ? $payrollSlip->payment_date->format('d M Y') : '-' }}</div>
            </div>
            @if($payrollSlip->cutoff_start && $payrollSlip->cutoff_end)
            <div class="col-sm-5 col-lg-4">
                <div class="emp-field-label">Periode Cutoff</div>
                <div class="emp-field-value">{{ $payrollSlip->cutoff_start->format('d M Y') }} – {{ $payrollSlip->cutoff_end->format('d M Y') }}</div>
            </div>
            @endif
        </div>
    </div>
    {{-- /EMPLOYEE INFO --}}

    {{-- ── INCOME & DEDUCTIONS ── --}}
    <div class="slip-items-section">
        <div class="row g-4">

            {{-- Income --}}
            <div class="col-sm-6">
                <div class="items-col-head income">
                    <i class="bi bi-plus-circle-fill"></i> Pendapatan
                </div>
                <table class="items-tbl">
                    <tbody>
                        @foreach($payrollSlip->incomes()->get() as $item)
                        <tr>
                            <td>{{ $item->label }}</td>
                            <td class="amt">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="in-foot">
                        <tr>
                            <td>Total Pendapatan</td>
                            <td class="amt">Rp {{ number_format($payrollSlip->total_income, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Deductions --}}
            <div class="col-sm-6">
                <div class="items-col-head deduction">
                    <i class="bi bi-dash-circle-fill"></i> Potongan
                </div>
                @php $deductions = $payrollSlip->deductions()->get() @endphp
                <table class="items-tbl">
                    <tbody>
                        @forelse($deductions as $item)
                        <tr>
                            <td>{{ $item->label }}</td>
                            <td class="amt">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-muted text-center py-3" style="font-size:.8rem">
                                <i class="bi bi-inbox d-block mb-1 opacity-25 fs-5"></i>Tidak ada potongan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="de-foot">
                        <tr>
                            <td>Total Potongan</td>
                            <td class="amt">Rp {{ number_format($payrollSlip->total_deduction, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
    {{-- /INCOME & DEDUCTIONS --}}

    {{-- ── CALCULATION SUMMARY ── --}}
    <div class="slip-calc">
        <div class="calc-pill">
            <span class="calc-pill-label">Total Pendapatan</span>
            <span class="calc-pill-val text-success">Rp {{ number_format($payrollSlip->total_income, 0, ',', '.') }}</span>
        </div>
        <span class="calc-op">−</span>
        <div class="calc-pill">
            <span class="calc-pill-label">Total Potongan</span>
            <span class="calc-pill-val text-danger">Rp {{ number_format($payrollSlip->total_deduction, 0, ',', '.') }}</span>
        </div>
        <span class="calc-op">=</span>
        <div class="calc-pill">
            <span class="calc-pill-label">Take Home Pay</span>
            <span class="calc-pill-val text-primary" style="font-size:1.05rem">Rp {{ number_format($payrollSlip->take_home_pay, 0, ',', '.') }}</span>
        </div>
    </div>
    {{-- /CALC --}}

    {{-- ── THP BANNER ── --}}
    <div class="slip-thp">
        <div class="thp-left">
            <div class="thp-eyebrow"><i class="bi bi-wallet2 me-1"></i>Take Home Pay</div>
            <div class="thp-period-sub">{{ $payrollSlip->period_label }}</div>
        </div>
        <div class="thp-amount">Rp {{ number_format($payrollSlip->take_home_pay, 0, ',', '.') }}</div>
    </div>
    {{-- /THP --}}

    {{-- ── NOTES (conditional) ── --}}
    @if($payrollSlip->notes)
    <div class="slip-notes">
        <i class="bi bi-info-circle-fill text-warning fs-5 flex-shrink-0"></i>
        <div>
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#92400e;margin-bottom:.2rem">Catatan</div>
            <div style="font-size:.85rem;color:#78350f;line-height:1.55">{{ $payrollSlip->notes }}</div>
        </div>
    </div>
    @endif
    {{-- /NOTES --}}

    {{-- ── SIGNATURES ── --}}
    @php
        $authUser     = auth()->user();
        $isThisEmpUser = $authUser && $payrollSlip->employee->user_id === $authUser->id;
    @endphp
    <div class="slip-signatures">
        <div class="sig-block">
            <div class="sig-title">Diterima oleh,</div>
            @if($payrollSlip->isEmployeeSigned())
                {{-- Employee digital signature stamp --}}
                <div class="sig-space position-relative" style="border-bottom-color:#16a34a">
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center">
                        <div style="border:2px solid #16a34a;border-radius:.5rem;padding:.25rem .6rem;transform:rotate(-3deg);opacity:.85;text-align:center">
                            <div style="font-size:.55rem;font-weight:700;color:#16a34a;letter-spacing:.08em;text-transform:uppercase">Digital</div>
                            <div style="font-size:.55rem;font-weight:700;color:#16a34a;letter-spacing:.08em;text-transform:uppercase">Signed</div>
                        </div>
                    </div>
                </div>
                <div class="sig-name">{{ $payrollSlip->employee->name }}</div>
                <div class="sig-role">Karyawan</div>
                <div style="font-size:.65rem;color:#94a3b8;margin-top:.2rem">
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    {{ $payrollSlip->employee_signed_at->format('d M Y, H:i') }} WIB
                </div>
            @elseif($isThisEmpUser && $payrollSlip->status === 'published')
                {{-- Sign button for employee --}}
                <div class="sig-space d-flex align-items-center justify-content-center">
                    <form method="POST" action="{{ route('my.slips.sign', $payrollSlip) }}" class="no-print">
                        @csrf
                        <button class="btn btn-sm btn-outline-success"
                            onclick="return confirm('Tandatangani slip ini sebagai tanda terima gaji?')">
                            <i class="bi bi-pen me-1"></i>Tanda Tangani
                        </button>
                    </form>
                </div>
                <div class="sig-name">{{ $payrollSlip->employee->name }}</div>
                <div class="sig-role">Karyawan</div>
            @else
                <div class="sig-space"></div>
                <div class="sig-name">{{ $payrollSlip->employee->name }}</div>
                <div class="sig-role">Karyawan</div>
                <div style="font-size:.65rem;color:#cbd5e1;margin-top:.2rem">
                    <i class="bi bi-hourglass-split me-1"></i>Belum ditandatangani
                </div>
            @endif
        </div>
        <div class="d-flex flex-column align-items-center justify-content-end" style="color:#e2e8f0;font-size:2rem;padding-bottom:.5rem">
            &amp;
        </div>
        <div class="sig-block">
            <div class="sig-title">Disetujui &amp; Ditandatangani oleh,</div>
            @if($payrollSlip->isSigned() && $payrollSlip->signer)
                {{-- Digital signature stamp --}}
                <div class="sig-space position-relative" style="border-bottom-color:#2563eb">
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center">
                        <div style="border:2px solid #2563eb;border-radius:.5rem;padding:.25rem .6rem;transform:rotate(-3deg);opacity:.85;text-align:center">
                            <div style="font-size:.55rem;font-weight:700;color:#2563eb;letter-spacing:.08em;text-transform:uppercase">Digital</div>
                            <div style="font-size:.55rem;font-weight:700;color:#2563eb;letter-spacing:.08em;text-transform:uppercase">Signature</div>
                        </div>
                    </div>
                </div>
                <div class="sig-name">{{ $payrollSlip->signer->name }}</div>
                <div class="sig-role">{{ $payrollSlip->signer->title ?? $payrollSlip->signer->role?->label() }}</div>
                <div style="font-size:.65rem;color:#94a3b8;margin-top:.2rem">
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    {{ $payrollSlip->signed_at->format('d M Y, H:i') }} WIB
                </div>
            @elseif(auth()->check() && auth()->user()->canSign() && $payrollSlip->status === 'published')
                {{-- Sign button --}}
                <div class="sig-space d-flex align-items-center justify-content-center">
                    <form method="POST" action="{{ route('payroll-slips.sign', $payrollSlip) }}" class="no-print">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-primary" onclick="return confirm('Tandatangani slip ini sebagai {{ addslashes(auth()->user()->name) }}?')">
                            <i class="bi bi-pen me-1"></i>Tanda Tangani
                        </button>
                    </form>
                </div>
                <div class="sig-name">{{ auth()->user()->name }}</div>
                <div class="sig-role">{{ auth()->user()->title ?? auth()->user()->role?->label() }}</div>
            @else
                <div class="sig-space"></div>
                <div class="sig-name">HRD / Management</div>
                <div class="sig-role">{{ $payrollSlip->company->name }}</div>
            @endif
        </div>
    </div>
    {{-- /SIGNATURES --}}

    {{-- ── DOCUMENT FOOTER ── --}}
    <div class="slip-footer">
        <div class="slip-footer-text">
            Dokumen ini diterbitkan secara resmi oleh <strong>{{ $payrollSlip->company->name }}</strong>.<br>
            Slip gaji ini sah sebagai bukti pembayaran dan tidak memerlukan tanda tangan basah.
        </div>
        <div class="slip-footer-id">
            <i class="bi bi-shield-check me-1"></i>{{ $payrollSlip->slip_number }}
            &nbsp;·&nbsp; {{ now()->format('d M Y') }}
        </div>
    </div>
    {{-- /FOOTER --}}

</div>
{{-- /SLIP DOCUMENT --}}

@endsection
