@extends('layouts.app')
@section('title', 'Dokumen Kontrak')
@section('page-title', 'Dokumen Kontrak')

@section('content')
<div class="card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" action="{{ route('contract-documents.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#64748b">CARI NOMOR KONTRAK</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="001/SPK/...">
            </div>
            <div class="col-md-4">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#64748b">KARYAWAN</label>
                <select name="employee_id" class="form-select form-select-sm">
                    <option value="">Semua Karyawan</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('contract-documents.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                <a href="{{ route('contract-documents.create') }}" class="btn btn-success btn-sm ms-auto"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="card-title mb-0"><i class="bi bi-folder2-open me-2 text-primary"></i>CRUD Dokumen Kontrak Kerja</span>
        <span class="text-muted" style="font-size:.82rem">{{ $contracts->total() }} dokumen</span>
    </div>

    @if($contracts->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-folder2-open fs-1 d-block mb-2 opacity-25"></i>
            Belum ada dokumen kontrak.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>No. Kontrak</th>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th>Nilai</th>
                        <th>Status</th>
                        <th>File</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contracts as $contract)
                        <tr>
                            <td class="fw-semibold">{{ $contract->contract_number }}</td>
                            <td>
                                <div class="fw-semibold">{{ $contract->employee->name ?? '-' }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $contract->employee->company->name ?? '-' }}</div>
                            </td>
                            <td style="font-size:.82rem">{{ $contract->contract_date?->isoFormat('D MMM YYYY') ?? '-' }}</td>
                            <td style="font-size:.82rem">{{ $contract->contract_value ? 'Rp ' . number_format($contract->contract_value, 0, ',', '.') : '-' }}</td>
                            <td>
                                @if($contract->isSigned())
                                    <span class="badge" style="background:#dcfce7;color:#15803d;border:1px solid #86efac;font-size:.72rem">Signed</span>
                                @else
                                    <span class="badge" style="background:#fef9c3;color:#a16207;border:1px solid #fde047;font-size:.72rem">Pending</span>
                                @endif
                            </td>
                            <td style="font-size:.82rem">{{ $contract->original_name ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('contract-documents.show', $contract) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                                @if(!$contract->isSigned() && !$contract->isSignedByEmployee())
                                    <a href="{{ route('contract-documents.edit', $contract) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('contract-documents.destroy', $contract) }}" class="d-inline" 
                                        onsubmit="return confirm('Hapus permanen dokumen kontrak ini?\nTindakan ini TIDAK DAPAT DIBATALKAN.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Gunakan Batalkan untuk mengakhiri kontrak yang sudah ditandatangani">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" disabled title="Hanya kontrak yang belum ditandatangani yang bisa dihapus">Hapus</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($contracts->hasPages())
        <div class="card-footer bg-white border-0 pt-0">
            {{ $contracts->links() }}
        </div>
    @endif
</div>
@endsection