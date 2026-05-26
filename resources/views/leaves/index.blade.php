@extends('layouts.app')

@section('page-title', 'Manajemen Cuti')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Permohonan Cuti Karyawan</h4>
        <p class="text-muted small mb-0">Review dan approve permohonan cuti</p>
    </div>
    <a href="{{ route('leaves.types') }}" class="btn btn-outline-primary">
        <i class="bi bi-list-ul me-1"></i> Kelola Tipe Cuti
    </a>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input name="search" type="text" class="form-control form-control-sm" placeholder="Cari nama karyawan..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending"  @selected(request('status')=='pending')>Menunggu</option>
                    <option value="approved" @selected(request('status')=='approved')>Disetujui</option>
                    <option value="rejected" @selected(request('status')=='rejected')>Ditolak</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="leave_type_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->id }}" @selected(request('leave_type_id')==$lt->id)>{{ $lt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Cari</button>
                <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Karyawan</th>
                        <th>Tipe Cuti</th>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveRequests as $lr)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $lr->employee->name }}</div>
                                <div class="text-muted small">{{ $lr->employee->company->name ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-pill" style="background-color:{{ $lr->leaveType->color ?? '#2563eb' }}">
                                    {{ $lr->leaveType->name }}
                                </span>
                            </td>
                            <td class="small">
                                {{ $lr->start_date->format('d M Y') }}
                                @if(!$lr->start_date->equalTo($lr->end_date))
                                    <br>s/d {{ $lr->end_date->format('d M Y') }}
                                @endif
                            </td>
                            <td class="text-center fw-semibold">{{ $lr->days_count }}</td>
                            <td class="small" style="max-width:200px">
                                <div class="text-truncate" title="{{ $lr->reason }}">{{ $lr->reason }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $lr->statusBadgeClass() }} badge-pill">{{ $lr->statusLabel() }}</span>
                            </td>
                            <td class="small text-muted">{{ $lr->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('leaves.show', $lr) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>
                                Tidak ada permohonan cuti
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($leaveRequests->hasPages())
        <div class="card-footer">
            {{ $leaveRequests->links() }}
        </div>
    @endif
</div>
@endsection
