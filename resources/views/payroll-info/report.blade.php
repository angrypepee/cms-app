@extends('layouts.app')
@section('title', 'Laporan Pembayaran Payroll')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Laporan Pembayaran Payroll</h4>
        <small class="text-muted">Ringkasan slip gaji yang dibuat dari kesepakatan gaji tiap karyawan</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form method="GET" action="{{ route('payroll-info.report') }}" class="d-flex gap-2 align-items-center">
            <select name="period" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:180px">
                @foreach($availablePeriods as $p)
                    @php $val = $p->period_year . '-' . str_pad($p->period_month, 2, '0', STR_PAD_LEFT); @endphp
                    <option value="{{ $val }}"
                        data-month="{{ $p->period_month }}" data-year="{{ $p->period_year }}"
                        {{ $p->period_month == $periodMonth && $p->period_year == $periodYear ? 'selected' : '' }}>
                        {{ $monthsId[$p->period_month] }} {{ $p->period_year }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="period_month" id="rep_pm" value="{{ $periodMonth }}">
            <input type="hidden" name="period_year"  id="rep_py" value="{{ $periodYear }}">
        </form>
        <a href="{{ route('payroll-info.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3 text-center">
            <div class="col-md-3">
                <div class="text-muted small text-uppercase">Karyawan</div>
                <div class="fs-4 fw-semibold">{{ $totals['employees'] }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small text-uppercase">Total Pendapatan</div>
                <div class="fs-5 fw-semibold text-success">Rp {{ number_format($totals['income'], 0, ',', '.') }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small text-uppercase">Total Potongan</div>
                <div class="fs-5 fw-semibold text-danger">Rp {{ number_format($totals['deduction'], 0, ',', '.') }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small text-uppercase">Total Dibayarkan</div>
                <div class="fs-4 fw-bold text-primary">Rp {{ number_format($totals['take_home'], 0, ',', '.') }}</div>
            </div>
        </div>
        <hr class="my-3">
        <div class="d-flex justify-content-center gap-4 text-muted small flex-wrap">
            <span><i class="bi bi-check2-circle text-success me-1"></i>{{ $totals['signed_admin'] }} ditandatangani admin</span>
            <span><i class="bi bi-pen-fill text-primary me-1"></i>{{ $totals['signed_employee'] }} ditandatangani karyawan</span>
            <span><i class="bi bi-calendar3 me-1"></i>Periode {{ $monthsId[$periodMonth] }} {{ $periodYear }}</span>
        </div>
    </div>
</div>

@if($byCompany->isNotEmpty() && $byCompany->count() > 1)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white"><i class="bi bi-building me-2"></i>Ringkasan per Perusahaan</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Perusahaan</th>
                    <th class="text-end">Karyawan</th>
                    <th class="text-end">Pendapatan</th>
                    <th class="text-end">Potongan</th>
                    <th class="text-end">Total Dibayar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($byCompany as $name => $row)
                <tr>
                    <td>{{ $name }}</td>
                    <td class="text-end">{{ $row['count'] }}</td>
                    <td class="text-end text-success">Rp {{ number_format($row['income'], 0, ',', '.') }}</td>
                    <td class="text-end text-danger">Rp {{ number_format($row['deduction'], 0, ',', '.') }}</td>
                    <td class="text-end fw-semibold">Rp {{ number_format($row['take_home'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><i class="bi bi-list-ul me-2"></i>Detail Pembayaran per Karyawan</div>
    @if($slips->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
            Belum ada slip gaji untuk periode ini. Buat slip dengan menekan tombol <strong>Transfer</strong> di halaman Payroll Info.
        </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size:.88rem">
            <thead class="table-light">
                <tr>
                    <th>Slip #</th>
                    <th>Karyawan</th>
                    <th>Bank</th>
                    <th>No. Rekening</th>
                    <th class="text-end">Pendapatan</th>
                    <th class="text-end">Potongan</th>
                    <th class="text-end">Dibayar</th>
                    <th class="text-center">Status TTD</th>
                </tr>
            </thead>
            <tbody>
                @foreach($slips as $slip)
                <tr>
                    <td><a href="{{ route('payroll-slips.show', $slip) }}" class="text-decoration-none font-monospace" style="font-size:.78rem">{{ $slip->slip_number }}</a></td>
                    <td>
                        <div class="fw-medium">{{ $slip->employee?->name }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ $slip->employee?->employee_id }} · {{ $slip->employee?->position }}</div>
                    </td>
                    <td>{{ $slip->employee?->bank_name ?: '—' }}</td>
                    <td><span class="font-monospace" style="font-size:.82rem">{{ $slip->employee?->bank_account ?: '—' }}</span></td>
                    <td class="text-end text-success">Rp {{ number_format($slip->total_income, 0, ',', '.') }}</td>
                    <td class="text-end text-danger">Rp {{ number_format($slip->total_deduction, 0, ',', '.') }}</td>
                    <td class="text-end fw-semibold">Rp {{ number_format($slip->take_home_pay, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($slip->signed_at)
                            <span class="badge bg-success bg-opacity-10 text-success" title="Admin: {{ $slip->signed_at->format('d M Y H:i') }}"><i class="bi bi-check2"></i> A</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">—</span>
                        @endif
                        @if($slip->employee_signed_at)
                            <span class="badge bg-primary bg-opacity-10 text-primary" title="Karyawan: {{ $slip->employee_signed_at->format('d M Y H:i') }}"><i class="bi bi-pen-fill"></i> K</span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning">K?</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-semibold">
                <tr>
                    <td colspan="4" class="text-end">TOTAL</td>
                    <td class="text-end text-success">Rp {{ number_format($totals['income'], 0, ',', '.') }}</td>
                    <td class="text-end text-danger">Rp {{ number_format($totals['deduction'], 0, ',', '.') }}</td>
                    <td class="text-end text-primary">Rp {{ number_format($totals['take_home'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.querySelector('select[name="period"]');
    if (!sel) return;
    sel.addEventListener('change', function() {
        var opt = sel.options[sel.selectedIndex];
        document.getElementById('rep_pm').value = opt.dataset.month;
        document.getElementById('rep_py').value = opt.dataset.year;
    });
});
</script>
@endpush
@endsection
