@extends('layouts.app')
@section('title', 'Bonus Karyawan')
@section('page-title', 'Bonus Karyawan')
@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible mb-4" role="alert">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-gift me-2 text-primary"></i>Daftar Bonus</span>
        <a href="{{ route('bonuses.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Bonus</a>
    </div>

    {{-- Filters --}}
    <div class="card-body border-bottom bg-light py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label" style="font-size:.78rem">Jenis</label>
                <select name="bonus_type" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="thr"     {{ request('bonus_type') === 'thr'     ? 'selected' : '' }}>Bonus Tahunan (THR)</option>
                    <option value="project" {{ request('bonus_type') === 'project' ? 'selected' : '' }}>Bonus Project</option>
                </select>
            </div>
            <div class="col-md-3">
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
                <label class="form-label" style="font-size:.78rem">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="paid"  {{ request('status') === 'paid'  ? 'selected' : '' }}>Dibayar</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
            @if(request()->hasAny(['bonus_type','company_id','year','status']))
            <div class="col-md-1">
                <a href="{{ route('bonuses.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
            </div>
            @endif
        </form>
    </div>

    @if($bonuses->isEmpty())
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-gift fs-1 d-block mb-2 opacity-25"></i>Belum ada data bonus.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Karyawan</th>
                        <th>Jenis</th>
                        <th>Periode</th>
                        <th class="text-end">Nominal</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bonuses as $bonus)
                    <tr>
                        <td>
                            <a href="{{ route('bonuses.show', $bonus) }}" class="fw-medium text-decoration-none text-dark">{{ $bonus->title }}</a>
                        </td>
                        <td>
                            <div style="font-size:.85rem">{{ $bonus->employee->name }}</div>
                            <div class="text-muted" style="font-size:.75rem">{{ $bonus->company->name }}</div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $bonus->typeBadgeColor() }} bg-opacity-15 text-{{ $bonus->typeBadgeColor() }} badge-pill" style="font-size:.72rem">
                                <i class="bi {{ $bonus->typeIcon() }} me-1"></i>{{ $bonus->typeLabel() }}
                            </span>
                        </td>
                        <td class="text-muted" style="font-size:.82rem">{{ $bonus->periodLabel() }}</td>
                        <td class="text-end fw-semibold text-success">Rp {{ number_format($bonus->amount, 0, ',', '.') }}</td>
                        <td>
                            @if($bonus->isPaid())
                                <span class="badge badge-pill badge-approved">Dibayar</span>
                            @else
                                <span class="badge badge-pill badge-draft">Draft</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('bonuses.show', $bonus) }}" class="btn btn-sm btn-outline-secondary">Lihat</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-body pt-2">
            {{ $bonuses->links() }}
        </div>
    @endif
</div>
@endsection
