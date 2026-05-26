@extends('layouts.app')
@section('title', 'Uang Apresiasi')
@section('page-title', 'Uang Apresiasi Perusahaan')
@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible mb-4" role="alert">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-stars me-2 text-warning"></i>Anggaran Apresiasi</span>
        <a href="{{ route('appreciation.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Anggaran</a>
    </div>

    <div class="card-body border-bottom bg-light py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label" style="font-size:.78rem">Perusahaan</label>
                <select name="company_id" class="form-select form-select-sm">
                    <option value="">Semua Perusahaan</option>
                    @foreach($companies as $co)
                        <option value="{{ $co->id }}" {{ request('company_id') == $co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" style="font-size:.78rem">Tahun</label>
                <select name="year" class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
            @if(request()->hasAny(['company_id','year']))
            <div class="col-md-1">
                <a href="{{ route('appreciation.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
            </div>
            @endif
        </form>
    </div>

    @if($budgets->isEmpty())
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-stars fs-1 d-block mb-2 opacity-25"></i>Belum ada anggaran apresiasi.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Perusahaan</th>
                        <th>Tahun</th>
                        <th class="text-end">Total Anggaran</th>
                        <th class="text-end">Terpakai</th>
                        <th>Progres</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budgets as $budget)
                    @php
                        $used    = $budget->usedAmount();
                        $pct     = $budget->usagePercentage();
                        $barClass = $pct >= 90 ? 'bg-danger' : ($pct >= 60 ? 'bg-warning' : 'bg-success');
                    @endphp
                    <tr>
                        <td class="fw-medium">{{ $budget->employee->name }}</td>
                        <td class="text-muted" style="font-size:.85rem">{{ $budget->company->name }}</td>
                        <td>{{ $budget->year }}</td>
                        <td class="text-end">Rp {{ number_format($budget->total_amount, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($used, 0, ',', '.') }}</td>
                        <td style="min-width:120px">
                            <div class="progress" style="height:6px">
                                <div class="progress-bar {{ $barClass }}" style="width:{{ $pct }}%"></div>
                            </div>
                            <div class="text-muted" style="font-size:.72rem">{{ $pct }}% terpakai</div>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('appreciation.show', $budget) }}" class="btn btn-sm btn-outline-secondary">Lihat</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-body pt-2">
            {{ $budgets->links() }}
        </div>
    @endif
</div>
@endsection
