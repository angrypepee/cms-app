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
    .header { display: flex; align-items: flex-start; justify-content: space-between; padding-bottom: 12px; border-bottom: 2px solid #1d4ed8; margin-bottom: 12px; }
    .header-left { display: flex; align-items: center; gap: 12px; }
    .company-logo { width: 56px; height: 56px; object-fit: contain; border-radius: 4px; }
    .company-name { font-size: 16px; font-weight: 700; color: #111827; line-height: 1.2; }
    .company-sub { font-size: 9.5px; color: #6b7280; margin-top: 2px; }
    .header-right { text-align: right; }
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
    .emp-grid { display: flex; flex-wrap: wrap; gap: 4px 0; margin-bottom: 14px; }
    .emp-field { width: 33.33%; padding: 4px 8px 4px 0; }
    .emp-label { font-size: 8.5px; color: #9ca3af; }
    .emp-value { font-size: 10.5px; font-weight: 600; color: #111827; margin-top: 1px; }

    /* Two-column items */
    .items-wrap { display: flex; gap: 16px; margin-bottom: 12px; }
    .items-col { flex: 1; }
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
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .thp-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; opacity: .8; }
    .thp-period { font-size: 8px; opacity: .55; margin-top: 2px; }
    .thp-amount { font-size: 20px; font-weight: 800; }

    /* Signature */
    .sig-section { display: flex; justify-content: flex-end; margin-top: 18px; }
    .sig-box { text-align: center; }
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
        <div class="header-left">
            @if($payrollSlip->company->logo)
                <img src="{{ storage_path('app/public/' . $payrollSlip->company->logo) }}" class="company-logo" alt="Logo">
            @endif
            <div>
                <div class="company-name">{{ $payrollSlip->company->name }}</div>
                @if($payrollSlip->company->tagline)
                    <div class="company-sub">{{ $payrollSlip->company->tagline }}</div>
                @endif
                @if($payrollSlip->company->address)
                    <div class="company-sub" style="margin-top:3px">{{ $payrollSlip->company->address }}</div>
                @endif
                @if($payrollSlip->company->phone || $payrollSlip->company->email)
                    <div class="company-sub">{{ implode(' | ', array_filter([$payrollSlip->company->phone, $payrollSlip->company->email])) }}</div>
                @endif
            </div>
        </div>
        <div class="header-right">
            <div class="slip-title">Slip Gaji</div>
            <div class="slip-period">{{ $payrollSlip->period_label }}</div>
            <div class="slip-number">{{ $payrollSlip->slip_number }}</div>
        </div>
    </div>

    {{-- Employee Info --}}
    <div class="section-title">Informasi Karyawan</div>
    <div class="emp-grid">
        <div class="emp-field">
            <div class="emp-label">Nama Lengkap</div>
            <div class="emp-value">{{ $payrollSlip->employee->name }}</div>
        </div>
        <div class="emp-field">
            <div class="emp-label">ID Karyawan</div>
            <div class="emp-value" style="font-family:monospace">{{ $payrollSlip->employee->employee_id }}</div>
        </div>
        <div class="emp-field">
            <div class="emp-label">Jabatan</div>
            <div class="emp-value">{{ $payrollSlip->employee->position ?? '-' }}</div>
        </div>
        <div class="emp-field">
            <div class="emp-label">Departemen</div>
            <div class="emp-value">{{ $payrollSlip->employee->department ?? '-' }}</div>
        </div>
        <div class="emp-field">
            <div class="emp-label">Kategori</div>
            <div class="emp-value">{{ $payrollSlip->employee->employee_category?->label() ?? '-' }}</div>
        </div>
        <div class="emp-field">
            <div class="emp-label">Golongan</div>
            <div class="emp-value">{{ $payrollSlip->employee->grade ?? '-' }}</div>
        </div>
        <div class="emp-field">
            <div class="emp-label">Tanggal Pembayaran</div>
            <div class="emp-value">{{ $payrollSlip->payment_date ? $payrollSlip->payment_date->format('d/m/Y') : '-' }}</div>
        </div>
        @if($payrollSlip->cutoff_start && $payrollSlip->cutoff_end)
        <div class="emp-field">
            <div class="emp-label">Periode Cutoff</div>
            <div class="emp-value">{{ $payrollSlip->cutoff_start->format('d/m/Y') }} – {{ $payrollSlip->cutoff_end->format('d/m/Y') }}</div>
        </div>
        @endif
        @if($payrollSlip->employee->bank_name || $payrollSlip->employee->bank_account)
        <div class="emp-field">
            <div class="emp-label">Rekening Bank</div>
            <div class="emp-value">{{ trim($payrollSlip->employee->bank_name . ' ' . $payrollSlip->employee->bank_account) }}</div>
        </div>
        @endif
        @if($payrollSlip->employee->npwp)
        <div class="emp-field">
            <div class="emp-label">NPWP</div>
            <div class="emp-value" style="font-family:monospace">{{ $payrollSlip->employee->npwp }}</div>
        </div>
        @endif
    </div>

    <hr class="divider">

    {{-- Items --}}
    <div class="items-wrap">
        <div class="items-col">
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
        </div>
        <div class="items-col">
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
        </div>
    </div>

    {{-- Take Home Pay --}}
    <div class="thp-box">
        <div>
            <div class="thp-label">Take Home Pay</div>
            <div class="thp-period">Periode: {{ $payrollSlip->period_label }}</div>
        </div>
        <div class="thp-amount">Rp {{ number_format($payrollSlip->take_home_pay, 0, ',', '.') }}</div>
    </div>

    @if($payrollSlip->notes)
    <div class="notes-box">
        <div class="section-title" style="margin-bottom:4px">Catatan</div>
        <div class="notes-text">{{ $payrollSlip->notes }}</div>
    </div>
    @endif

    {{-- Signature --}}
    <div class="sig-section" style="justify-content: space-between;">
        <div class="sig-box">
            <div class="sig-label">
                {{ $payrollSlip->employee_signed_at ? $payrollSlip->employee_signed_at->format('d/m/Y') : '..............' }}
            </div>
            <div class="sig-space"></div>
            <div class="sig-name">{{ $payrollSlip->employee->name }}</div>
            <div class="sig-title">Karyawan</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">
                {{ $payrollSlip->signed_at
                    ? $payrollSlip->signed_at->format('d/m/Y')
                    : ($payrollSlip->payment_date ? $payrollSlip->payment_date->format('d/m/Y') : date('d/m/Y')) }}
            </div>
            <div class="sig-space"></div>
            <div class="sig-name">{{ $payrollSlip->signer->name ?? 'HRD / Management' }}</div>
            <div class="sig-title">{{ $payrollSlip->signer->title ?? $payrollSlip->company->name }}</div>
        </div>
    </div>

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
