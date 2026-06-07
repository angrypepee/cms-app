@extends('layouts.app')
@section('title', $pageTitle ?? 'Kontrak Kerja')
@section('page-title', $pageTitle ?? 'Kontrak Kerja')

@section('content')
@php
    $hasFilters = request()->hasAny(['search', 'company_id', 'contract_status']);
@endphp

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100" style="border-left:4px solid #2563eb">
            <div class="card-body p-4">
                <div class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Total Karyawan Aktif</div>
                <div class="fw-bold" style="font-size:2.5rem;color:#1e293b;line-height:1">{{ $allActive }}</div>
                <div class="text-muted" style="font-size:.78rem">Data kontrak karyawan aktif</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Kontrak Aman</div>
                <div class="fw-bold" style="font-size:2.5rem;color:#16a34a;line-height:1">{{ max($allActive - $expiredCount - $expiringCount, 0) }}</div>
                <div class="text-muted" style="font-size:.78rem">Belum masuk masa peringatan</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Segera Berakhir</div>
                <div class="fw-bold" style="font-size:2.5rem;color:#d97706;line-height:1">{{ $expiringCount }}</div>
                <div class="text-muted" style="font-size:.78rem">Dalam 30 hari ke depan</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Kontrak Habis</div>
                <div class="fw-bold" style="font-size:2.5rem;color:#dc2626;line-height:1">{{ $expiredCount }}</div>
                <div class="text-muted" style="font-size:.78rem">Perlu ditindaklanjuti</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" action="{{ route('kontrak-kerja.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#64748b">CARI KARYAWAN</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nama karyawan…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#64748b">PERUSAHAAN</label>
                <select name="company_id" class="form-select form-select-sm">
                    <option value="">Semua Perusahaan</option>
                    @foreach($companies as $id => $name)
                        <option value="{{ $id }}" {{ request('company_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#64748b">STATUS</label>
                <select name="contract_status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('contract_status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="expiring" {{ request('contract_status') === 'expiring' ? 'selected' : '' }}>Segera Berakhir</option>
                    <option value="expired" {{ request('contract_status') === 'expired' ? 'selected' : '' }}>Kontrak Habis</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                @if($hasFilters)
                    <a href="{{ route('kontrak-kerja.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="card-title mb-0">
            <i class="bi bi-file-earmark-text me-2 text-primary"></i>Daftar Kontrak Kerja
        </span>
        <span class="text-muted" style="font-size:.82rem">Menampilkan {{ $employees->count() }} dari {{ $employees->total() }} data</span>
    </div>

    @if($employees->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-folder2-open fs-1 d-block mb-2 opacity-25"></i>
            Tidak ada data kontrak ditemukan.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:36px">#</th>
                        <th>Karyawan</th>
                        <th>Perusahaan</th>
                        <th>Periode Kontrak</th>
                        <th>Status</th>
                        <th class="text-end" style="min-width:160px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $index => $employee)
                        @php [$label, $color] = $employee->contractBadge(); @endphp
                        <tr>
                            <td class="text-muted" style="font-size:.78rem">{{ $employees->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('employees.show', $employee) }}" class="text-decoration-none">
                                    <div class="fw-semibold" style="font-size:.875rem;color:#1e293b">{{ $employee->name }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $employee->employee_id }}{{ $employee->position ? ' · '.$employee->position : '' }}</div>
                                </a>
                            </td>
                            <td class="text-muted" style="font-size:.82rem">{{ $employee->company->name ?? '-' }}</td>
                            <td style="font-size:.82rem">
                                @if($employee->contract_start)
                                    <div>{{ $employee->contract_start->isoFormat('D MMM YYYY') }}</div>
                                    @if($employee->contract_end)
                                        <div class="text-muted">s/d {{ $employee->contract_end->isoFormat('D MMM YYYY') }}</div>
                                    @else
                                        <div class="text-muted">s/d <em>Permanen</em></div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} badge-pill" style="font-size:.72rem">{{ $label }}</span>
                                @if($employee->contract_end && $employee->contractStatus() === 'expiring')
                                    <div class="text-warning" style="font-size:.7rem;margin-top:.15rem">
                                        <i class="bi bi-exclamation-triangle-fill"></i> {{ $employee->contract_end->diffForHumans() }}
                                    </div>
                                @elseif($employee->contractStatus() === 'expired')
                                    <div class="text-danger" style="font-size:.7rem;margin-top:.15rem">
                                        <i class="bi bi-x-circle-fill"></i> Habis {{ $employee->contract_end?->isoFormat('D MMM YYYY') }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('kontrak-kerja.show', $employee) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-folder2-open"></i> Kelola Dokumen
                                </a>
                                <a href="{{ route('contract-documents.create', ['employee_id' => $employee->id]) }}" class="btn btn-outline-secondary btn-sm ms-1">
                                    <i class="bi bi-plus-lg"></i> Kontrak Baru
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($employees->hasPages())
        <div class="card-footer bg-white border-0 pt-0">
            {{ $employees->links() }}
        </div>
    @endif
</div>
@endsection
