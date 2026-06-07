@extends('layouts.app')
@section('title', 'Project Plan')
@section('page-title', 'Project Plan')

@section('content')

@php
$statusLabels = [
    'planning'  => ['Perencanaan', 'secondary'],
    'active'    => ['Berjalan',    'primary'],
    'on_hold'   => ['Ditunda',     'warning'],
    'completed' => ['Selesai',     'success'],
    'cancelled' => ['Dibatalkan',  'danger'],
];
@endphp

{{-- Filter bar --}}
<form method="GET" action="{{ route('project-plan.index') }}" class="row g-2 align-items-end mb-4">
    <div class="col-md-5">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Cari nama atau kode project..." value="{{ $q }}">
        </div>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Semua Status</option>
            @foreach($statuses as $s)
                <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $statusLabels[$s][0] }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
        <a href="{{ route('project-plan.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
    </div>
</form>

@if(session('success'))
    <div class="alert alert-success alert-dismissible py-2" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@forelse($projects as $project)
    @php [$statusLabel, $statusColor] = $project->statusBadge(); @endphp
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 py-2">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }}" style="font-size:.72rem">{{ $statusLabel }}</span>
                <div>
                    <a href="{{ route('project-plan.show', $project) }}" class="fw-semibold text-decoration-none">
                        {{ $project->name }}
                    </a>
                    <span class="text-muted ms-2" style="font-size:.78rem">{{ $project->code }}</span>
                </div>
                @if($project->client)
                    <span class="text-muted" style="font-size:.8rem"><i class="bi bi-building me-1"></i>{{ $project->client->name }}</span>
                @endif
            </div>
            <div class="d-flex align-items-center gap-3" style="font-size:.8rem">
                @if($project->start_date || $project->end_date)
                    <span class="text-muted">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ $project->start_date?->isoFormat('D MMM YY') ?? '—' }}
                        <span class="mx-1">→</span>
                        {{ $project->end_date?->isoFormat('D MMM YY') ?? 'Selesai' }}
                    </span>
                @endif
                @if($project->budget)
                    <span class="text-success fw-semibold">Rp {{ number_format($project->budget, 0, ',', '.') }}</span>
                @endif
                <a href="{{ route('project-plan.show', $project) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-people me-1"></i>Kelola Tim
                </a>
                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-eye me-1"></i>Detail Project
                </a>
            </div>
        </div>

        {{-- Timeline bar --}}
        @if($project->start_date && $project->end_date)
        @php
            $today    = now()->startOfDay();
            $start    = $project->start_date->startOfDay();
            $end      = $project->end_date->startOfDay();
            $total    = max($start->diffInDays($end), 1);
            $elapsed  = min(max($today->diffInDays($start, false) * -1, 0), $total);
            $progress = round(($elapsed / $total) * 100);
        @endphp
        <div class="px-3 py-1" style="font-size:.72rem">
            <div class="d-flex justify-content-between text-muted mb-1">
                <span>{{ $start->isoFormat('D MMM YYYY') }}</span>
                <span class="text-{{ $statusColor }}">{{ $progress }}% berjalan</span>
                <span>{{ $end->isoFormat('D MMM YYYY') }}</span>
            </div>
            <div class="progress" style="height:6px;border-radius:50rem">
                <div class="progress-bar bg-{{ $statusColor }}" style="width:{{ $progress }}%"></div>
            </div>
        </div>
        @endif

        {{-- Team members --}}
        @if($project->employees->isNotEmpty())
        <div class="card-body py-2 px-3">
            <div class="text-muted mb-2" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em"><i class="bi bi-people me-1"></i>Tim ({{ $project->employees->count() }})</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($project->employees as $emp)
                    <div class="d-flex align-items-center gap-2 border rounded px-2 py-1" style="font-size:.8rem">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:28px;height:28px;font-size:.7rem;font-weight:700;color:#1d4ed8">
                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-medium">{{ $emp->name }}</div>
                            @if($emp->pivot->role)
                                <div class="text-muted" style="font-size:.72rem">{{ $emp->pivot->role }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="card-body py-2 px-3">
            <span class="text-muted" style="font-size:.8rem"><i class="bi bi-person-x me-1"></i>Belum ada anggota tim. <a href="{{ route('project-plan.show', $project) }}">Tambah tim &rarr;</a></span>
        </div>
        @endif
    </div>
@empty
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-kanban fs-1 d-block mb-2 opacity-25"></i>
            Belum ada project. <a href="{{ route('projects.create') }}">Buat project baru</a> dari menu B2B › Project.
        </div>
    </div>
@endforelse

@endsection
