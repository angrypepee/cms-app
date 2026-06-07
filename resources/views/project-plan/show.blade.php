@extends('layouts.app')
@section('title', 'Project Plan — ' . $project->name)
@section('page-title', 'Project Plan')

@section('content')
@php [$statusLabel, $statusColor] = $project->statusBadge(); @endphp

{{-- Back + action --}}
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <a href="{{ route('project-plan.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye me-1"></i>Detail Project</a>
    <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit Project</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible py-2 mb-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- Left: Project info --}}
    <div class="col-lg-4">
        <div class="card">
            {{-- Header --}}
            <div style="background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 60%,#2563eb 100%);padding:1.5rem;color:#fff;border-radius:.5rem .5rem 0 0;position:relative;overflow:hidden">
                <div style="position:absolute;right:-30px;top:-30px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.07)"></div>
                <div class="position-relative" style="z-index:1">
                    <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.14em;opacity:.65">Project Plan</div>
                    <div style="font-size:1.05rem;font-weight:700;line-height:1.3;margin-top:.2rem">{{ $project->name }}</div>
                    <div style="font-size:.72rem;opacity:.7;margin-top:.15rem">{{ $project->code }}</div>
                    <span style="display:inline-flex;align-items:center;gap:.3rem;padding:.22em .65em;border-radius:50rem;font-size:.65rem;font-weight:700;letter-spacing:.05em;margin-top:.5rem;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3)">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @if($project->client)
                    <li class="list-group-item px-4 py-3">
                        <div class="text-muted" style="font-size:.75rem">Klien</div>
                        <div class="fw-semibold">{{ $project->client->name }}</div>
                    </li>
                    @endif
                    @if($project->company)
                    <li class="list-group-item px-4 py-3">
                        <div class="text-muted" style="font-size:.75rem">Perusahaan</div>
                        <div class="fw-semibold">{{ $project->company->name }}</div>
                    </li>
                    @endif
                    @if($project->start_date || $project->end_date)
                    <li class="list-group-item px-4 py-3">
                        <div class="text-muted" style="font-size:.75rem">Periode</div>
                        <div class="fw-semibold">
                            {{ $project->start_date?->isoFormat('D MMM YYYY') ?? '—' }}
                            <span class="text-muted mx-1">→</span>
                            {{ $project->end_date?->isoFormat('D MMM YYYY') ?? 'Selesai' }}
                        </div>
                        @if($project->start_date && $project->end_date)
                        @php
                            $today    = now()->startOfDay();
                            $start    = $project->start_date->startOfDay();
                            $end      = $project->end_date->startOfDay();
                            $total    = max($start->diffInDays($end), 1);
                            $elapsed  = min(max($today->diffInDays($start, false) * -1, 0), $total);
                            $progress = round(($elapsed / $total) * 100);
                        @endphp
                        <div class="progress mt-2" style="height:5px;border-radius:50rem">
                            <div class="progress-bar bg-{{ $statusColor }}" style="width:{{ $progress }}%"></div>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.72rem">{{ $progress }}% berjalan</div>
                        @endif
                    </li>
                    @endif
                    @if($project->budget)
                    <li class="list-group-item px-4 py-3">
                        <div class="text-muted" style="font-size:.75rem">Budget</div>
                        <div class="fw-semibold text-success">Rp {{ number_format($project->budget, 0, ',', '.') }}</div>
                    </li>
                    @endif
                    @if($project->description)
                    <li class="list-group-item px-4 py-3">
                        <div class="text-muted" style="font-size:.75rem">Deskripsi</div>
                        <div style="font-size:.85rem">{{ $project->description }}</div>
                    </li>
                    @endif
                </ul>
            </div>

            {{-- Stats --}}
            <div class="card-footer bg-transparent">
                <div class="row g-0 text-center">
                    <div class="col border-end">
                        <div class="fw-bold fs-5">{{ $project->employees->count() }}</div>
                        <div class="text-muted" style="font-size:.72rem">Anggota Tim</div>
                    </div>
                    <div class="col border-end">
                        <div class="fw-bold fs-5">{{ $project->quotations->count() }}</div>
                        <div class="text-muted" style="font-size:.72rem">Quotation</div>
                    </div>
                    <div class="col">
                        <div class="fw-bold fs-5">{{ $project->invoices->count() }}</div>
                        <div class="text-muted" style="font-size:.72rem">Invoice</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Team management --}}
    <div class="col-lg-8">

        {{-- Current members --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title mb-0"><i class="bi bi-people me-2 text-primary"></i>Anggota Tim</span>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Anggota
                </button>
            </div>

            @if($project->employees->isEmpty())
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                    Belum ada anggota tim. Klik <strong>Tambah Anggota</strong> untuk menambahkan.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Karyawan</th>
                                <th>Peran dalam Project</th>
                                <th>Status Pekerjaan</th>
                                <th>Jabatan Resmi</th>
                                <th>Bergabung</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project->employees as $emp)
                            @php
                                $empWs = $emp->pivot->work_status ?? 'not_started';
                                [$empWsLabel, $empWsColor] = \App\Models\Pivots\ProjectMemberPivot::workStatusLabel($empWs);
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width:36px;height:36px;font-size:.85rem;font-weight:700;color:#1d4ed8">
                                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:.88rem">{{ $emp->name }}</div>
                                            <div class="text-muted" style="font-size:.75rem">{{ $emp->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($emp->pivot->role)
                                        <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.75rem">{{ $emp->pivot->role }}</span>
                                    @else
                                        <span class="text-muted" style="font-size:.82rem">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $empWsColor }} bg-opacity-10 text-{{ $empWsColor }}" style="font-size:.72rem">
                                        <i class="bi bi-{{ $empWs==='completed' ? 'check-circle-fill' : ($empWs==='in_progress' ? 'activity' : 'circle') }} me-1"></i>
                                        {{ $empWsLabel }}
                                    </span>
                                    @if($emp->pivot->work_started_at)
                                        <div class="text-muted" style="font-size:.7rem">Mulai: {{ $emp->pivot->work_started_at?->format('d M Y') }}</div>
                                    @endif
                                    @if($emp->pivot->work_completed_at)
                                        <div class="text-success" style="font-size:.7rem">Selesai: {{ $emp->pivot->work_completed_at?->format('d M Y') }}</div>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size:.82rem">{{ $emp->position ?? '—' }}</td>
                                <td class="text-muted" style="font-size:.82rem">
                                    {{ $emp->pivot->joined_at?->isoFormat('D MMM YYYY') ?? '—' }}
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editMemberModal"
                                        data-emp-id="{{ $emp->id }}"
                                        data-emp-name="{{ $emp->name }}"
                                        data-role="{{ $emp->pivot->role }}"
                                        data-notes="{{ $emp->pivot->notes }}"
                                        data-joined="{{ $emp->pivot->joined_at?->format('Y-m-d') }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST"
                                        action="{{ route('project-plan.members.remove', [$project, $emp]) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Hapus {{ $emp->name }} dari tim project ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @if($emp->pivot->notes)
                            <tr>
                                <td colspan="6" class="py-1 ps-5 text-muted" style="font-size:.78rem">
                                    <i class="bi bi-chat-text me-1"></i>{{ $emp->pivot->notes }}
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Work History --}}
        @if($project->workHistories->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">
                <span class="card-title mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>History Pekerjaan Tim</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-size:.82rem">
                    <thead class="table-light">
                        <tr><th>Karyawan</th><th>Perubahan Status</th><th>Catatan</th><th>Dicatat Oleh</th><th>Waktu</th></tr>
                    </thead>
                    <tbody>
                        @foreach($project->workHistories as $h)
                        @php [$toLabel, $toColor] = \App\Models\Pivots\ProjectMemberPivot::workStatusLabel($h->to_status); @endphp
                        <tr>
                            <td class="fw-medium">{{ $h->employee->name ?? '—' }}</td>
                            <td>
                                @if($h->from_status)
                                    @php [$fromLabel] = \App\Models\Pivots\ProjectMemberPivot::workStatusLabel($h->from_status); @endphp
                                    <span class="text-muted">{{ $fromLabel }}</span>
                                    <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                @endif
                                <span class="badge bg-{{ $toColor }} bg-opacity-10 text-{{ $toColor }}">{{ $toLabel }}</span>
                            </td>
                            <td class="text-muted">{{ $h->note ?? '—' }}</td>
                            <td class="text-muted">{{ $h->logger?->name ?? 'Sistem' }}</td>
                            <td class="text-muted" style="white-space:nowrap">{{ $h->created_at->isoFormat('D MMM YYYY, HH:mm') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Related contracts --}}
        @php
            $contractEmployeeIds = $project->employees->pluck('id');
            $relatedContracts = \App\Models\ContractDocument::with(['employee', 'signer'])
                ->where('project_name', 'like', '%' . $project->name . '%')
                ->orWhereIn('employee_id', $contractEmployeeIds)
                ->orderByDesc('contract_date')
                ->limit(10)
                ->get();
        @endphp
        @if($relatedContracts->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <span class="card-title mb-0"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Kontrak Terkait Tim</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.85rem">
                    <thead class="table-light">
                        <tr>
                            <th>No. Kontrak</th>
                            <th>Karyawan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($relatedContracts as $contract)
                        <tr>
                            <td class="fw-semibold font-monospace" style="font-size:.78rem">{{ $contract->contract_number }}</td>
                            <td>{{ $contract->employee->name ?? '—' }}</td>
                            <td class="text-muted">{{ $contract->contract_date?->isoFormat('D MMM YYYY') ?? '—' }}</td>
                            <td>
                                @if($contract->isSigned())
                                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.7rem"><i class="bi bi-patch-check me-1"></i>Ditandatangani</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:.7rem">Belum Ditandatangani</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('contract-documents.show', $contract) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Repo Contributor Stats --}}
        @if(!empty($repoStats))
        @foreach($repoStats as $repoData)
        <div class="card mt-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title mb-0">
                    <i class="bi bi-{{ $repoData['link']->type === 'github' ? 'github' : 'gitlab' }} me-2 text-primary"></i>
                    Distribusi Kontribusi — {{ $repoData['link']->label }}
                    @if($repoData['parsed'])
                        <span class="text-muted fw-normal" style="font-size:.78rem">
                            ({{ $repoData['parsed']['owner'] }}/{{ $repoData['parsed']['repo'] }})
                        </span>
                    @endif
                </span>
                <a href="{{ $repoData['link']->url }}" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size:.75rem">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Buka Repo
                </a>
            </div>

            @if($repoData['error'])
            {{-- Error state --}}
            <div class="card-body">
                <div class="alert alert-warning py-2 mb-0" style="font-size:.85rem">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {!! $repoData['error'] !!}
                </div>
                <div class="mt-3 text-muted" style="font-size:.8rem">
                    <strong>Cara mengatasi 403:</strong>
                    <ol class="mt-1 mb-0 ps-3">
                        <li>Buka <strong>CMS → Tab "Integrasi Repo"</strong> dan pastikan token sudah disimpan.</li>
                        <li>Untuk GitLab: token harus memiliki scope <code>read_repository</code> <em>dan</em> pengguna harus menjadi anggota project <code>{{ $repoData['parsed']['owner'] ?? '' }}/{{ $repoData['parsed']['repo'] ?? '' }}</code>.</li>
                        <li>Coba buka URL API ini di browser saat login GitLab: <br>
                            <code style="font-size:.75rem">https://gitlab.com/api/v4/projects/{{ urlencode(($repoData['parsed']['owner'] ?? '') . '/' . ($repoData['parsed']['repo'] ?? '')) }}/repository/contributors</code>
                        </li>
                    </ol>
                </div>
            </div>
            @else
            {{-- Success state --}}
            @php
                $totalCommits = collect($repoData['contributors'])->sum('contributions');
            @endphp
            <div class="card-body">
                {{-- Matched employees --}}
                @if(!empty($repoData['matched']))
                <div class="mb-3">
                    <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Anggota Tim yang Terdeteksi</div>
                    <div class="row g-2">
                        @foreach($project->employees as $emp)
                            @if(isset($repoData['matched'][$emp->id]))
                            @php
                                $c = $repoData['matched'][$emp->id];
                                $pct = $totalCommits > 0 ? round(($c['contributions'] / $totalCommits) * 100) : 0;
                                $initials = collect(explode(' ', $emp->name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                                $colors = ['#2563eb','#7c3aed','#0891b2','#16a34a','#d97706','#dc2626','#0d9488'];
                                $bg = $colors[crc32($emp->employee_id) % count($colors)];
                            @endphp
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        @if($c['avatar_url'])
                                            <img src="{{ $c['avatar_url'] }}" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover">
                                        @else
                                            <div style="width:36px;height:36px;border-radius:50%;background:{{ $bg }};display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:#fff;flex-shrink:0">{{ $initials }}</div>
                                        @endif
                                        <div style="flex:1;min-width:0">
                                            <div class="fw-semibold" style="font-size:.88rem">{{ $emp->name }}</div>
                                            <div class="text-muted" style="font-size:.74rem">
                                                @if($c['profile_url'])
                                                    <a href="{{ $c['profile_url'] }}" target="_blank" class="text-muted text-decoration-none">@{{ $c['login'] }}</a>
                                                @else
                                                    {{ $c['login'] }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-primary" style="font-size:1.1rem">{{ number_format($c['contributions']) }}</div>
                                            <div class="text-muted" style="font-size:.72rem">commit</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px;border-radius:50rem">
                                            <div class="progress-bar bg-primary" style="width:{{ $pct }}%"></div>
                                        </div>
                                        <span class="text-muted" style="font-size:.72rem;white-space:nowrap">{{ $pct }}%</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @elseif(!empty($repoData['contributors']))
                <div class="alert alert-info py-2 mb-3" style="font-size:.82rem">
                    <i class="bi bi-info-circle me-1"></i>
                    Data kontributor berhasil diambil, namun belum ada anggota tim yang cocok.
                    Tambahkan URL GitLab (<code>gitlab.com/username</code>) di profil karyawan untuk mencocokkan otomatis.
                </div>
                @endif

                {{-- All contributors (compact) --}}
                @if(!empty($repoData['contributors']))
                <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">
                    Semua Kontributor ({{ count($repoData['contributors']) }}) — Total {{ number_format($totalCommits) }} commit
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="font-size:.82rem">
                        <thead class="table-light">
                            <tr><th>#</th><th>Kontributor</th><th>Commit</th><th>Distribusi</th><th>Karyawan</th></tr>
                        </thead>
                        <tbody>
                            @foreach($repoData['contributors'] as $i => $c)
                            @php
                                $pct = $totalCommits > 0 ? round(($c['contributions'] / $totalCommits) * 100) : 0;
                                $matchedEmp = collect($project->employees)->first(fn($e) => isset($repoData['matched'][$e->id]) && $repoData['matched'][$e->id]['login'] === $c['login']);
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($c['avatar_url'])
                                            <img src="{{ $c['avatar_url'] }}" alt="" style="width:24px;height:24px;border-radius:50%;object-fit:cover">
                                        @endif
                                        @if($c['profile_url'])
                                            <a href="{{ $c['profile_url'] }}" target="_blank" class="text-decoration-none">{{ $c['login'] }}</a>
                                        @else
                                            {{ $c['login'] }}
                                        @endif
                                    </div>
                                </td>
                                <td class="fw-semibold">{{ number_format($c['contributions']) }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress" style="height:5px;border-radius:50rem;width:80px">
                                            <div class="progress-bar bg-primary" style="width:{{ $pct }}%"></div>
                                        </div>
                                        <span class="text-muted" style="font-size:.72rem">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td>
                                    @if($matchedEmp)
                                        <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.72rem">
                                            <i class="bi bi-person-check me-1"></i>{{ $matchedEmp->name }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size:.78rem">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @endif
        </div>
        @endforeach
        @endif

        {{-- Commits per Anggota (Admin only) --}}
        @if(!empty($repoCommits))
        @foreach($repoCommits as $rc)
        <div class="card mt-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title mb-0">
                    <i class="bi bi-{{ $rc['link']->type === 'github' ? 'github' : 'gitlab' }} me-2 text-primary"></i>
                    List Commit / Push — {{ $rc['link']->label }}
                    @if($rc['parsed'])
                        <span class="text-muted fw-normal" style="font-size:.78rem">({{ $rc['parsed']['owner'] }}/{{ $rc['parsed']['repo'] }})</span>
                    @endif
                    @if(!$rc['error'] && $rc['total'])
                        <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ $rc['total'] }} commit</span>
                    @endif
                </span>
                <a href="{{ $rc['link']->url }}" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size:.75rem">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Buka Repo
                </a>
            </div>
            @if($rc['error'])
            <div class="card-body">
                <div class="alert alert-warning py-2 mb-0" style="font-size:.82rem">
                    <i class="bi bi-exclamation-triangle me-1"></i>{!! $rc['error'] !!}
                </div>
            </div>
            @elseif(empty($rc['byAuthor']))
            <div class="card-body text-center py-3 text-muted" style="font-size:.85rem">
                <i class="bi bi-git d-block mb-1 fs-3 opacity-25"></i>Tidak ada data commit yang dapat ditampilkan.
            </div>
            @else
            <div class="card-body p-0">
                <ul class="nav nav-tabs px-3 pt-2" id="commits-tabs-{{ $rc['link']->id }}">
                    @foreach($rc['byAuthor'] as $author => $commits)
                    @php
                        $empForAuthor = collect($project->employees)->first(function($e) use ($rc, $author) {
                            if (!isset($rc['matched'])) return false;
                            foreach ($rc['matched'] as $empId => $c) {
                                if ($c['login'] === $author) return $e->id === $empId;
                            }
                            return false;
                        });
                    @endphp
                    <li class="nav-item">
                        <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                            data-bs-toggle="tab"
                            data-bs-target="#commits-tab-{{ $rc['link']->id }}-{{ $loop->index }}"
                            type="button"
                            style="font-size:.78rem">
                            @if($empForAuthor)
                                <i class="bi bi-person-check me-1 text-success"></i>{{ $empForAuthor->name }}
                            @else
                                <i class="bi bi-person me-1"></i>{{ $author }}
                            @endif
                            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ count($commits) }}</span>
                        </button>
                    </li>
                    @endforeach
                </ul>
                <div class="tab-content">
                    @foreach($rc['byAuthor'] as $author => $commits)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                        id="commits-tab-{{ $rc['link']->id }}-{{ $loop->index }}">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0" style="font-size:.8rem">
                                <thead class="table-light">
                                    <tr><th style="width:80px">Commit</th><th>Pesan</th><th style="width:160px">Waktu</th></tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($commits, 0, 50) as $commit)
                                    <tr>
                                        <td>
                                            @if($commit['url'])
                                                <a href="{{ $commit['url'] }}" target="_blank" class="font-monospace text-primary" style="font-size:.75rem">{{ $commit['sha'] }}</a>
                                            @else
                                                <span class="font-monospace text-muted">{{ $commit['sha'] }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $commit['message'] }}</td>
                                        <td class="text-muted" style="white-space:nowrap">
                                            {{ $commit['date'] ? \Carbon\Carbon::parse($commit['date'])->isoFormat('D MMM YYYY, HH:mm') : '—' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(count($commits) > 50)
                        <div class="px-3 py-2 text-muted" style="font-size:.75rem">
                            Menampilkan 50 dari {{ count($commits) }} commit terbaru.
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endforeach
        @endif

    </div>
</div>

{{-- Modal: Tambah Anggota --}}
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('project-plan.members.add', $project) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Tambah Anggota Tim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Karyawan <span class="text-danger">*</span></label>
                        <select name="employee_id" id="add-member-employee" class="form-select" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($employees as $emp)
                                @unless($project->employees->contains($emp->id))
                                    <option value="{{ $emp->id }}" data-position="{{ $emp->position }}">{{ $emp->name }} — {{ $emp->position ?? $emp->employee_id }}</option>
                                @endunless
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Peran dalam Project</label>
                        <input type="text" name="role" id="add-member-role" class="form-control" placeholder="Kosongkan untuk otomatis dari jabatan karyawan">
                        <div class="form-text" style="font-size:.72rem">Jika dikosongkan, akan diisi otomatis dari jabatan karyawan.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Tanggal Bergabung</label>
                        <input type="date" name="joined_at" class="form-control" value="{{ $project->start_date?->format('Y-m-d') ?? now()->format('Y-m-d') }}">
                        <div class="form-text" style="font-size:.72rem">Default: tanggal mulai project{{ $project->start_date ? ' (' . $project->start_date->isoFormat('D MMM YYYY') . ')' : '' }}.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambahkan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Edit Anggota --}}
