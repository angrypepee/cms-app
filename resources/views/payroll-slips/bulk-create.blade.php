@extends('layouts.app')
@section('title', 'Buat Slip Gaji Massal')
@section('page-title', 'Buat Slip Gaji Massal')
@section('content')
@php
    $monthNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                   7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
@endphp

{{-- Period & company picker (GET — reloads page with employee list) --}}
<form method="GET" action="{{ route('payroll-slips.bulk-create') }}" class="card mb-4">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-calendar3 me-2 text-primary"></i>Pilih Periode &amp; Perusahaan</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-medium">Perusahaan <span class="text-danger">*</span></label>
                <select name="company_id" class="form-select" required>
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach($companies as $co)
                        <option value="{{ $co->id }}" {{ $companyId == $co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-medium">Bulan <span class="text-danger">*</span></label>
                <select name="month" class="form-select" required>
                    @foreach($monthNames as $n => $label)
                        <option value="{{ $n }}" {{ $month == $n ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-medium">Tahun <span class="text-danger">*</span></label>
                <select name="year" class="form-select" required>
                    @foreach(range((int) date('Y') + 1, (int) date('Y') - 4) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-arrow-clockwise me-1"></i>Muat Karyawan</button>
            </div>
        </div>
    </div>
</form>

@if($companyId && $employees->count() === 0)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Tidak ada karyawan aktif untuk perusahaan ini.
    </div>
@elseif($employees->count() > 0)
<form method="POST" action="{{ route('payroll-slips.bulk-store') }}" id="bulkForm">
    @csrf
    <input type="hidden" name="company_id"   value="{{ $companyId }}">
    <input type="hidden" name="period_month" value="{{ $month }}">
    <input type="hidden" name="period_year"  value="{{ $year }}">

    {{-- Optional shared dates --}}
    <div class="card mb-4">
        <div class="card-header">
            <span class="card-title"><i class="bi bi-calendar-check me-2 text-primary"></i>Tanggal (Opsional)</span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-medium">Tanggal Pembayaran</label>
                    <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Tanggal Penerbitan</label>
                    <input type="date" name="released_at" class="form-control" value="{{ old('released_at') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Cutoff Mulai</label>
                    <input type="date" name="cutoff_start" class="form-control" value="{{ old('cutoff_start') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Cutoff Selesai</label>
                    <input type="date" name="cutoff_end" class="form-control" value="{{ old('cutoff_end') }}">
                </div>
            </div>
            <div class="text-muted mt-3" style="font-size:.85rem">
                <i class="bi bi-info-circle me-1"></i>Tanggal di sini akan diterapkan ke semua slip yang dibuat.
            </div>
        </div>
    </div>

    {{-- Employee list --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <span class="card-title">
                <i class="bi bi-people me-2 text-primary"></i>
                Karyawan Aktif &mdash; {{ $monthNames[$month] }} {{ $year }}
            </span>
            <div class="ms-auto d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(true)">Pilih Semua</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(false)">Hapus Semua</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px"><input type="checkbox" class="form-check-input" id="checkAll" checked></th>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th class="text-end">Gaji Pokok</th>
                            <th class="text-end">THP Estimasi</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $emp)
                            @php
                                $totals  = $emp->agreementTotals();
                                $exists  = $existingEmployeeIds->contains($emp->id);
                                $noSalary = (float) $emp->base_salary <= 0 && empty($emp->salary_components);
                            @endphp
                            <tr class="{{ $exists ? 'table-warning' : ($noSalary ? 'text-muted' : '') }}">
                                <td>
                                    <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}"
                                           class="form-check-input emp-check"
                                           {{ ($exists || $noSalary) ? '' : 'checked' }}
                                           {{ $exists ? 'disabled' : '' }}>
                                </td>
                                <td class="font-monospace small">{{ $emp->employee_id }}</td>
                                <td>{{ $emp->name }}</td>
                                <td>{{ $emp->position ?? '-' }}</td>
                                <td class="text-end">Rp {{ number_format((float) $emp->base_salary, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold text-primary">Rp {{ number_format($totals['take_home'], 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($exists)
                                        <span class="badge bg-warning text-dark">Sudah Ada</span>
                                    @elseif($noSalary)
                                        <span class="badge bg-secondary">Belum Ada Gaji</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success">Siap</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                Slip dibuat otomatis dari <strong>Gaji Pokok</strong> + <strong>Komponen Gaji</strong> yang sudah disimpan pada master karyawan.
                Karyawan yang sudah memiliki slip pada periode ini akan dilewati.
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
                    <i class="bi bi-floppy me-1"></i>Buat sebagai Draft
                </button>
                <button type="submit" name="action" value="publish" class="btn btn-primary"
                        onclick="return confirm('Buat & publikasikan slip gaji untuk semua karyawan terpilih?')">
                    <i class="bi bi-send me-1"></i>Buat &amp; Publish
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function toggleAll(state) {
    document.querySelectorAll('.emp-check:not(:disabled)').forEach(cb => cb.checked = state);
    document.getElementById('checkAll').checked = state;
}
document.getElementById('checkAll').addEventListener('change', e => toggleAll(e.target.checked));
document.getElementById('bulkForm').addEventListener('submit', e => {
    const n = document.querySelectorAll('.emp-check:checked').length;
    if (n === 0) { e.preventDefault(); alert('Pilih minimal 1 karyawan.'); }
});
</script>
@endpush
@endif
@endsection
