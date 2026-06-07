@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
@php
    $initials = collect(explode(' ', $employee->name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
    $colors   = ['#2563eb','#7c3aed','#0891b2','#16a34a','#d97706','#dc2626','#0d9488'];
    $bgColor  = $colors[crc32($employee->employee_id) % count($colors)];
@endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible py-2 mb-4" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- Left: Profile card --}}
    <div class="col-lg-4">
        <div class="card">
            {{-- Header gradient --}}
            <div style="background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 60%,#2563eb 100%);padding:1.75rem 1.5rem;color:#fff;border-radius:.5rem .5rem 0 0;position:relative;overflow:hidden">
                <div style="position:absolute;right:-30px;top:-30px;width:130px;height:130px;border-radius:50%;background:rgba(255,255,255,.07)"></div>
                <div class="d-flex flex-column align-items-center position-relative" style="z-index:1;text-align:center">
                    <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:700;color:#fff;border:3px solid rgba(255,255,255,.4);margin-bottom:.75rem">
                        {{ $initials }}
                    </div>
                    <div style="font-size:1.1rem;font-weight:700">{{ $employee->name }}</div>
                    <div style="font-size:.78rem;opacity:.75;margin-top:.2rem">{{ $employee->position ?? 'Karyawan' }}</div>
                    <div style="font-size:.72rem;opacity:.6;margin-top:.1rem">{{ $employee->company->name ?? '-' }}</div>
                    @if($employee->github_url || $employee->gitlab_url || $employee->linkedin_url || $employee->portfolio_url)
                    <div class="d-flex gap-2 mt-3 flex-wrap justify-content-center">
                        @if($employee->github_url)
                            <a href="{{ $employee->github_url }}" target="_blank" style="color:rgba(255,255,255,.8);font-size:1.1rem" title="GitHub"><i class="bi bi-github"></i></a>
                        @endif
                        @if($employee->gitlab_url)
                            <a href="{{ $employee->gitlab_url }}" target="_blank" style="color:rgba(255,255,255,.8);font-size:1.1rem" title="GitLab"><i class="bi bi-gitlab"></i></a>
                        @endif
                        @if($employee->linkedin_url)
                            <a href="{{ $employee->linkedin_url }}" target="_blank" style="color:rgba(255,255,255,.8);font-size:1.1rem" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                        @endif
                        @if($employee->portfolio_url)
                            <a href="{{ $employee->portfolio_url }}" target="_blank" style="color:rgba(255,255,255,.8);font-size:1.1rem" title="Portfolio"><i class="bi bi-globe"></i></a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Info list --}}
            <div class="card-footer bg-transparent p-0">
                <ul class="list-group list-group-flush" style="font-size:.85rem">
                    <li class="list-group-item px-4 py-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">ID Karyawan</span>
                            <span class="fw-medium font-monospace">{{ $employee->employee_id }}</span>
                        </div>
                    </li>
                    <li class="list-group-item px-4 py-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Departemen</span>
                            <span>{{ $employee->department ?? '—' }}</span>
                        </div>
                    </li>
                    @if($employee->grade)
                    <li class="list-group-item px-4 py-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Grade</span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $employee->grade }}</span>
                        </div>
                    </li>
                    @endif
                    @if($employee->employee_category)
                    <li class="list-group-item px-4 py-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Kategori</span>
                            <span class="badge bg-{{ $employee->employee_category->badgeColor() }} bg-opacity-10 text-{{ $employee->employee_category->badgeColor() }}" style="font-size:.72rem">{{ $employee->employee_category->label() }}</span>
                        </div>
                    </li>
                    @endif
                    @if($employee->contract_start)
                    <li class="list-group-item px-4 py-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Kontrak</span>
                            <span>{{ $employee->contract_start->isoFormat('D MMM YYYY') }} → {{ $employee->contract_end?->isoFormat('D MMM YYYY') ?? 'Permanen' }}</span>
                        </div>
                    </li>
                    @endif
                    <li class="list-group-item px-4 py-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            @if($employee->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.72rem">Aktif</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem">Nonaktif</span>
                            @endif
                        </div>
                    </li>
                    <li class="list-group-item px-4 py-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Email Akun</span>
                            <span class="text-muted" style="font-size:.8rem">{{ $user->email }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Project summary --}}
        @if($employee->projects->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header"><span class="card-title mb-0" style="font-size:.85rem"><i class="bi bi-kanban me-2 text-primary"></i>Project Aktif</span></div>
            <div class="card-body p-3">
                @foreach($employee->projects->where('status', 'active')->take(3) as $proj)
                <div class="d-flex justify-content-between align-items-center py-1" style="font-size:.82rem">
                    <div>
                        <div class="fw-medium">{{ $proj->name }}</div>
                        <div class="text-muted" style="font-size:.74rem">{{ $proj->client->name ?? '—' }}</div>
                    </div>
                    <a href="{{ route('my.projects') }}" class="btn btn-sm btn-outline-primary py-0" style="font-size:.72rem">Lihat</a>
                </div>
                @endforeach
                @if($employee->projects->where('status','active')->count() === 0)
                    <div class="text-muted" style="font-size:.82rem">Tidak ada project aktif</div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Right: edit forms --}}
    <div class="col-lg-8">

        {{-- Edit links --}}
        <div class="card mb-4">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-link-45deg me-2 text-primary"></i>Tautan Profesional</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('my.profile.update') }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium" style="font-size:.85rem"><i class="bi bi-github me-1"></i>GitHub</label>
                            <input type="url" name="github_url" class="form-control @error('github_url') is-invalid @enderror"
                                value="{{ old('github_url', $employee->github_url) }}"
                                placeholder="https://github.com/username">
                            @error('github_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium" style="font-size:.85rem"><i class="bi bi-gitlab me-1"></i>GitLab</label>
                            <input type="url" name="gitlab_url" class="form-control @error('gitlab_url') is-invalid @enderror"
                                value="{{ old('gitlab_url', $employee->gitlab_url) }}"
                                placeholder="https://gitlab.com/username">
                            @error('gitlab_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium" style="font-size:.85rem"><i class="bi bi-linkedin me-1"></i>LinkedIn</label>
                            <input type="url" name="linkedin_url" class="form-control @error('linkedin_url') is-invalid @enderror"
                                value="{{ old('linkedin_url', $employee->linkedin_url) }}"
                                placeholder="https://linkedin.com/in/username">
                            @error('linkedin_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium" style="font-size:.85rem"><i class="bi bi-globe me-1"></i>Website / Portfolio</label>
                            <input type="url" name="portfolio_url" class="form-control @error('portfolio_url') is-invalid @enderror"
                                value="{{ old('portfolio_url', $employee->portfolio_url) }}"
                                placeholder="https://portofolio.com">
                            @error('portfolio_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg me-1"></i>Simpan Tautan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change password --}}
        <div class="card mb-4">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-key me-2 text-primary"></i>Ganti Password</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('my.profile.update') }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium" style="font-size:.85rem">Password Saat Ini <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium" style="font-size:.85rem">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" autocomplete="new-password" minlength="8">
                            @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium" style="font-size:.85rem">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-key me-1"></i>Ganti Password</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Portfolio files --}}
        @if($employee->portfolios->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-folder2-open me-2 text-primary"></i>Portfolio Saya</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.85rem">
                    <thead class="table-light">
                        <tr><th>File</th><th>Ukuran</th><th>Tanggal</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($employee->portfolios as $pf)
                        <tr>
                            <td>
                                <i class="bi bi-file-earmark{{ $pf->isPdf() ? '-pdf text-danger' : ($pf->isImage() ? '-image text-info' : '') }} me-2"></i>
                                <span class="fw-medium">{{ $pf->label }}</span>
                                <div class="text-muted" style="font-size:.74rem">{{ $pf->original_name }}</div>
                            </td>
                            <td class="text-muted">{{ $pf->fileSizeFormatted() }}</td>
                            <td class="text-muted">{{ $pf->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($pf->isViewable())
                                        <a href="{{ route('employee-portfolios.show', [$employee, $pf]) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                    @endif
                                    <a href="{{ route('employee-portfolios.show', [$employee, $pf]) }}?download=1" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
