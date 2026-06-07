@extends('layouts.app')
@section('title', 'Karyawan')
@section('page-title', 'Karyawan')
@section('content')

{{-- Summary Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:42px;height:42px;border-radius:.65rem;background:#f3f0ff;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;color:#7c3aed"><i class="bi bi-people-fill"></i></div>
                <div><div class="fw-bold" style="font-size:1.5rem;line-height:1;color:#1e293b">{{ $stats['total'] }}</div>
                <div class="text-muted" style="font-size:.74rem">Total Karyawan</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:42px;height:42px;border-radius:.65rem;background:#f0fdf4;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;color:#16a34a"><i class="bi bi-person-check-fill"></i></div>
                <div><div class="fw-bold" style="font-size:1.5rem;line-height:1;color:#1e293b">{{ $stats['active'] }}</div>
                <div class="text-muted" style="font-size:.74rem">Aktif</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:42px;height:42px;border-radius:.65rem;background:#f8fafc;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;color:#94a3b8"><i class="bi bi-person-dash-fill"></i></div>
                <div><div class="fw-bold" style="font-size:1.5rem;line-height:1;color:#1e293b">{{ $stats['inactive'] }}</div>
                <div class="text-muted" style="font-size:.74rem">Nonaktif</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:42px;height:42px;border-radius:.65rem;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;color:#2563eb"><i class="bi bi-person-badge-fill"></i></div>
                <div><div class="fw-bold" style="font-size:1.5rem;line-height:1;color:#1e293b">{{ $stats['with_account'] }}</div>
                <div class="text-muted" style="font-size:.74rem">Punya Akun Login</div></div>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('employees.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Cari</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Nama / ID karyawan...">
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm fw-medium mb-1">Perusahaan</label>
                <select name="company_id" class="form-select form-select-sm">
                    <option value="">Semua Perusahaan</option>
                    @foreach($companies as $co)
                        <option value="{{ $co->id }}" {{ request('company_id') == $co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm fw-medium mb-1">Kategori</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->value }}" {{ request('category') === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm fw-medium mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                <a href="{{ route('employees.create') }}" class="btn btn-success btn-sm ms-auto"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-people me-2 text-primary"></i>Daftar Karyawan</span>
        <span class="badge bg-secondary">{{ $employees->total() }}</span>
    </div>
    @if($employees->isEmpty())
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>Tidak ada karyawan ditemukan.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width:36px"></th>
                        <th>Karyawan</th>
                        <th>Perusahaan</th>
                        <th>Jabatan / Dept</th>
                        <th>Kategori</th>
                        @if(auth()->user()->isAdmin())
                        <th>BPJS</th>
                        <th>Gaji Pokok</th>
                        @endif
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                    @php
                        $initials = collect(explode(' ', $emp->name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                        $colors   = ['#2563eb','#7c3aed','#0891b2','#16a34a','#d97706','#dc2626','#0d9488'];
                        $bgColor  = $colors[crc32($emp->employee_id) % count($colors)];
                    @endphp
                    <tr>
                        <td>
                            <div style="width:36px;height:36px;border-radius:50%;background:{{ $bgColor }};display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#fff;flex-shrink:0">{{ $initials }}</div>
                        </td>
                        <td>
                            <div>
                                <a href="{{ route('employees.show', $emp) }}" class="fw-medium text-decoration-none" style="font-size:.875rem;color:#1e293b">{{ $emp->name }}</a>
                                <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                    <span class="font-monospace text-muted" style="font-size:.72rem">{{ $emp->employee_id }}</span>
                                    @if(!$emp->user)
                                        <span class="badge" style="font-size:.62rem;background:#fef2f2;color:#dc2626;border:1px solid #fecaca">Belum ada akun</span>
                                    @endif
                                    @if($emp->github_url)
                                        <a href="{{ $emp->github_url }}" target="_blank" class="text-muted" title="GitHub" style="font-size:.8rem"><i class="bi bi-github"></i></a>
                                    @endif
                                    @if($emp->linkedin_url)
                                        <a href="{{ $emp->linkedin_url }}" target="_blank" style="color:#0a66c2;font-size:.8rem" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                                    @endif
                                    @if($emp->portfolio_url || $emp->gitlab_url)
                                        <a href="{{ $emp->portfolio_url ?? $emp->gitlab_url }}" target="_blank" class="text-muted" style="font-size:.8rem" title="Portfolio"><i class="bi bi-globe"></i></a>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-muted" style="font-size:.85rem">{{ $emp->company->name ?? '-' }}</td>
                        <td style="font-size:.85rem">
                            <div>{{ $emp->position ?? '-' }}</div>
                            @if($emp->department)<div class="text-muted" style="font-size:.78rem">{{ $emp->department }}</div>@endif
                            @if($emp->grade)<span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.68rem">{{ $emp->grade }}</span>@endif
                        </td>
                        <td>
                            @if($emp->employee_category)
                                <span class="badge bg-{{ $emp->employee_category->badgeColor() }} bg-opacity-10 text-{{ $emp->employee_category->badgeColor() }} badge-pill" style="font-size:.72rem">{{ $emp->employee_category->label() }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        @if(auth()->user()->isAdmin())
                        <td>
                            @php $bk = !empty($emp->bpjs_kesehatan); $bkt = !empty($emp->bpjs_ketenagakerjaan); @endphp
                            <div class="d-flex flex-column gap-1">
                                <span class="badge badge-pill" style="font-size:.65rem;background:{{ $bk ? '#f0fdf4' : '#f8fafc' }};color:{{ $bk ? '#16a34a' : '#94a3b8' }};border:1px solid {{ $bk ? '#bbf7d0' : '#e2e8f0' }}">
                                    <i class="bi bi-{{ $bk ? 'check' : 'x' }}-circle me-1"></i>Kesehatan
                                </span>
                                <span class="badge badge-pill" style="font-size:.65rem;background:{{ $bkt ? '#f0fdf4' : '#f8fafc' }};color:{{ $bkt ? '#16a34a' : '#94a3b8' }};border:1px solid {{ $bkt ? '#bbf7d0' : '#e2e8f0' }}">
                                    <i class="bi bi-{{ $bkt ? 'check' : 'x' }}-circle me-1"></i>Ketenagakerjaan
                                </span>
                            </div>
                        </td>
                        <td class="fw-semibold text-success" style="font-size:.82rem">
                            Rp {{ number_format($emp->base_salary, 0, ',', '.') }}
                        </td>
                        @endif
                        <td>
                            @if($emp->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.72rem">Aktif</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('employees.show', $emp) }}" class="btn btn-sm btn-outline-secondary">Lihat</a>
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('employees.edit', $emp) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('employees.destroy', $emp) }}" onsubmit="return confirm('Hapus karyawan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent">{{ $employees->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
