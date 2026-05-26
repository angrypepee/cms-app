<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Slip Gaji - {{ $payrollSlip->slip_number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 11px;
        color: #1f2937;
        background: #fff;
        padding: 0;
    }
    .page {
        width: 210mm;
        min-height: 297mm;
        padding: 18mm 18mm 14mm 18mm;
        margin: 0 auto;
        background: #fff;
    }

    /* Header */
    .header { width: 100%; border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; margin-bottom: 14px; }
    .header table { width: 100%; border-collapse: collapse; }
    .header td { vertical-align: top; padding: 0; }
    .header td.right { text-align: right; width: 38%; }
    .company-logo { width: 52px; height: 52px; object-fit: contain; vertical-align: top; }
    .company-block { padding-left: 10px; vertical-align: top; }
    .company-name { font-size: 15px; font-weight: 700; color: #111827; line-height: 1.25; }
    .company-sub { font-size: 9px; color: #6b7280; margin-top: 2px; line-height: 1.4; }
    .slip-title { font-size: 13px; font-weight: 700; color: #1d4ed8; letter-spacing: 0.05em; text-transform: uppercase; }
    .slip-period { font-size: 12px; font-weight: 600; color: #374151; margin-top: 2px; }
    .slip-number { font-size: 9px; color: #9ca3af; font-family: monospace; margin-top: 2px; }

    /* Employee Info */
    .section-title {
        font-size: 8.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6b7280;
        margin-bottom: 6px;
    }
    .emp-grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .emp-grid td { width: 33.33%; padding: 4px 8px 4px 0; vertical-align: top; }
    .emp-label { font-size: 8.5px; color: #9ca3af; }
    .emp-value { font-size: 10.5px; font-weight: 600; color: #111827; margin-top: 1px; }

    /* Two-column items */
    .items-wrap { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin-bottom: 12px; }
    .items-wrap > tbody > tr > td { width: 50%; vertical-align: top; }
    .col-title { font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; padding-bottom: 4px; border-bottom: 1.5px solid; }
    .col-title.income { color: #15803d; border-color: #86efac; }
    .col-title.deduction { color: #b91c1c; border-color: #fca5a5; }
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table tr { border-bottom: 1px solid #f3f4f6; }
    .items-table td { padding: 4.5px 2px; vertical-align: middle; }
    .items-table td:last-child { text-align: right; font-weight: 500; white-space: nowrap; }
    .items-table tfoot td { padding-top: 6px; font-weight: 700; font-size: 10px; }
    .total-income { color: #15803d; }
    .total-deduction { color: #b91c1c; }

    /* Take Home Pay */
    .thp-box {
        background: #1d4ed8;
        color: #fff;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 16px;
        width: 100%;
    }
    .thp-box td { vertical-align: middle; color: #fff; }
    .thp-box td.right { text-align: right; }
    .thp-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; opacity: .8; }
    .thp-period { font-size: 8px; opacity: .55; margin-top: 2px; }
    .thp-amount { font-size: 20px; font-weight: 800; }

    /* Signature */
    .sig-section { width: 100%; margin-top: 18px; border-collapse: collapse; }
    .sig-section td { width: 50%; vertical-align: top; text-align: center; padding: 0 6px; }
    .sig-box .sig-label { font-size: 9.5px; color: #374151; }
    .sig-box .sig-space { width: 120px; height: 52px; border-bottom: 1px solid #9ca3af; margin: 10px auto 4px; }
    .sig-box .sig-name { font-size: 9.5px; font-weight: 600; color: #111827; }
    .sig-box .sig-title { font-size: 8px; color: #6b7280; }

    /* Footer */
    .doc-footer { margin-top: 16px; padding-top: 8px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 8px; color: #9ca3af; }

    .divider { border: none; border-top: 1px solid #e5e7eb; margin: 12px 0; }
    .notes-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px 10px; margin-bottom: 12px; }
    .notes-text { font-size: 9.5px; color: #374151; line-height: 1.5; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <table>
            <tr>
                <td>
                    <table>
                        <tr>
                            @if($payrollSlip->company->logo)
                            <td style="width:62px">
                                <img src="{{ storage_path('app/public/' . $payrollSlip->company->logo) }}" class="company-logo" alt="Logo">
                            </td>
                            @endif
                            <td class="company-block">
                                <div class="company-name">{{ $payrollSlip->company->name }}</div>
                                @if($payrollSlip->company->tagline)
                                    <div class="company-sub">{{ $payrollSlip->company->tagline }}</div>
                                @endif
                                @if($payrollSlip->company->address)
                                    <div class="company-sub">{{ $payrollSlip->company->address }}</div>
                                @endif
                                @if($payrollSlip->company->phone || $payrollSlip->company->email)
                                    <div class="company-sub">{{ implode(' | ', array_filter([$payrollSlip->company->phone, $payrollSlip->company->email])) }}</div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="right">
                    <div class="slip-title">Slip Gaji</div>
                    <div class="slip-period">{{ $payrollSlip->period_label }}</div>
                    <div class="slip-number">{{ $payrollSlip->slip_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Employee Info --}}
    <div class="section-title">Informasi Karyawan</div>
    @php
        $empFields = [
            ['Nama Lengkap',       $payrollSlip->employee->name],
            ['ID Karyawan',        $payrollSlip->employee->employee_id, true],
            ['Jabatan',            $payrollSlip->employee->position ?? '-'],
            ['Departemen',         $payrollSlip->employee->department ?? '-'],
            ['Kategori',           $payrollSlip->employee->employee_category?->label() ?? '-'],
            ['Golongan',           $payrollSlip->employee->grade ?? '-'],
            ['Tanggal Pembayaran', $payrollSlip->payment_date ? $payrollSlip->payment_date->format('d/m/Y') : '-'],
        ];
        if ($payrollSlip->cutoff_start && $payrollSlip->cutoff_end) {
            $empFields[] = ['Periode Cutoff', $payrollSlip->cutoff_start->format('d/m/Y') . ' – ' . $payrollSlip->cutoff_end->format('d/m/Y')];
        }
        if ($payrollSlip->employee->bank_name || $payrollSlip->employee->bank_account) {
            $empFields[] = ['Rekening Bank', trim(($payrollSlip->employee->bank_name ?? '') . ' ' . ($payrollSlip->employee->bank_account ?? ''))];
        }
        if ($payrollSlip->employee->npwp) {
            $empFields[] = ['NPWP', $payrollSlip->employee->npwp, true];
        }
        $empRows = array_chunk($empFields, 3);
    @endphp
    <table class="emp-grid">
        @foreach($empRows as $row)
        <tr>
            @foreach($row as $field)
            <td>
                <div class="emp-label">{{ $field[0] }}</div>
                <div class="emp-value" @if(!empty($field[2])) style="font-family:monospace" @endif>{{ $field[1] }}</div>
            </td>
            @endforeach
            @for($i = count($row); $i < 3; $i++)<td></td>@endfor
        </tr>
        @endforeach
    </table>

    <hr class="divider">

    {{-- Items --}}
    <table class="items-wrap">
        <tr>
            <td>
                <div class="col-title income">Pendapatan</div>
                <table class="items-table">
                    <tbody>
                        @foreach($payrollSlip->incomes as $item)
                        <tr>
                            <td>{{ $item->label }}</td>
                            <td>Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="total-income">Total Pendapatan</td>
                            <td class="total-income">Rp {{ number_format($payrollSlip->total_income, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>
            <td>
                <div class="col-title deduction">Potongan</div>
                <table class="items-table">
                    <tbody>
                        @forelse($payrollSlip->deductions as $item)
                        <tr>
                            <td>{{ $item->label }}</td>
                            <td>Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" style="color:#9ca3af;text-align:center;padding:8px">Tidak ada potongan</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="total-deduction">Total Potongan</td>
                            <td class="total-deduction">Rp {{ number_format($payrollSlip->total_deduction, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    {{-- Take Home Pay --}}
    <table class="thp-box">
        <tr>
            <td>
                <div class="thp-label">Take Home Pay</div>
                <div class="thp-period">Periode: {{ $payrollSlip->period_label }}</div>
            </td>
            <td class="right">
                <div class="thp-amount">Rp {{ number_format($payrollSlip->take_home_pay, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    @if($payrollSlip->notes)
    <div class="notes-box">
        <div class="section-title" style="margin-bottom:4px">Catatan</div>
        <div class="notes-text">{{ $payrollSlip->notes }}</div>
    </div>
    @endif

    {{-- Signature --}}
    <table class="sig-section">
        <tr>
            <td>
                <div class="sig-label">
                    {{ $payrollSlip->employee_signed_at ? $payrollSlip->employee_signed_at->format('d/m/Y') : '..............' }}
                </div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $payrollSlip->employee->name }}</div>
                <div class="sig-title">Karyawan</div>
            </td>
            <td>
                <div class="sig-label">
                    {{ $payrollSlip->signed_at
                        ? $payrollSlip->signed_at->format('d/m/Y')
                        : ($payrollSlip->payment_date ? $payrollSlip->payment_date->format('d/m/Y') : date('d/m/Y')) }}
                </div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $payrollSlip->signer->name ?? 'HRD / Management' }}</div>
                <div class="sig-title">{{ $payrollSlip->signer->title ?? $payrollSlip->company->name }}</div>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="doc-footer">
        Diterbitkan
        @if($payrollSlip->released_at)
            pada {{ $payrollSlip->released_at->format('d F Y') }}
        @endif
        secara digital oleh {{ $payrollSlip->company->name }}.
        Slip gaji ini sah dan tidak memerlukan tanda tangan basah.
    </div>

</div>
</body>
</html>
