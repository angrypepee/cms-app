@extends('layouts.app')
@section('title', 'Pengajuan Lembur')
@section('page-title', 'Pengajuan Lembur')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#1e293b">Pengajuan Lembur</h5>
        <p class="text-muted mb-0" style="font-size:.84rem">Daftar semua pengajuan lembur karyawan</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="row g-2 mb-4 align-items-end">
    <div class="col-sm-5 col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Cari nama karyawan…" value="{{ request('search') }}">
    </div>
    <div class="col-sm-4 col-md-3">
        <select name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Menunggu</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-funnel me-1"></i>Filter</button>
        @if(request()->hasAny(['search','status']))
            <a href="{{ route('overtime.index') }}" class="btn btn-outline-danger ms-1"><i class="bi bi-x-lg"></i></a>
        @endif
    </div>
</form>

<div class="card">
    @if($overtimeRequests->isEmpty())
        <div class="text-center py-5" style="color:#94a3b8">
            <i class="bi bi-clock-history d-block mb-2" style="font-size:2rem;opacity:.3"></i>
            Belum ada pengajuan lembur.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Perusahaan</th>
                        <th>Tanggal</th>
                        <th>Waktu Lembur</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overtimeRequests as $ot)
                    <tr>
                        <td>
                            <div class="fw-medium" style="font-size:.875rem">{{ $ot->employee->name }}</div>
                            <div class="text-muted" style="font-size:.75rem">{{ $ot->employee->employee_id }}</div>
                        </td>
                        <td class="text-muted" style="font-size:.82rem">{{ $ot->employee->company->name ?? '-' }}</td>
                        <td style="font-size:.875rem">{{ $ot->date->isoFormat('D MMM YYYY') }}</td>
                        <td style="font-size:.875rem">
                            {{ substr($ot->start_time, 0, 5) }} &ndash; {{ substr($ot->end_time, 0, 5) }}
                        </td>
                        <td style="font-size:.82rem;color:#64748b">{{ $ot->durationLabel() }}</td>
                        <td>
                            <span class="badge badge-pill {{ $ot->statusBadgeClass() }}">{{ $ot->statusLabel() }}</span>
                        </td>
                        <td>
                            <a href="{{ route('overtime.show', $ot) }}" class="btn btn-sm btn-outline-secondary">Review</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($overtimeRequests->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $overtimeRequests->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
