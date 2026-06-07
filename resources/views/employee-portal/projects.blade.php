@extends('layouts.app')
@section('title', 'Project Saya')
@section('page-title', 'Project Saya')

@section('content')
@php
$projectStatusLabels = [
    'planning'  => ['Perencanaan', 'secondary'],
    'active'    => ['Berjalan',    'primary'],
    'on_hold'   => ['Ditunda',     'warning'],
    'completed' => ['Selesai',     'success'],
    'cancelled' => ['Dibatalkan',  'danger'],
];
@endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible py-2 mb-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($projects->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-kanban fs-1 d-block mb-2 opacity-25"></i>
            <div class="fw-semibold mb-1">Belum ada project yang ditugaskan</div>
            <div style="font-size:.85rem">Anda akan muncul di sini setelah ditambahkan ke project oleh admin.</div>
        </div>
    </div>
@else

{{-- Summary strip --}}
@php
    $cntActive    = $projects->where('status', 'active')->count();
    $cntCompleted = $projects->where('status', 'completed')->count();
    $cntPlanning  = $projects->where('status', 'planning')->count();
    $cntMyDone    = $projects->filter(fn($p) => ($p->my_pivot?->work_status ?? 'not_started') === 'completed')->count();
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:40px;height:40px;border-radius:.65rem;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#2563eb"><i class="bi bi-kanban-fill"></i></div>
                <div><div class="fw-bold" style="font-size:1.4rem;line-height:1;color:#1e293b">{{ $projects->count() }}</div><div class="text-muted" style="font-size:.74rem">Total Project</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:40px;height:40px;border-radius:.65rem;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#2563eb"><i class="bi bi-play-circle-fill"></i></div>
                <div><div class="fw-bold" style="font-size:1.4rem;line-height:1;color:#1e293b">{{ $cntActive }}</div><div class="text-muted" style="font-size:.74rem">Sedang Berjalan</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:40px;height:40px;border-radius:.65rem;background:#fefce8;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#ca8a04"><i class="bi bi-hourglass-split"></i></div>
                <div><div class="fw-bold" style="font-size:1.4rem;line-height:1;color:#1e293b">{{ $cntPlanning }}</div><div class="text-muted" style="font-size:.74rem">Perencanaan</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:40px;height:40px;border-radius:.65rem;background:#f0fdf4;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#16a34a"><i class="bi bi-check-circle-fill"></i></div>
                <div><div class="fw-bold" style="font-size:1.4rem;line-height:1;color:#1e293b">{{ $cntMyDone }}</div><div class="text-muted" style="font-size:.74rem">Saya Selesai</div></div>
            </div>
        </div>
    </div>
</div>

@foreach($projects as $project)
@php
    [$projStatusLabel, $projStatusColor] = $projectStatusLabels[$project->status] ?? [ucfirst($project->status), 'secondary'];
    $pivot = $project->my_pivot;
    $workStatus = $pivot?->work_status ?? 'not_started';
    [$wsLabel, $wsColor] = \App\Models\Pivots\ProjectMemberPivot::workStatusLabel($workStatus);
    $tabId = 'proj-' . $project->id;
    $canStart    = $workStatus === 'not_started' && in_array($project->status, ['active','planning']);
    $canComplete = $workStatus === 'in_progress';
    $canReset    = $workStatus === 'completed';
@endphp
<div class="card mb-4" id="card-{{ $tabId }}">

    {{-- ─ Header ─────────────────────────────────────────────── --}}
    <div style="background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 60%,#2563eb 100%);padding:1.1rem 1.5rem;color:#fff;border-radius:.5rem .5rem 0 0;position:relative;overflow:hidden">
        <div style="position:absolute;right:-30px;top:-30px;width:130px;height:130px;border-radius:50%;background:rgba(255,255,255,.07)"></div>
        <div class="d-flex align-items-start justify-content-between gap-3 position-relative" style="z-index:1">
            <div style="min-width:0">
                <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.14em;opacity:.6">{{ $project->code }}</div>
                <div style="font-size:1.05rem;font-weight:700;line-height:1.25;margin-top:.15rem">{{ $project->name }}</div>
                @if($project->client)
                    <div style="font-size:.75rem;opacity:.7;margin-top:.15rem">
                        <i class="bi bi-building me-1"></i>{{ $project->client->name }}
                        @if($project->company)&nbsp;&bull;&nbsp;{{ $project->company->name }}@endif
                    </div>
                @endif
                {{-- Status row --}}
                <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                    {{-- Project status --}}
                    <span style="display:inline-flex;align-items:center;gap:.25rem;padding:.2em .6em;border-radius:50rem;font-size:.62rem;font-weight:700;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25)">
                        {{ $projStatusLabel }}
                    </span>
                    {{-- My work status --}}
                    <span style="display:inline-flex;align-items:center;gap:.25rem;padding:.2em .6em;border-radius:50rem;font-size:.62rem;font-weight:700;
                        @if($wsColor==='primary') background:#1d4ed830;color:#93c5fd;border:1px solid #3b82f640
                        @elseif($wsColor==='success') background:#16a34a30;color:#86efac;border:1px solid #22c55e40
                        @else background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);border:1px solid rgba(255,255,255,.2) @endif">
                        <i class="bi bi-{{ $workStatus==='completed' ? 'check-circle-fill' : ($workStatus==='in_progress' ? 'activity' : 'circle') }} me-1"></i>
                        {{ $wsLabel }}
                    </span>
                    @if($pivot?->role)
                        <span style="display:inline-flex;align-items:center;gap:.25rem;padding:.2em .6em;border-radius:50rem;font-size:.62rem;font-weight:700;background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);border:1px solid rgba(255,255,255,.2)">
                            <i class="bi bi-person-badge me-1"></i>{{ $pivot->role }}
                        </span>
                    @endif
                </div>
            </div>
            <div style="flex-shrink:0;text-align:right">
                {{-- Status update buttons --}}
                @if($canStart)
                <form method="POST" action="{{ route('my.projects.work-status', $project) }}" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="work_status" value="in_progress">
                    <button type="submit" class="btn btn-sm" style="background:#fff;color:#1d4ed8;font-weight:600;font-size:.75rem;padding:.3rem .8rem;border:none">
                        <i class="bi bi-play-fill me-1"></i>Mulai Kerjakan
                    </button>
                </form>
                @elseif($canComplete)
                <form method="POST" action="{{ route('my.projects.work-status', $project) }}" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="work_status" value="completed">
                    <button type="submit" class="btn btn-sm" style="background:#16a34a;color:#fff;font-weight:600;font-size:.75rem;padding:.3rem .8rem;border:none"
                        onclick="return confirm('Tandai pekerjaan Anda di project ini sebagai selesai?')">
                        <i class="bi bi-check-circle me-1"></i>Tandai Selesai
                    </button>
                </form>
                @elseif($canReset)
                <form method="POST" action="{{ route('my.projects.work-status', $project) }}" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="work_status" value="in_progress">
                    <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;font-size:.72rem;padding:.25rem .6rem;border:1px solid rgba(255,255,255,.3)">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Buka Kembali
                    </button>
                </form>
                @endif
                {{-- Timeline progress --}}
                @if($project->start_date && $project->end_date)
                @php
                    $today2   = now()->startOfDay();
                    $start2   = $project->start_date->startOfDay();
                    $end2     = $project->end_date->startOfDay();
                    $total2   = max($start2->diffInDays($end2), 1);
                    $elapsed2 = min(max($today2->diffInDays($start2, false) * -1, 0), $total2);
                    $prog2    = round(($elapsed2 / $total2) * 100);
                @endphp
                <div style="margin-top:.6rem;min-width:130px">
                    <div class="d-flex justify-content-between" style="font-size:.6rem;opacity:.6;margin-bottom:.2rem">
                        <span>{{ $start2->isoFormat('D MMM YY') }}</span>
                        <span>{{ $prog2 }}%</span>
                        <span>{{ $end2->isoFormat('D MMM YY') }}</span>
                    </div>
                    <div style="height:4px;border-radius:50rem;background:rgba(255,255,255,.2);overflow:hidden">
                        <div style="height:100%;width:{{ $prog2 }}%;background:#fff;border-radius:50rem"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ─ Tab navigation ──────────────────────────────────────── --}}
    <ul class="nav nav-tabs px-3 pt-2 border-0" style="background:#f8fafc;border-bottom:1px solid #e2e8f0!important">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-info" type="button" style="font-size:.82rem">
                <i class="bi bi-info-circle me-1"></i>Info
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-team" type="button" style="font-size:.82rem">
                <i class="bi bi-people me-1"></i>Tim
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.65rem">{{ $project->employees_count }}</span>
            </button>
        </li>
        @if($project->links->isNotEmpty())
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-links" type="button" style="font-size:.82rem">
                <i class="bi bi-link-45deg me-1"></i>Tautan
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.65rem">{{ $project->links->count() }}</span>
            </button>
        </li>
        @endif
        @if($project->files->isNotEmpty())
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-files" type="button" style="font-size:.82rem">
                <i class="bi bi-folder2-open me-1"></i>File
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.65rem">{{ $project->files->count() }}</span>
            </button>
        </li>
        @endif
    </ul>

    {{-- ─ Tab content ─────────────────────────────────────────── --}}
    <div class="tab-content">

        {{-- Info --}}
        <div class="tab-pane fade show active p-4" id="{{ $tabId }}-info" style="font-size:.88rem">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Periode Project</div>
                        <div class="fw-semibold" style="font-size:.9rem">
                            {{ $project->start_date?->isoFormat('D MMM YYYY') ?? '—' }}
                            <span class="text-muted mx-1">→</span>
                            {{ $project->end_date?->isoFormat('D MMM YYYY') ?? 'Selesai' }}
                        </div>
                        @if($project->start_date && $project->end_date)
                        @php
                            $remaining = max(now()->startOfDay()->diffInDays($project->end_date->startOfDay(), false), 0);
                            $isOverdue = now()->startOfDay()->isAfter($project->end_date->startOfDay()) && $project->status !== 'completed';
                        @endphp
                        <div class="mt-2" style="font-size:.78rem">
                            @if($project->status === 'completed')
                                <span class="text-success"><i class="bi bi-check-circle me-1"></i>Project selesai</span>
                            @elseif($isOverdue)
                                <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Melewati target</span>
                            @else
                                <span class="text-muted"><i class="bi bi-hourglass me-1"></i>{{ $remaining }} hari tersisa</span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Status Pekerjaan Saya</div>
                        <span class="badge bg-{{ $wsColor }} bg-opacity-10 text-{{ $wsColor }}" style="font-size:.8rem;padding:.35em .75em">
                            <i class="bi bi-{{ $workStatus==='completed' ? 'check-circle-fill' : ($workStatus==='in_progress' ? 'activity' : 'circle') }} me-1"></i>
                            {{ $wsLabel }}
                        </span>
                        @if($pivot?->work_started_at)
                        <div class="mt-2 text-muted" style="font-size:.78rem">
                            <i class="bi bi-play me-1"></i>Mulai: {{ $pivot->work_started_at->isoFormat('D MMM YYYY, HH:mm') }}
                        </div>
                        @endif
                        @if($pivot?->work_completed_at)
                        <div class="mt-1 text-success" style="font-size:.78rem">
                            <i class="bi bi-check2 me-1"></i>Selesai: {{ $pivot->work_completed_at->isoFormat('D MMM YYYY, HH:mm') }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Partisipasi Saya</div>
                        <div><span class="text-muted">Peran:</span> <strong class="ms-1">{{ $pivot?->role ?? '—' }}</strong></div>
                        @if($pivot?->joined_at)
                        <div class="mt-1"><span class="text-muted">Bergabung:</span> <strong class="ms-1">{{ \Carbon\Carbon::parse($pivot->joined_at)->isoFormat('D MMM YYYY') }}</strong></div>
                        @endif
                        @if($pivot?->notes)
                        <div class="mt-2 text-muted" style="font-size:.8rem;border-top:1px solid #f1f5f9;padding-top:.5rem">
                            <i class="bi bi-chat-text me-1"></i>{{ $pivot->notes }}
                        </div>
                        @endif
                    </div>
                </div>

                @if($project->description)
                <div class="col-12">
                    <div class="border rounded p-3">
                        <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Deskripsi</div>
                        <div style="white-space:pre-line;line-height:1.75">{{ $project->description }}</div>
                    </div>
                </div>
                @endif

                @if($project->notes)
                <div class="col-12">
                    <div class="border rounded p-3 bg-light">
                        <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Catatan</div>
                        <div class="text-muted" style="white-space:pre-line;line-height:1.75">{{ $project->notes }}</div>
                    </div>
                </div>
                @endif

                {{-- Work history for this employee --}}
                @if(isset($project->my_history) && $project->my_history->isNotEmpty())
                <div class="col-12">
                    <div class="border rounded p-3">
                        <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">
                            <i class="bi bi-clock-history me-1"></i>History Pekerjaan Saya
                        </div>
                        @foreach($project->my_history as $h)
                        @php [$toLabel, $toColor] = \App\Models\Pivots\ProjectMemberPivot::workStatusLabel($h->to_status); @endphp
                        <div class="d-flex align-items-center gap-2 py-1 {{ !$loop->last ? 'border-bottom' : '' }}" style="font-size:.8rem">
                            <span class="badge bg-{{ $toColor }} bg-opacity-10 text-{{ $toColor }}" style="font-size:.7rem;white-space:nowrap">{{ $toLabel }}</span>
                            <span class="text-muted flex-grow-1">{{ $h->note }}</span>
                            <span class="text-muted" style="font-size:.72rem;white-space:nowrap">{{ $h->created_at->isoFormat('D MMM YYYY, HH:mm') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Tim --}}
        <div class="tab-pane fade p-4" id="{{ $tabId }}-team">
            @if($project->employees->isEmpty())
                <div class="text-center py-3 text-muted" style="font-size:.85rem">Belum ada anggota tim.</div>
            @else
            <div class="row g-2">
                @foreach($project->employees as $member)
                @php
                    $initials = collect(explode(' ', $member->name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                    $colors   = ['#2563eb','#7c3aed','#0891b2','#16a34a','#d97706','#dc2626','#0d9488'];
                    $mbg      = $colors[crc32($member->employee_id ?? $member->id) % count($colors)];
                    $isMe     = $member->id === $employee->id;
                    $mws      = $member->pivot->work_status ?? 'not_started';
                    [$mwsLabel, $mwsColor] = \App\Models\Pivots\ProjectMemberPivot::workStatusLabel($mws);
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="border rounded p-2 h-100 {{ $isMe ? 'border-primary' : '' }}"
                        style="{{ $isMe ? 'background:#eff6ff' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:40px;height:40px;border-radius:50%;background:{{ $mbg }};display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:#fff;flex-shrink:0;">
                                {{ $initials }}
                            </div>
                            <div style="font-size:.82rem;min-width:0;flex:1">
                                <div class="fw-semibold d-flex align-items-center gap-1 flex-wrap">
                                    {{ $member->name }}
                                    @if($isMe)<span class="badge bg-primary" style="font-size:.6rem">Saya</span>@endif
                                </div>
                                <div class="text-muted" style="font-size:.74rem">{{ $member->position ?? '—' }}</div>
                                @if($member->pivot->role)
                                    <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.68rem">{{ $member->pivot->role }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2 pt-2" style="border-top:1px solid #f1f5f9">
                            <span class="badge bg-{{ $mwsColor }} bg-opacity-10 text-{{ $mwsColor }}" style="font-size:.7rem">
                                <i class="bi bi-{{ $mws==='completed' ? 'check-circle-fill' : ($mws==='in_progress' ? 'activity' : 'circle') }} me-1"></i>
                                {{ $mwsLabel }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Tautan --}}
        @if($project->links->isNotEmpty())
        <div class="tab-pane fade p-4" id="{{ $tabId }}-links">
            <div class="row g-2">
                @foreach($project->links as $link)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ $link->url }}" target="_blank"
                        class="d-flex align-items-center gap-3 border rounded p-3 text-decoration-none h-100"
                        style="color:inherit"
                        onmouseover="this.style.boxShadow='0 2px 12px rgba(0,0,0,.08)'"
                        onmouseout="this.style.boxShadow=''">
                        <div style="width:40px;height:40px;border-radius:.6rem;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:{{ $link->typeColor() }};background:{{ $link->typeColor() }}15">
                            <i class="{{ $link->typeIcon() }}"></i>
                        </div>
                        <div style="font-size:.85rem;min-width:0;flex:1">
                            <div class="fw-semibold">{{ $link->label }}</div>
                            <div class="text-muted" style="font-size:.74rem">{{ $link->typeLabel() }}</div>
                        </div>
                        <i class="bi bi-box-arrow-up-right text-muted" style="font-size:.8rem;flex-shrink:0"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- File --}}
        @if($project->files->isNotEmpty())
        <div class="tab-pane fade" id="{{ $tabId }}-files">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.85rem">
                    <thead class="table-light">
                        <tr>
                            <th>File</th><th>Ukuran</th><th>Diunggah oleh</th><th>Tanggal</th><th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->files as $pf)
                        <tr>
                            <td>
                                <i class="bi bi-file-earmark{{ $pf->isPdf() ? '-pdf text-danger' : ($pf->isImage() ? '-image text-info' : '') }} me-2"></i>
                                <span class="fw-medium">{{ $pf->label }}</span>
                                <div class="text-muted" style="font-size:.74rem">{{ $pf->original_name }}</div>
                            </td>
                            <td class="text-muted">{{ $pf->fileSizeFormatted() }}</td>
                            <td class="text-muted">{{ $pf->uploader?->name ?? '—' }}</td>
                            <td class="text-muted">{{ $pf->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($pf->isViewable())
                                        <a href="{{ route('projects.files.show', [$project, $pf]) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                    @endif
                                    <a href="{{ route('projects.files.show', [$project, $pf]) }}?download=1" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>{{-- tab-content --}}
</div>{{-- card --}}
@endforeach

@endif
@endsection

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

@if(session('success'))
    <div class="alert alert-success alert-dismissible py-2 mb-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($projects->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-kanban fs-1 d-block mb-2 opacity-25"></i>
            <div class="fw-semibold mb-1">Belum ada project yang ditugaskan</div>
            <div style="font-size:.85rem">Anda akan muncul di sini setelah ditambahkan ke project oleh admin.</div>
        </div>
    </div>
@else

{{-- Summary strip --}}
@php
    $active    = $projects->where('status', 'active')->count();
    $completed = $projects->where('status', 'completed')->count();
    $planning  = $projects->where('status', 'planning')->count();
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:40px;height:40px;border-radius:.65rem;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#2563eb">
                    <i class="bi bi-kanban-fill"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:1.4rem;line-height:1;color:#1e293b">{{ $projects->count() }}</div>
                    <div class="text-muted" style="font-size:.74rem">Total Project</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:40px;height:40px;border-radius:.65rem;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#2563eb">
                    <i class="bi bi-play-circle-fill"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:1.4rem;line-height:1;color:#1e293b">{{ $active }}</div>
                    <div class="text-muted" style="font-size:.74rem">Sedang Berjalan</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:40px;height:40px;border-radius:.65rem;background:#fefce8;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#ca8a04">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:1.4rem;line-height:1;color:#1e293b">{{ $planning }}</div>
                    <div class="text-muted" style="font-size:.74rem">Perencanaan</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:40px;height:40px;border-radius:.65rem;background:#f0fdf4;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#16a34a">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:1.4rem;line-height:1;color:#1e293b">{{ $completed }}</div>
                    <div class="text-muted" style="font-size:.74rem">Selesai</div>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($projects as $project)
@php
    [$statusLabel, $statusColor] = $statusLabels[$project->status] ?? [ucfirst($project->status), 'secondary'];
    $pivot = $project->my_pivot;
    $tabId = 'proj-' . $project->id;
@endphp
<div class="card mb-4">

    {{-- Header gradient --}}
    <div style="background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 60%,#2563eb 100%);padding:1.1rem 1.5rem;color:#fff;border-radius:.5rem .5rem 0 0;position:relative;overflow:hidden">
        <div style="position:absolute;right:-30px;top:-30px;width:130px;height:130px;border-radius:50%;background:rgba(255,255,255,.07)"></div>
        <div style="position:absolute;right:60px;bottom:-40px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.05)"></div>
        <div class="d-flex align-items-start justify-content-between gap-3 position-relative" style="z-index:1">
            <div>
                <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.14em;opacity:.6">{{ $project->code }}</div>
                <div style="font-size:1.1rem;font-weight:700;line-height:1.2;margin-top:.15rem">{{ $project->name }}</div>
                @if($project->client)
                    <div style="font-size:.75rem;opacity:.7;margin-top:.15rem">
                        <i class="bi bi-building me-1"></i>{{ $project->client->name }}
                        @if($project->company)&nbsp;&bull;&nbsp;{{ $project->company->name }}@endif
                    </div>
                @endif
                @if($pivot?->role)
                    <div style="margin-top:.4rem">
                        <span style="display:inline-flex;align-items:center;gap:.3rem;padding:.2em .65em;border-radius:50rem;font-size:.65rem;font-weight:700;background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.3)">
                            <i class="bi bi-person-badge"></i>{{ $pivot->role }}
                        </span>
                    </div>
                @endif
            </div>
            <div class="text-end" style="flex-shrink:0">
                <span style="display:inline-flex;align-items:center;gap:.25rem;padding:.25em .7em;border-radius:50rem;font-size:.65rem;font-weight:700;background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.3)">
                    {{ $statusLabel }}
                </span>
                @if($project->start_date && $project->end_date)
                @php
                    $today2   = now()->startOfDay();
                    $start2   = $project->start_date->startOfDay();
                    $end2     = $project->end_date->startOfDay();
                    $total2   = max($start2->diffInDays($end2), 1);
                    $elapsed2 = min(max($today2->diffInDays($start2, false) * -1, 0), $total2);
                    $prog2    = round(($elapsed2 / $total2) * 100);
                @endphp
                <div style="margin-top:.5rem;min-width:130px">
                    <div class="d-flex justify-content-between" style="font-size:.62rem;opacity:.65;margin-bottom:.2rem">
                        <span>{{ $start2->isoFormat('D MMM YY') }}</span>
                        <span>{{ $prog2 }}%</span>
                        <span>{{ $end2->isoFormat('D MMM YY') }}</span>
                    </div>
                    <div style="height:4px;border-radius:50rem;background:rgba(255,255,255,.2);overflow:hidden">
                        <div style="height:100%;width:{{ $prog2 }}%;background:#fff;border-radius:50rem"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tab navigation --}}
    <ul class="nav nav-tabs px-3 pt-2 border-0" style="background:#f8fafc;border-bottom:1px solid #e2e8f0!important" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-info" type="button" style="font-size:.82rem">
                <i class="bi bi-info-circle me-1"></i>Info
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-team" type="button" style="font-size:.82rem">
                <i class="bi bi-people me-1"></i>Tim
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.65rem">{{ $project->employees_count }}</span>
            </button>
        </li>
        @if($project->links->isNotEmpty())
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-links" type="button" style="font-size:.82rem">
                <i class="bi bi-link-45deg me-1"></i>Tautan
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.65rem">{{ $project->links->count() }}</span>
            </button>
        </li>
        @endif
        @if($project->files->isNotEmpty())
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-files" type="button" style="font-size:.82rem">
                <i class="bi bi-folder2-open me-1"></i>File
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.65rem">{{ $project->files->count() }}</span>
            </button>
        </li>
        @endif
    </ul>

    {{-- Tab content --}}
    <div class="tab-content">

        {{-- Tab: Info --}}
        <div class="tab-pane fade show active p-4" id="{{ $tabId }}-info" style="font-size:.88rem">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Periode Project</div>
                        <div class="fw-semibold">
                            {{ $project->start_date?->isoFormat('D MMMM YYYY') ?? '—' }}
                            <span class="text-muted mx-1">→</span>
                            {{ $project->end_date?->isoFormat('D MMMM YYYY') ?? 'Selesai' }}
                        </div>
                        @if($project->start_date && $project->end_date)
                        @php
                            $remaining = max(now()->startOfDay()->diffInDays($project->end_date->startOfDay(), false), 0);
                            $isOverdue = now()->startOfDay()->isAfter($project->end_date->startOfDay()) && $project->status !== 'completed';
                        @endphp
                        <div class="mt-2" style="font-size:.78rem">
                            @if($project->status === 'completed')
                                <span class="text-success"><i class="bi bi-check-circle me-1"></i>Project selesai</span>
                            @elseif($isOverdue)
                                <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Melebihi target selesai</span>
                            @else
                                <span class="text-muted"><i class="bi bi-hourglass me-1"></i>{{ $remaining }} hari tersisa</span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Partisipasi Saya</div>
                        <div><span class="text-muted">Peran:</span> <strong class="ms-1">{{ $pivot?->role ?? '—' }}</strong></div>
                        @if($pivot?->joined_at)
                        <div class="mt-1"><span class="text-muted">Bergabung:</span> <strong class="ms-1">{{ \Carbon\Carbon::parse($pivot->joined_at)->isoFormat('D MMMM YYYY') }}</strong></div>
                        @endif
                        @if($pivot?->notes)
                        <div class="mt-2 text-muted" style="font-size:.8rem;border-top:1px solid #f1f5f9;padding-top:.5rem">
                            <i class="bi bi-chat-text me-1"></i>{{ $pivot->notes }}
                        </div>
                        @endif
                    </div>
                </div>

                @if($project->description)
                <div class="col-12">
                    <div class="border rounded p-3">
                        <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Deskripsi Project</div>
                        <div style="white-space:pre-line;line-height:1.75">{{ $project->description }}</div>
                    </div>
                </div>
                @endif

                @if($project->notes)
                <div class="col-12">
                    <div class="border rounded p-3 bg-light">
                        <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Catatan</div>
                        <div class="text-muted" style="white-space:pre-line;line-height:1.75">{{ $project->notes }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Tab: Tim --}}
        <div class="tab-pane fade p-4" id="{{ $tabId }}-team">
            @if($project->employees->isEmpty())
                <div class="text-center py-3 text-muted" style="font-size:.85rem">Belum ada anggota tim.</div>
            @else
            <div class="row g-2">
                @foreach($project->employees as $member)
                @php
                    $initials = collect(explode(' ', $member->name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                    $colors   = ['#2563eb','#7c3aed','#0891b2','#16a34a','#d97706','#dc2626','#0d9488'];
                    $mbg      = $colors[crc32($member->employee_id ?? $member->id) % count($colors)];
                    $isMe     = $member->id === $employee->id;
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="d-flex align-items-center gap-2 border rounded p-2 h-100 {{ $isMe ? 'border-primary' : '' }}"
                        style="{{ $isMe ? 'background:#eff6ff' : '' }}">
                        <div style="width:40px;height:40px;border-radius:50%;background:{{ $mbg }};display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:#fff;flex-shrink:0;{{ $isMe ? 'border:2px solid #2563eb' : '' }}">
                            {{ $initials }}
                        </div>
                        <div style="font-size:.82rem;min-width:0">
                            <div class="fw-semibold d-flex align-items-center gap-1 flex-wrap">
                                {{ $member->name }}
                                @if($isMe)<span class="badge bg-primary" style="font-size:.6rem">Saya</span>@endif
                            </div>
                            <div class="text-muted" style="font-size:.74rem">{{ $member->position ?? '—' }}</div>
                            @if($member->pivot->role)
                                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.68rem">{{ $member->pivot->role }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Tab: Tautan --}}
        @if($project->links->isNotEmpty())
        <div class="tab-pane fade p-4" id="{{ $tabId }}-links">
            <div class="row g-2">
                @foreach($project->links as $link)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ $link->url }}" target="_blank"
                        class="d-flex align-items-center gap-3 border rounded p-3 text-decoration-none h-100"
                        style="color:inherit"
                        onmouseover="this.style.boxShadow='0 2px 12px rgba(0,0,0,.08)'"
                        onmouseout="this.style.boxShadow=''">
                        <div style="width:40px;height:40px;border-radius:.6rem;background:{{ $link->typeColor() }}15;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:{{ $link->typeColor() }}">
                            <i class="{{ $link->typeIcon() }}"></i>
                        </div>
                        <div style="font-size:.85rem;min-width:0;flex:1">
                            <div class="fw-semibold">{{ $link->label }}</div>
                            <div class="text-muted" style="font-size:.74rem">{{ $link->typeLabel() }}</div>
                        </div>
                        <i class="bi bi-box-arrow-up-right text-muted" style="font-size:.8rem;flex-shrink:0"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Tab: File --}}
        @if($project->files->isNotEmpty())
        <div class="tab-pane fade" id="{{ $tabId }}-files">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.85rem">
                    <thead class="table-light">
                        <tr>
                            <th>File</th>
                            <th>Ukuran</th>
                            <th>Diunggah oleh</th>
                            <th>Tanggal</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->files as $pf)
                        <tr>
                            <td>
                                <i class="bi bi-file-earmark{{ $pf->isPdf() ? '-pdf text-danger' : ($pf->isImage() ? '-image text-info' : '') }} me-2"></i>
                                <span class="fw-medium">{{ $pf->label }}</span>
                                <div class="text-muted" style="font-size:.74rem">{{ $pf->original_name }}</div>
                            </td>
                            <td class="text-muted">{{ $pf->fileSizeFormatted() }}</td>
                            <td class="text-muted">{{ $pf->uploader?->name ?? '—' }}</td>
                            <td class="text-muted">{{ $pf->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($pf->isViewable())
                                        <a href="{{ route('projects.files.show', [$project, $pf]) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                    @endif
                                    <a href="{{ route('projects.files.show', [$project, $pf]) }}?download=1" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
        @endif

    </div>{{-- tab-content --}}
</div>{{-- card --}}
@endforeach

@endif
@endsection
