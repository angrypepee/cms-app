@extends('layouts.app')
@section('title', 'Detail Apresiasi — '.$appreciation->employee->name)
@section('page-title', 'Detail Uang Apresiasi')
@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible mb-4" role="alert">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $used      = $appreciation->usedAmount();
    $remaining = $appreciation->remainingAmount();
    $pct       = $appreciation->usagePercentage();
    $barClass  = $pct >= 90 ? 'bg-danger' : ($pct >= 60 ? 'bg-warning' : 'bg-success');
@endphp

<div class="row g-4">
    {{-- Left: summary + claims list --}}
    <div class="col-lg-8">
        {{-- Budget summary card --}}
        <div class="card mb-4">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-stars me-2 text-warning"></i>Ringkasan Anggaran {{ $appreciation->year }}</span>
                <form method="POST" action="{{ route('appreciation.destroy', $appreciation) }}"
                    onsubmit="return confirm('Hapus anggaran apresiasi ini beserta semua permohonannya?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-4">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase">Total Anggaran</div>
                        <div class="fw-bold fs-5">Rp {{ number_format($appreciation->total_amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase">Terpakai</div>
                        <div class="fw-bold fs-5 text-warning">Rp {{ number_format($used, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase">Sisa</div>
                        <div class="fw-bold fs-5 text-success">Rp {{ number_format($remaining, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="progress" style="height:10px">
                    <div class="progress-bar {{ $barClass }}" style="width:{{ $pct }}%" title="{{ $pct }}%"></div>
                </div>
                <div class="text-muted mt-1" style="font-size:.78rem">{{ $pct }}% anggaran telah digunakan</div>
                @if($appreciation->notes)
                    <div class="alert alert-light mt-3 mb-0" style="font-size:.85rem"><i class="bi bi-info-circle me-1"></i>{{ $appreciation->notes }}</div>
                @endif
            </div>
        </div>

        {{-- Claims list --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-file-earmark-text me-2"></i>Daftar Permohonan</span>
                @if($remaining > 0)
                <a href="{{ route('appreciation.claims.create', $appreciation) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Ajukan Permohonan
                </a>
                @endif
            </div>

            @if($appreciation->claims->isEmpty())
                <div class="card-body text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Belum ada permohonan.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Pengaju</th>
                                <th class="text-end">Nominal</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appreciation->claims as $claim)
                            <tr>
                                <td>{{ $claim->title }}</td>
                                <td class="text-muted" style="font-size:.82rem">{{ $claim->submitter->name }}</td>
                                <td class="text-end">Rp {{ number_format($claim->amount, 0, ',', '.') }}</td>
                                <td><span class="badge badge-pill {{ $claim->statusBadgeClass() }}">{{ $claim->statusLabel() }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('appreciation.claims.show', [$appreciation, $claim]) }}" class="btn btn-sm btn-outline-secondary">Lihat</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Right: employee info --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="text-muted mb-3" style="font-size:.78rem;text-transform:uppercase">Karyawan</h6>
                <div class="fw-semibold">{{ $appreciation->employee->name }}</div>
                <div class="text-muted" style="font-size:.83rem">{{ $appreciation->employee->employee_id }}</div>
                <div class="text-muted" style="font-size:.83rem">{{ $appreciation->employee->job_title ?? '-' }}</div>
                <a href="{{ route('employees.show', $appreciation->employee) }}" class="btn btn-sm btn-outline-secondary mt-2">
                    <i class="bi bi-person me-1"></i>Lihat Profil
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2" style="font-size:.78rem;text-transform:uppercase">Perusahaan</h6>
                <div class="fw-semibold">{{ $appreciation->company->name }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
