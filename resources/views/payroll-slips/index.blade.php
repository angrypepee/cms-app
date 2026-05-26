@extends('layouts.app')
@section('title', 'Slip Gaji')
@section('page-title', 'Slip Gaji')
@section('content')
{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('payroll-slips.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label form-label-sm fw-medium mb-1">Perusahaan</label>
                <select name="company_id" class="form-select form-select-sm">
                    <option value="">Semua Perusahaan</option>
                    @foreach($companies as $co)
                        <option value="{{ $co->id }}" {{ request('company_id') == $co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm fw-medium mb-1">Bulan</label>
                <select name="month" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm fw-medium mb-1">Tahun</label>
                <select name="year" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach(range(date('Y'), date('Y')-4) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm fw-medium mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('payroll-slips.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                <a href="{{ route('payroll-slips.create') }}" class="btn btn-success btn-sm ms-auto"><i class="bi bi-plus-lg me-1"></i>Buat Slip</a>
                <a href="{{ route('payroll-slips.bulk-create') }}" class="btn btn-primary btn-sm"><i class="bi bi-people-fill me-1"></i>Buat Massal</a>
            </div>
        </form>
    </div>
</div>
{{-- Table --}}
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Daftar Slip Gaji</span>
        <span class="badge bg-secondary">{{ $slips->total() }}</span>
    </div>
    @if($slips->isEmpty())
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 opacity-25"></i>Tidak ada slip gaji ditemukan.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>No. Slip</th>
                        <th>Karyawan</th>
                        <th>Perusahaan</th>
                        <th>Periode</th>
                        <th class="text-end">Take Home Pay</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($slips as $slip)
                    <tr>
                        <td><span class="font-monospace text-muted" style="font-size:.78rem">{{ $slip->slip_number }}</span></td>
                        <td class="fw-medium">{{ $slip->employee->name }}</td>
                        <td class="text-muted" style="font-size:.85rem">{{ $slip->company->name }}</td>
                        <td>{{ $slip->period_label }}</td>
                        <td class="text-end fw-semibold text-success">Rp {{ number_format($slip->take_home_pay, 0, ',', '.') }}</td>
                        <td><span class="badge badge-pill {{ $slip->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $slip->status === 'published' ? 'Published' : 'Draft' }}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('payroll-slips.show', $slip) }}" class="btn btn-sm btn-outline-secondary">Lihat</a>
                                <a href="{{ route('payroll-slips.edit', $slip) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('payroll-slips.destroy', $slip) }}" onsubmit="return confirm('Hapus slip gaji ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent">{{ $slips->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
