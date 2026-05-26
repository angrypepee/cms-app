@extends('layouts.app')

@section('title', 'Reimbursement')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-receipt me-2 text-primary"></i>Reimbursement</h4>
            <p class="text-muted mb-0" style="font-size:.88rem">Daftar permohonan reimbursement karyawan</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1" style="font-size:.82rem">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Menunggu</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label mb-1" style="font-size:.82rem">Karyawan</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">Semua Karyawan</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ $emp->employee_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
                    @if(request()->anyFilled(['status', 'employee_id']))
                        <a href="{{ route('reimbursements.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    @if($reimbursements->isEmpty())
        <div class="card"><div class="card-body text-center py-5 text-muted">
            <i class="bi bi-receipt fs-1 d-block mb-2 opacity-25"></i>
            Belum ada permohonan reimbursement.
        </div></div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Approver</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reimbursements as $r)
                        <tr class="{{ $r->isPending() ? 'table-warning-subtle' : '' }}">
                            <td>
                                <span class="fw-medium" style="font-size:.88rem">{{ $r->employee?->name }}</span>
                                <div class="text-muted" style="font-size:.75rem">{{ $r->employee?->employee_id }}</div>
                            </td>
                            <td style="font-size:.88rem">{{ $r->title }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem">
                                    {{ $r->categoryLabel() }}
                                </span>
                            </td>
                            <td class="fw-semibold text-success">Rp {{ number_format($r->amount, 0, ',', '.') }}</td>
                            <td style="font-size:.85rem">{{ $r->approver?->name }}</td>
                            <td><span class="badge badge-pill {{ $r->statusBadgeClass() }}">{{ $r->statusLabel() }}</span></td>
                            <td class="text-muted" style="font-size:.82rem">{{ $r->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('reimbursements.show', $r) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($reimbursements->hasPages())
                <div class="card-footer">{{ $reimbursements->links() }}</div>
            @endif
        </div>
    @endif
</div>
@endsection
