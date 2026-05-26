@extends('layouts.app')
@section('title', 'Perusahaan')
@section('page-title', 'Perusahaan')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0" style="font-size:.85rem">{{ $companies->total() }} perusahaan terdaftar</p>
    <a href="{{ route('companies.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Perusahaan</a>
</div>
@if($companies->isEmpty())
    <div class="card"><div class="card-body text-center py-5 text-muted">
        <i class="bi bi-building fs-1 d-block mb-2 opacity-25"></i>
        Belum ada perusahaan. <a href="{{ route('companies.create') }}">Tambah sekarang</a>
    </div></div>
@else
<div class="row g-4">
    @foreach($companies as $company)
    <div class="col-sm-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    @if($company->logo)
                        <img src="{{ asset('storage/'.$company->logo) }}" class="rounded" style="width:52px;height:52px;object-fit:contain;border:1px solid #e2e8f0" alt="{{ $company->name }}">
                    @else
                        <div class="rounded bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px">
                            <i class="bi bi-building-fill text-primary fs-4"></i>
                        </div>
                    @endif
                    <div class="flex-grow-1 overflow-hidden">
                        <h6 class="fw-bold mb-0 text-truncate">{{ $company->name }}</h6>
                        @if($company->tagline)<p class="text-muted mb-0" style="font-size:.78rem">{{ $company->tagline }}</p>@endif
                        <span class="badge bg-primary bg-opacity-10 text-primary mt-1" style="font-size:.7rem">{{ $company->employees_count ?? 0 }} karyawan</span>
                    </div>
                </div>
                @if($company->address)
                    <p class="text-muted mb-1" style="font-size:.8rem"><i class="bi bi-geo-alt me-1"></i>{{ Str::limit($company->address, 60) }}</p>
                @endif
                @if($company->phone)
                    <p class="text-muted mb-0" style="font-size:.8rem"><i class="bi bi-telephone me-1"></i>{{ $company->phone }}</p>
                @endif
            </div>
            <div class="card-footer bg-transparent border-top d-flex gap-2 p-3">
                <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-outline-secondary flex-fill">Lihat</a>
                <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-outline-primary flex-fill">Edit</a>
                <form method="POST" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm('Hapus perusahaan ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@if($companies->hasPages())<div class="mt-4">{{ $companies->links() }}</div>@endif
@endsection
