<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Slip Gaji - {{ $payrollSlip->slip_number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    @page { margin: 14mm 12mm; }
    body {
        font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
        font-size: 10.5px;
        color: #0f172a;
        background: #fff;
    }

    .slip-doc { width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; }

    /* ── Header ── */
    .slip-header {
        background-color: #1d4ed8;
        color: #fff;
        padding: 18px 22px;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    .slip-header table { width: 100%; border-collapse: collapse; }
    .slip-header td { vertical-align: middle; color: #fff; padding: 0; }
    .slip-header td.right { text-align: right; width: 40%; }
    .company-logo {
        width: 54px; height: 54px;
        background: #fff; padding: 4px;
        border-radius: 6px;
    }
    .logo-placeholder {
        width: 54px; height: 54px;
        background: #2563eb;
        border-radius: 6px;
        text-align: center; line-height: 54px;
        font-size: 22px; font-weight: 700;
        color: #fff;
    }
    .company-block { padding-left: 12px; }
    .slip-company-name { font-size: 14px; font-weight: 700; line-height: 1.25; }
    .slip-company-meta { font-size: 8.5px; color: #cfe0ff; margin-top: 3px; line-height: 1.5; }
    .slip-label  { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #cfe0ff; }
    .slip-title  { font-size: 16px; font-weight: 800; line-height: 1; margin-top: 2px; }
    .slip-num    { font-size: 8.5px; font-family: monospace; color: #b8ccf0; margin-top: 2px; }
    .badge-status {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 50px;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: 0.05em;
        margin-top: 5px;
    }
    .badge-status.published { background: #166534; color: #bbf7d0; border: 1px solid #86efac; }
    .badge-status.draft     { background: #854d0e; color: #fde68a; border: 1px solid #fde68a; }

    /* ── Section label ── */
    .slip-section-label {
        font-size: 7.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
        margin-bottom: 8px;
        padding-bottom: 4px;
        border-bottom: 1px solid #e2e8f0;
    }

    /* ── Employee Info ── */
    .slip-emp {
        padding: 14px 22px;
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
    }
    .emp-grid { width: 100%; border-collapse: collapse; }
    .emp-grid td { vertical-align: top; padding: 4px 8px 4px 0; width: 33.33%; }
    .emp-field-label { font-size: 7.5px; color: #94a3b8; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.04em; }
    .emp-field-value { font-size: 10px; font-weight: 600; color: #0f172a; }
    .emp-name { font-size: 12px; font-weight: 700; color: #1e3a8a; }
    .mono { font-family: monospace; }

    /* ── Items ── */
    .slip-items-section { padding: 14px 22px; border-bottom: 1px solid #e2e8f0; }
    .items-wrap { width: 100%; border-collapse: separate; border-spacing: 12px 0; }
    .items-wrap > tbody > tr > td { width: 50%; vertical-align: top; padding: 0; }

    .items-col-head {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 8.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 6px;
    }
    .items-col-head.income    { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .items-col-head.deduction { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }

    .items-tbl { width: 100%; border-collapse: collapse; }
    .items-tbl td {
        padding: 5px 4px;
        font-size: 10px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }
    .items-tbl .amt { text-align: right; font-weight: 500; white-space: nowrap; }
    .items-tbl tfoot td {
        padding: 7px 4px 2px;
        font-weight: 700;
        font-size: 10px;
        border-top: 1.5px solid;
        border-bottom: none;
    }
    .items-tbl tfoot.in-foot td { color: #15803d; border-color: #86efac; }
    .items-tbl tfoot.de-foot td { color: #be123c; border-color: #fca5a5; }

    /* ── Calc summary ── */
    .slip-calc {
        padding: 10px 22px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .slip-calc table { width: 100%; border-collapse: collapse; }
    .slip-calc td { vertical-align: middle; text-align: right; padding: 0 6px; }
    .calc-label { font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; }
    .calc-val   { font-weight: 700; font-size: 10.5px; }
    .calc-val.income    { color: #15803d; }
    .calc-val.deduction { color: #be123c; }
    .calc-val.thp       { color: #1d4ed8; font-size: 12px; }
    .calc-op { font-size: 13px; color: #cbd5e1; padding: 0 6px; }

    /* ── THP banner ── */
    .slip-thp {
        background-color: #1d4ed8;
        color: #fff;
        padding: 14px 22px;
    }
    .slip-thp table { width: 100%; border-collapse: collapse; }
    .slip-thp td { vertical-align: middle; color: #fff; padding: 0; }
    .slip-thp td.right { text-align: right; }
    .thp-eyebrow { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.13em; color: #cfe0ff; }
    .thp-period-sub { font-size: 9.5px; font-weight: 600; color: #cfe0ff; margin-top: 2px; }
    .thp-amount { font-size: 22px; font-weight: 800; }

    /* ── Notes ── */
    .slip-notes {
        padding: 10px 22px;
        background: #fffbeb;
        border-bottom: 1px solid #fde68a;
    }
    .notes-title { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #92400e; margin-bottom: 3px; }
    .notes-text  { font-size: 9.5px; color: #78350f; line-height: 1.55; }

    /* ── Signatures ── */
    .slip-signatures { padding: 16px 22px; border-bottom: 1px solid #e2e8f0; }
    .sig-table { width: 100%; border-collapse: collapse; }
    .sig-table td { width: 50%; vertical-align: top; text-align: center; padding: 0 10px; }
    .sig-title { font-size: 8.5px; color: #64748b; margin-bottom: 6px; }
    .sig-space { height: 55px; border-bottom: 1.5px dashed #94a3b8; margin: 4px 0 6px; }
    .sig-space.signed-emp   { border-bottom-color: #16a34a; }
    .sig-space.signed-mgr   { border-bottom-color: #2563eb; }
    .sig-stamp {
        display: inline-block;
        margin-top: 14px;
        border: 1.5px solid;
        border-radius: 5px;
        padding: 4px 9px;
        font-size: 7px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        line-height: 1.25;
    }
    .sig-stamp.emp { color: #16a34a; border-color: #16a34a; }
    .sig-stamp.mgr { color: #2563eb; border-color: #2563eb; }
    .sig-name { font-size: 10px; font-weight: 700; color: #1e293b; }
    .sig-role { font-size: 8px; color: #94a3b8; }
    .sig-meta { font-size: 7.5px; color: #94a3b8; margin-top: 3px; }
    .sig-meta.pending { color: #cbd5e1; }

    /* ── Footer ── */
    .slip-footer {
        padding: 10px 22px;
        background: #f8fafc;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
    }
    .slip-footer table { width: 100%; border-collapse: collapse; }
    .slip-footer td { vertical-align: middle; padding: 0; }
    .slip-footer td.right { text-align: right; }
    .slip-footer-text { font-size: 8px; color: #94a3b8; line-height: 1.55; }
    .slip-footer-id   { font-size: 7.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; }
</style>
</head>
<body>

<div class="slip-doc">

    {{-- ─── HEADER ─── --}}
    <div class="slip-header">
        <table>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td style="width: 64px;">
                                @if($payrollSlip->company->logo && file_exists(storage_path('app/public/' . $payrollSlip->company->logo)))
                                    <img src="{{ storage_path('app/public/' . $payrollSlip->company->logo) }}" class="company-logo" alt="">
                                @else
                                    <div class="logo-placeholder">{{ strtoupper(substr($payrollSlip->company->name, 0, 1)) }}</div>
                                @endif
                            </td>
                            <td class="company-block">
                                <div class="slip-company-name">{{ $payrollSlip->company->name }}</div>
                                @php
                                    $meta = array_filter([
                                        $payrollSlip->company->address ?? null,
                                        $payrollSlip->company->phone ?? null,
                                        $payrollSlip->company->email ?? null,
                                    ]);
                                @endphp
                                @if(!empty($meta))
                                    <div class="slip-company-meta">{{ implode(' · ', $meta) }}</div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="right">
                    <div class="slip-label">Slip Gaji Karyawan</div>
                    <div class="slip-title">{{ $payrollSlip->period_label }}</div>
                    <div class="slip-num">{{ $payrollSlip->slip_number }}</div>
                    <span class="badge-status {{ $payrollSlip->status }}">{{ strtoupper($payrollSlip->status) }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ─── EMPLOYEE INFO ─── --}}
    <div class="slip-emp">
        <div class="slip-section-label">Informasi Karyawan</div>
        @php
            $fields = [
                ['Nama Lengkap',       $payrollSlip->employee->name, false, true],
                ['ID Karyawan',        $payrollSlip->employee->employee_id, true],
                ['Jabatan',            $payrollSlip->employee->position ?? '-'],
                ['Departemen',         $payrollSlip->employee->department ?? '-'],
                ['Golongan',           $payrollSlip->employee->grade ?? '-'],
                ['Kategori',           $payrollSlip->employee->employee_category?->label() ?? '-'],
                ['Tanggal Pembayaran', $payrollSlip->payment_date ? $payrollSlip->payment_date->format('d M Y') : '-'],
            ];
            if ($payrollSlip->cutoff_start && $payrollSlip->cutoff_end) {
                $fields[] = ['Periode Cutoff', $payrollSlip->cutoff_start->format('d M Y') . ' – ' . $payrollSlip->cutoff_end->format('d M Y')];
            }
            if ($payrollSlip->employee->bank_name || $payrollSlip->employee->bank_account) {
                $fields[] = ['Rekening Bank', trim(($payrollSlip->employee->bank_name ?? '') . ' ' . ($payrollSlip->employee->bank_account ?? ''))];
            }
            if ($payrollSlip->employee->npwp) {
                $fields[] = ['NPWP', $payrollSlip->employee->npwp, true];
            }
            $rows = array_chunk($fields, 3);
        @endphp
        <table class="emp-grid">
            @foreach($rows as $row)
            <tr>
                @foreach($row as $f)
                <td>
                    <div class="emp-field-label">{{ $f[0] }}</div>
                    <div class="{{ !empty($f[3]) ? 'emp-name' : 'emp-field-value' }} {{ !empty($f[2]) ? 'mono' : '' }}">{{ $f[1] }}</div>
                </td>
                @endforeach
                @for($i = count($row); $i < 3; $i++)<td></td>@endfor
            </tr>
            @endforeach
        </table>
    </div>

    {{-- ─── INCOME & DEDUCTIONS ─── --}}
    <div class="slip-items-section">
        <table class="items-wrap">
            <tr>
                <td>
                    <div class="items-col-head income">+ Pendapatan</div>
                    <table class="items-tbl">
                        <tbody>
                            @foreach($payrollSlip->incomes as $item)
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
                </td>
                <td>
                    <div class="items-col-head deduction">− Potongan</div>
                    <table class="items-tbl">
                        <tbody>
                            @forelse($payrollSlip->deductions as $item)
                            <tr>
                                <td>{{ $item->label }}</td>
                                <td class="amt">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" style="color:#94a3b8;text-align:center;padding:10px;font-size:9.5px">Tidak ada potongan</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="de-foot">
                            <tr>
                                <td>Total Potongan</td>
                                <td class="amt">Rp {{ number_format($payrollSlip->total_deduction, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- ─── CALC SUMMARY ─── --}}
    <div class="slip-calc">
        <table>
            <tr>
                <td></td>
                <td>
                    <div class="calc-label">Total Pendapatan</div>
                    <div class="calc-val income">Rp {{ number_format($payrollSlip->total_income, 0, ',', '.') }}</div>
                </td>
                <td style="width:18px"><span class="calc-op">−</span></td>
                <td>
                    <div class="calc-label">Total Potongan</div>
                    <div class="calc-val deduction">Rp {{ number_format($payrollSlip->total_deduction, 0, ',', '.') }}</div>
                </td>
                <td style="width:18px"><span class="calc-op">=</span></td>
                <td>
                    <div class="calc-label">Take Home Pay</div>
                    <div class="calc-val thp">Rp {{ number_format($payrollSlip->take_home_pay, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ─── THP BANNER ─── --}}
    <div class="slip-thp">
        <table>
            <tr>
                <td>
                    <div class="thp-eyebrow">Take Home Pay</div>
                    <div class="thp-period-sub">{{ $payrollSlip->period_label }}</div>
                </td>
                <td class="right">
                    <div class="thp-amount">Rp {{ number_format($payrollSlip->take_home_pay, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ─── NOTES ─── --}}
    @if($payrollSlip->notes)
    <div class="slip-notes">
        <div class="notes-title">Catatan</div>
        <div class="notes-text">{{ $payrollSlip->notes }}</div>
    </div>
    @endif

    {{-- ─── SIGNATURES ─── --}}
    <div class="slip-signatures">
        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-title">Diterima oleh,</div>
                    @if($payrollSlip->isEmployeeSigned())
                        <div class="sig-space signed-emp">
                            <span class="sig-stamp emp">Digital<br>Signed</span>
                        </div>
                        <div class="sig-name">{{ $payrollSlip->employee->name }}</div>
                        <div class="sig-role">Karyawan</div>
                        <div class="sig-meta">✓ {{ $payrollSlip->employee_signed_at->format('d M Y, H:i') }} WIB</div>
                    @else
                        <div class="sig-space"></div>
                        <div class="sig-name">{{ $payrollSlip->employee->name }}</div>
                        <div class="sig-role">Karyawan</div>
                        <div class="sig-meta pending">Belum ditandatangani</div>
                    @endif
                </td>
                <td>
                    <div class="sig-title">Disetujui &amp; Ditandatangani oleh,</div>
                    @if($payrollSlip->isSigned() && $payrollSlip->signer)
                        <div class="sig-space signed-mgr">
                            <span class="sig-stamp mgr">Digital<br>Signature</span>
                        </div>
                        <div class="sig-name">{{ $payrollSlip->signer->name }}</div>
                        <div class="sig-role">{{ $payrollSlip->signer->title ?? $payrollSlip->signer->role?->label() }}</div>
                        <div class="sig-meta">✓ {{ $payrollSlip->signed_at->format('d M Y, H:i') }} WIB</div>
                    @else
                        <div class="sig-space"></div>
                        <div class="sig-name">HRD / Management</div>
                        <div class="sig-role">{{ $payrollSlip->company->name }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- ─── FOOTER ─── --}}
    <div class="slip-footer">
        <table>
            <tr>
                <td>
                    <div class="slip-footer-text">
                        Dokumen ini diterbitkan secara resmi oleh <strong>{{ $payrollSlip->company->name }}</strong>.<br>
                        Slip gaji ini sah sebagai bukti pembayaran dan tidak memerlukan tanda tangan basah.
                    </div>
                </td>
                <td class="right" style="width:35%">
                    <div class="slip-footer-id">
                        Diterbitkan · {{ $payrollSlip->slip_number }}<br>
                        {{ ($payrollSlip->released_at ?? $payrollSlip->created_at)->format('d M Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>
