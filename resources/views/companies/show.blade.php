@extends('layouts.app')
@section('title', $company->name)
@section('page-title', $company->name)
@section('content')
<div class="row g-4">
    {{-- Company Detail Card --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body p-4 text-center">
                @if($company->logo)
                    <img src="{{ asset('storage/'.$company->logo) }}" class="rounded mb-3" style="width:80px;height:80px;object-fit:contain;border:1px solid #e2e8f0" alt="{{ $company->name }}">
                @else
                    <div class="rounded bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" style="width:80px;height:80px">
                        <i class="bi bi-building-fill text-primary fs-2"></i>
                    </div>
                @endif
                <h5 class="fw-bold mb-1">{{ $company->name }}</h5>
                @if($company->tagline)<p class="text-muted mb-3" style="font-size:.85rem">{{ $company->tagline }}</p>@endif
                <div class="d-flex gap-2 justify-content-center mb-3">
                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
                    <form method="POST" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm('Hapus perusahaan ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3 me-1"></i>Hapus</button>
                    </form>
                </div>
            </div>
            <div class="card-footer bg-transparent p-0">
                <ul class="list-group list-group-flush">
                    @if($company->address)
                        <li class="list-group-item px-4 py-3">
                            <div class="d-flex gap-2"><i class="bi bi-geo-alt text-muted mt-1 flex-shrink-0"></i><span style="font-size:.85rem">{{ $company->address }}</span></div>
                        </li>
                    @endif
                    @if($company->phone)
                        <li class="list-group-item px-4 py-3">
                            <div class="d-flex gap-2"><i class="bi bi-telephone text-muted flex-shrink-0"></i><span style="font-size:.85rem">{{ $company->phone }}</span></div>
                        </li>
                    @endif
                    @if($company->email)
                        <li class="list-group-item px-4 py-3">
                            <div class="d-flex gap-2"><i class="bi bi-envelope text-muted flex-shrink-0"></i><span style="font-size:.85rem">{{ $company->email }}</span></div>
                        </li>
                    @endif
                    @if($company->website)
                        <li class="list-group-item px-4 py-3">
                            <div class="d-flex gap-2"><i class="bi bi-globe text-muted flex-shrink-0"></i><a href="{{ $company->website }}" target="_blank" style="font-size:.85rem">{{ $company->website }}</a></div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    {{-- Employees Table --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-people me-2 text-primary"></i>Karyawan ({{ $company->employees->count() }})</span>
                <a href="{{ route('employees.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
            </div>
            @if($company->employees->isEmpty())
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>Belum ada karyawan di perusahaan ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>ID</th><th>Nama</th><th>Jabatan</th><th>Departemen</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach($company->employees as $emp)
                            <tr>
                                <td><span class="font-monospace text-muted" style="font-size:.78rem">{{ $emp->employee_id }}</span></td>
                                <td class="fw-medium">{{ $emp->name }}</td>
                                <td>{{ $emp->position ?? '-' }}</td>
                                <td class="text-muted">{{ $emp->department ?? '-' }}</td>
                                <td>
                                    @if($emp->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.72rem">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem">Nonaktif</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('employees.show', $emp) }}" class="btn btn-sm btn-outline-secondary">Lihat</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
