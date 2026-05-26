@extends('layouts.app')
@section('title', $bonus->title)
@section('page-title', 'Detail Bonus')
@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <span class="card-title">
                    <i class="bi {{ $bonus->typeIcon() }} me-2 text-primary"></i>{{ $bonus->title }}
                </span>
                <div class="d-flex gap-2">
                    @if(!$bonus->isPaid())
                        <a href="{{ route('bonuses.edit', $bonus) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
                        <form method="POST" action="{{ route('bonuses.pay', $bonus) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-success"
                                onclick="return confirm('Tandai bonus ini sebagai sudah dibayar?')">
                                <i class="bi bi-check-circle me-1"></i>Tandai Dibayar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Jenis Bonus</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-pill" style="background:var(--bs-{{ $bonus->typeBadgeColor() }}-subtle,#e7f5ff);color:var(--bs-{{ $bonus->typeBadgeColor() }});font-size:.8rem">
                            <i class="bi {{ $bonus->typeIcon() }} me-1"></i>{{ $bonus->typeLabel() }}
                        </span>
                    </dd>

                    <dt class="col-sm-4 text-muted">Status</dt>
                    <dd class="col-sm-8">
                        @if($bonus->isPaid())
                            <span class="badge badge-pill badge-approved">Dibayar</span>
                        @else
                            <span class="badge badge-pill badge-draft">Draft</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted">Nominal</dt>
                    <dd class="col-sm-8 fw-bold text-success fs-5">Rp {{ number_format($bonus->amount, 0, ',', '.') }}</dd>

                    <dt class="col-sm-4 text-muted">Periode</dt>
                    <dd class="col-sm-8">{{ $bonus->periodLabel() }}</dd>

                    <dt class="col-sm-4 text-muted">Tanggal Bayar</dt>
                    <dd class="col-sm-8">{{ $bonus->payment_date?->format('d M Y') ?? '-' }}</dd>

                    @if($bonus->notes)
                    <dt class="col-sm-4 text-muted">Catatan</dt>
                    <dd class="col-sm-8">{{ $bonus->notes }}</dd>
                    @endif

                    <dt class="col-sm-4 text-muted">Dibuat oleh</dt>
                    <dd class="col-sm-8">{{ $bonus->creator->name }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="text-muted mb-3" style="font-size:.78rem;text-transform:uppercase">Karyawan</h6>
                <div class="fw-semibold">{{ $bonus->employee->name }}</div>
                <div class="text-muted" style="font-size:.83rem">{{ $bonus->employee->employee_id }}</div>
                <div class="text-muted" style="font-size:.83rem">{{ $bonus->employee->job_title ?? '-' }}</div>
                <a href="{{ route('employees.show', $bonus->employee) }}" class="btn btn-sm btn-outline-secondary mt-2">
                    <i class="bi bi-person me-1"></i>Lihat Profil
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2" style="font-size:.78rem;text-transform:uppercase">Perusahaan</h6>
                <div class="fw-semibold">{{ $bonus->company->name }}</div>
            </div>
        </div>
        @if(!$bonus->isPaid())
        <div class="mt-3">
            <form method="POST" action="{{ route('bonuses.destroy', $bonus) }}"
                onsubmit="return confirm('Hapus bonus ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Hapus Bonus</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