<div class="modal fade" id="editMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editMemberForm">
            @csrf @method('PATCH')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMemberTitle"><i class="bi bi-pencil me-2"></i>Edit Anggota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Peran dalam Project</label>
                        <input type="text" name="role" id="edit-member-role" class="form-control" placeholder="Cth: Lead Developer">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Tanggal Bergabung</label>
                        <input type="date" name="joined_at" id="edit-member-joined" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Catatan</label>
                        <textarea name="notes" id="edit-member-notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('[data-bs-target="#editMemberModal"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var empId   = btn.dataset.empId;
        var empName = btn.dataset.empName;
        var baseUrl = '{{ route("project-plan.members.update", [$project, "__EMP__"]) }}';

        document.getElementById('editMemberForm').action = baseUrl.replace('__EMP__', empId);
        document.getElementById('editMemberTitle').textContent = '\u270F Edit: ' + empName;
        document.getElementById('edit-member-role').value   = btn.dataset.role   || '';
        document.getElementById('edit-member-notes').value  = btn.dataset.notes  || '';
        document.getElementById('edit-member-joined').value = btn.dataset.joined || '';
    });
});

// Auto-fill role from employee position when selecting in add modal
var addEmpSelect = document.getElementById('add-member-employee');
var addRoleInput = document.getElementById('add-member-role');
if (addEmpSelect && addRoleInput) {
    addEmpSelect.addEventListener('change', function () {
        var selected = addEmpSelect.options[addEmpSelect.selectedIndex];
        if (addRoleInput.value === '' && selected && selected.dataset.position) {
            addRoleInput.value = selected.dataset.position;
        }
    });
}
</script>
@endpush

@endsection
