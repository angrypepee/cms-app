@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')

{{-- Greeting --}}
@php
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
@endphp
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div style="min-width:0;flex:1 1 auto">
        <h5 class="fw-bold mb-1 text-truncate" style="color:#1e293b">{{ $greeting }}, {{ auth()->user()->name }} 👋</h5>
        <p class="text-muted mb-0" style="font-size:.84rem">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
    </div>
    <a href="{{ route('payroll-slips.create') }}" class="btn btn-primary d-none d-md-inline-flex align-items-center gap-1 flex-shrink-0">
        <i class="bi bi-plus-lg"></i> Buat Slip Gaji
    </a>
</div>

{{-- Stats Row 1 --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:46px;height:46px;border-radius:.75rem;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;color:#2563eb">
                    <i class="bi bi-building-fill"></i></div>
                <div><div class="fw-bold" style="font-size:1.6rem;line-height:1;color:#1e293b">{{ $stats['companies'] }}</div>
                <div class="text-muted" style="font-size:.75rem">Perusahaan</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:46px;height:46px;border-radius:.75rem;background:#f3f0ff;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;color:#7c3aed">
                    <i class="bi bi-people-fill"></i></div>
                <div><div class="fw-bold" style="font-size:1.6rem;line-height:1;color:#1e293b">{{ $stats['employees'] }}</div>
                <div class="text-muted" style="font-size:.75rem">Karyawan Aktif</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:46px;height:46px;border-radius:.75rem;background:#f0fdf4;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;color:#16a34a">
                    <i class="bi bi-receipt-cutoff"></i></div>
                <div><div class="fw-bold" style="font-size:1.6rem;line-height:1;color:#1e293b">{{ $stats['slips_total'] }}</div>
                <div class="text-muted" style="font-size:.75rem">Total Slip Gaji</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:46px;height:46px;border-radius:.75rem;background:#fefce8;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;color:#ca8a04">
                    <i class="bi bi-clock-history"></i></div>
                <div><div class="fw-bold" style="font-size:1.6rem;line-height:1;color:#1e293b">{{ $stats['slips_draft'] }}</div>
                <div class="text-muted" style="font-size:.75rem">Slip Draft</div></div>
            </div>
        </div>
    </div>
</div>
{{-- Stats Row 2 — Pending items (5 cards: 2/row mobile, 3/row sm-lg, 5/row xl) --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-sm-4 col-xl">
        <a href="{{ route('reimbursements.index') }}?status=pending" class="card h-100 text-decoration-none {{ $stats['pending_reimbursements'] > 0 ? 'border-danger border-opacity-50' : '' }}">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:46px;height:46px;border-radius:.75rem;background:{{ $stats['pending_reimbursements'] > 0 ? '#fef2f2' : '#f8fafc' }};display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;color:{{ $stats['pending_reimbursements'] > 0 ? '#dc2626' : '#94a3b8' }}">
                    <i class="bi bi-receipt"></i></div>
                <div><div class="fw-bold" style="font-size:1.6rem;line-height:1;color:{{ $stats['pending_reimbursements'] > 0 ? '#dc2626' : '#1e293b' }}">{{ $stats['pending_reimbursements'] }}</div>
                <div class="text-muted" style="font-size:.75rem">Reimburse Pending</div></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-sm-4 col-xl">
        <a href="{{ route('appreciation.index') }}" class="card h-100 text-decoration-none {{ $stats['pending_claims'] > 0 ? 'border-warning border-opacity-50' : '' }}">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:46px;height:46px;border-radius:.75rem;background:{{ $stats['pending_claims'] > 0 ? '#fffbeb' : '#f8fafc' }};display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;color:{{ $stats['pending_claims'] > 0 ? '#d97706' : '#94a3b8' }}">
                    <i class="bi bi-stars"></i></div>
                <div><div class="fw-bold" style="font-size:1.6rem;line-height:1;color:{{ $stats['pending_claims'] > 0 ? '#d97706' : '#1e293b' }}">{{ $stats['pending_claims'] }}</div>
                <div class="text-muted" style="font-size:.75rem">Klaim Apresiasi</div></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-sm-4 col-xl">
        <a href="{{ route('leaves.index') }}" class="card h-100 text-decoration-none {{ $stats['pending_leaves'] > 0 ? 'border-primary border-opacity-50' : '' }}">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:46px;height:46px;border-radius:.75rem;background:{{ $stats['pending_leaves'] > 0 ? '#eff6ff' : '#f8fafc' }};display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;color:{{ $stats['pending_leaves'] > 0 ? '#2563eb' : '#94a3b8' }}">
                    <i class="bi bi-calendar-check"></i></div>
                <div><div class="fw-bold" style="font-size:1.6rem;line-height:1;color:{{ $stats['pending_leaves'] > 0 ? '#2563eb' : '#1e293b' }}">{{ $stats['pending_leaves'] }}</div>
                <div class="text-muted" style="font-size:.75rem">Cuti Pending</div></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-sm-4 col-xl">
        <a href="{{ route('internal-requests.index') }}" class="card h-100 text-decoration-none {{ $stats['pending_requests'] > 0 ? '' : '' }}">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:46px;height:46px;border-radius:.75rem;background:{{ $stats['pending_requests'] > 0 ? '#ecfeff' : '#f8fafc' }};display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;color:{{ $stats['pending_requests'] > 0 ? '#0891b2' : '#94a3b8' }}">
                    <i class="bi bi-inbox"></i></div>
                <div><div class="fw-bold" style="font-size:1.6rem;line-height:1;color:{{ $stats['pending_requests'] > 0 ? '#0891b2' : '#1e293b' }}">{{ $stats['pending_requests'] }}</div>
                <div class="text-muted" style="font-size:.75rem">Permohonan Pending</div></div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-4 col-xl">
        <a href="{{ route('overtime.index') }}?status=pending" class="card h-100 text-decoration-none {{ $stats['pending_overtime'] > 0 ? 'border-warning border-opacity-50' : '' }}">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:46px;height:46px;border-radius:.75rem;background:{{ $stats['pending_overtime'] > 0 ? '#fffbeb' : '#f8fafc' }};display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;color:{{ $stats['pending_overtime'] > 0 ? '#d97706' : '#94a3b8' }}">
                    <i class="bi bi-clock-history"></i></div>
                <div><div class="fw-bold" style="font-size:1.6rem;line-height:1;color:{{ $stats['pending_overtime'] > 0 ? '#d97706' : '#1e293b' }}">{{ $stats['pending_overtime'] }}</div>
                <div class="text-muted" style="font-size:.75rem">Lembur Pending</div></div>
            </div>
        </a>
    </div>
</div>

{{-- Quick Actions --}}
<div class="row g-3 mb-4">
    <div class="col-12"><p class="fw-semibold text-muted text-uppercase mb-0" style="font-size:.7rem;letter-spacing:.07em">Aksi Cepat</p></div>
    @php
        $actions = [
            ['route' => route('payroll-slips.create'), 'bg' => '#eff6ff', 'color' => '#2563eb', 'icon' => 'bi-plus-circle-fill', 'label' => 'Buat Slip Gaji', 'sub' => 'Generate slip baru'],
            ['route' => route('employees.create'),    'bg' => '#f3f0ff', 'color' => '#7c3aed', 'icon' => 'bi-person-plus-fill', 'label' => 'Tambah Karyawan', 'sub' => 'Daftarkan karyawan baru'],
            ['route' => route('leaves.index'),        'bg' => '#eff6ff', 'color' => '#2563eb', 'icon' => 'bi-calendar-check',   'label' => 'Cuti Karyawan',  'sub' => ($stats['pending_leaves'] > 0 ? $stats['pending_leaves'].' menunggu' : 'Kelola cuti')],
            ['route' => route('internal-requests.index'), 'bg' => '#ecfeff', 'color' => '#0891b2', 'icon' => 'bi-inbox',       'label' => 'Permohonan',     'sub' => ($stats['pending_requests'] > 0 ? $stats['pending_requests'].' menunggu' : 'Kelola permohonan')],
            ['route' => route('reimbursements.index'),'bg' => '#fef2f2', 'color' => '#dc2626', 'icon' => 'bi-receipt',          'label' => 'Reimbursement',  'sub' => ($stats['pending_reimbursements'] > 0 ? $stats['pending_reimbursements'].' menunggu' : 'Kelola reimburse')],
            ['route' => route('overtime.index'),       'bg' => '#fffbeb', 'color' => '#d97706', 'icon' => 'bi-clock-history',    'label' => 'Lembur',         'sub' => ($stats['pending_overtime'] > 0 ? $stats['pending_overtime'].' menunggu' : 'Kelola lembur')],
            ['route' => route('companies.create'),    'bg' => '#f0fdf4', 'color' => '#16a34a', 'icon' => 'bi-building-add',     'label' => 'Tambah Perusahaan', 'sub' => 'Daftarkan perusahaan'],
        ];
    @endphp
    @foreach($actions as $act)
    {{-- 7 cards: 2/row mobile, 3/row sm, 4/row md, 7/row xl --}}
    <div class="col-6 col-sm-4 col-md-3 col-xl">
        <a href="{{ $act['route'] }}" class="action-card text-decoration-none">
            <div class="d-flex align-items-center gap-3">
                <div style="width:40px;height:40px;border-radius:.6rem;background:{{ $act['bg'] }};color:{{ $act['color'] }};display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0">
                    <i class="bi {{ $act['icon'] }}"></i></div>
                <div style="min-width:0">
                    <p class="fw-semibold mb-0 text-truncate" style="font-size:.85rem;color:#1e293b">{{ $act['label'] }}</p>
                    <p class="mb-0 text-truncate" style="font-size:.72rem;color:{{ str_contains($act['sub'], 'menunggu') ? '#dc2626' : '#94a3b8' }}">{{ $act['sub'] }}</p>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- Main Content --}}
<div class="row g-4">
    {{-- Recent Payroll Slips --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-clock-history me-2 text-primary"></i>Slip Gaji Terbaru</span>
                <a href="{{ route('payroll-slips.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            @if($recentSlips->isEmpty())
                <div class="text-center py-5" style="color:#94a3b8">
                    <i class="bi bi-inbox d-block mb-2" style="font-size:2rem;opacity:.3"></i>
                    Belum ada slip. <a href="{{ route('payroll-slips.create') }}" class="text-primary">Buat sekarang</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>No. Slip</th><th>Karyawan</th><th>Periode</th><th>Take Home Pay</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach($recentSlips as $slip)
                            <tr>
                                <td><span class="font-monospace text-muted" style="font-size:.78rem">{{ $slip->slip_number }}</span></td>
                                <td>
                                    <div class="fw-medium" style="font-size:.875rem">{{ $slip->employee->name }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $slip->company->name }}</div>
                                </td>
                                <td style="font-size:.85rem">{{ $slip->period_label }}</td>
                                <td class="fw-semibold" style="color:#16a34a;font-size:.875rem">Rp {{ number_format($slip->take_home_pay, 0, ',', '.') }}</td>
                                <td><span class="badge badge-pill {{ $slip->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $slip->status === 'published' ? 'Published' : 'Draft' }}</span></td>
                                <td><a href="{{ route('payroll-slips.show', $slip) }}" class="btn btn-sm btn-outline-secondary">Lihat</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Pending Items --}}
    <div class="col-lg-5 d-flex flex-column gap-4">

        {{-- Pending Cuti --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-calendar-check me-2 text-primary"></i>Cuti Pending</span>
                <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-outline-primary">Semua</a>
            </div>
            @if($pendingLeaves->isEmpty())
                <div class="text-center py-4" style="color:#94a3b8;font-size:.85rem">
                    <i class="bi bi-check-circle d-block mb-1" style="font-size:1.6rem;color:#86efac"></i>Tidak ada cuti pending.
                </div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($pendingLeaves as $lr)
                    <li class="list-group-item d-flex justify-content-between align-items-start gap-2 px-3 px-md-4 py-2">
                        <div style="min-width:0;flex:1 1 auto">
                            <div class="fw-medium text-truncate" style="font-size:.875rem;color:#1e293b">{{ $lr->employee->name }}</div>
                            <div class="text-truncate" style="font-size:.76rem;color:#64748b">
                                {{ $lr->leaveType->name ?? '-' }} &bull;
                                {{ $lr->start_date->format('d M') }}@if(!$lr->start_date->equalTo($lr->end_date))–{{ $lr->end_date->format('d M') }}@endif
                                &bull; <strong>{{ $lr->days_count }} hari</strong>
                            </div>
                        </div>
                        <a href="{{ route('leaves.show', $lr) }}" class="btn btn-primary btn-sm" style="font-size:.74rem;padding:.2rem .6rem;flex-shrink:0">Review</a>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Pending Permohonan --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-inbox me-2" style="color:#0891b2"></i>Permohonan Pending</span>
                <a href="{{ route('internal-requests.index') }}" class="btn btn-sm btn-outline-secondary">Semua</a>
            </div>
            @if($pendingRequests->isEmpty())
                <div class="text-center py-4" style="color:#94a3b8;font-size:.85rem">
                    <i class="bi bi-check-circle d-block mb-1" style="font-size:1.6rem;color:#86efac"></i>Tidak ada permohonan pending.
                </div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($pendingRequests as $req)
                    <li class="list-group-item d-flex justify-content-between align-items-start gap-2 px-3 px-md-4 py-2">
                        <div style="min-width:0;flex:1 1 auto">
                            <div class="fw-medium text-truncate" style="font-size:.875rem;color:#1e293b">{{ $req->employee->name ?? '-' }}</div>
                            <div class="text-truncate" style="font-size:.76rem;color:#64748b">{{ $req->type ?? '-' }} &bull; {{ $req->created_at->diffForHumans() }}</div>
                        </div>
                        <a href="{{ route('internal-requests.show', $req) }}" class="btn btn-outline-secondary btn-sm" style="font-size:.74rem;padding:.2rem .6rem;flex-shrink:0">Lihat</a>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Pending Reimbursements --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-receipt me-2 text-danger"></i>Reimbursement Pending</span>
                <a href="{{ route('reimbursements.index') }}?status=pending" class="btn btn-sm btn-outline-danger">Semua</a>
            </div>
            @if($pendingReimbursements->isEmpty())
                <div class="text-center py-4" style="color:#94a3b8;font-size:.85rem">
                    <i class="bi bi-check-circle d-block mb-1" style="font-size:1.6rem;color:#86efac"></i>Tidak ada reimbursement pending.
                </div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($pendingReimbursements as $r)
                    <li class="list-group-item d-flex justify-content-between align-items-start gap-2 px-3 px-md-4 py-2">
                        <div style="min-width:0;flex:1 1 auto">
                            <div class="fw-medium text-truncate" style="font-size:.875rem;color:#1e293b">{{ $r->employee->name }}</div>
                            <div class="text-truncate" style="font-size:.76rem;color:#64748b">
                                {{ Str::limit($r->title, 28) }} &bull;
                                <span style="color:#16a34a;font-weight:600">Rp {{ number_format($r->amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <a href="{{ route('reimbursements.show', $r) }}" class="btn btn-danger btn-sm" style="font-size:.74rem;padding:.2rem .6rem;flex-shrink:0">Review</a>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

{{-- Realtime Attendance --}}
<div class="card mt-4" id="attendance-card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span class="card-title">
            <i class="bi bi-person-check-fill me-2 text-success"></i>Kehadiran Hari Ini
            <span class="badge bg-success bg-opacity-10 text-success ms-2" id="att-badge">...</span>
        </span>
        <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3">
            <span class="text-muted d-none d-sm-flex align-items-center gap-1" style="font-size:.75rem">
                <span class="rounded-circle bg-success d-inline-block" id="att-dot" style="width:7px;height:7px;animation:pulse-dot 2s infinite"></span>
                Live &bull; diperbarui: <span id="att-updated">–</span>
            </span>
            <span class="text-muted d-flex d-sm-none align-items-center gap-1" style="font-size:.7rem">
                <span class="rounded-circle bg-success d-inline-block" style="width:7px;height:7px;animation:pulse-dot 2s infinite"></span>
                <span id="att-updated-mobile">–</span>
            </span>
            <button class="btn btn-sm btn-outline-secondary" onclick="loadAttendance()">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>
    <div id="att-body">
        <div class="text-center py-4 text-muted" style="font-size:.85rem">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>Memuat data...
        </div>
    </div>
</div>

@push('scripts')
<style>
@keyframes pulse-dot {
    0%,100%{opacity:1} 50%{opacity:.3}
}
</style>
<script>
function loadAttendance() {
    fetch('{{ route('attendance.today-json') }}')
        .then(r => r.json())
        .then(data => {
            document.getElementById('att-badge').textContent = data.present + ' / ' + data.total_active + ' hadir';
            document.getElementById('att-updated').textContent = data.last_updated;
            const mobileUpd = document.getElementById('att-updated-mobile');
            if (mobileUpd) mobileUpd.textContent = data.last_updated;

            if (data.rows.length === 0) {
                document.getElementById('att-body').innerHTML =
                    '<div class="text-center py-4" style="color:#94a3b8;font-size:.85rem">' +
                    '<i class="bi bi-person-x d-block mb-1" style="font-size:1.6rem"></i>Belum ada yang absen masuk hari ini.</div>';
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-hover align-middle mb-0">' +
                '<thead><tr>' +
                '<th style="width:36px"></th>' +
                '<th>Karyawan</th>' +
                '<th>Perusahaan</th>' +
                '<th>Jam Kerja</th>' +
                '<th>Masuk</th>' +
                '<th>Keluar</th>' +
                '<th>Durasi</th>' +
                '<th>Status</th>' +
                '</tr></thead><tbody>';

            data.rows.forEach(function(r, i) {
                const initials = r.employee_name.split(' ').slice(0,2).map(w=>w[0]).join('').toUpperCase();
                const colors = ['#2563eb','#7c3aed','#0891b2','#16a34a','#d97706','#dc2626'];
                const color  = colors[i % colors.length];

                // Late indicator
                let checkInCell = r.check_in || '–';
                if (r.check_in && r.late_minutes > 0) {
                    checkInCell = '<span style="color:#dc2626;font-weight:600">' + r.check_in + '</span>' +
                        ' <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:.67rem">+' + r.late_minutes + 'm</span>';
                } else if (r.check_in) {
                    checkInCell = '<span style="color:#16a34a;font-weight:600">' + r.check_in + '</span>' +
                        ' <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.67rem">Tepat</span>';
                }

                const workHours = (r.work_start && r.work_end)
                    ? '<span style="font-size:.78rem;color:#64748b">' + r.work_start + '–' + r.work_end + '</span>'
                    : '<span style="color:#cbd5e1">–</span>';

                const statusBadge = r.check_out
                    ? '<span class="badge bg-success bg-opacity-10 text-success" style="font-size:.72rem">Selesai</span>'
                    : (r.check_in
                        ? '<span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.72rem"><span class="me-1" style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#2563eb;animation:pulse-dot 2s infinite"></span>Hadir</span>'
                        : '<span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem">–</span>');

                html += '<tr>' +
                    '<td><div style="width:32px;height:32px;border-radius:50%;background:' + color + '20;color:' + color + ';display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700">' + initials + '</div></td>' +
                    '<td><div class="fw-medium" style="font-size:.875rem">' + r.employee_name + '</div></td>' +
                    '<td class="text-muted" style="font-size:.82rem">' + r.company + '</td>' +
                    '<td>' + workHours + '</td>' +
                    '<td>' + checkInCell + '</td>' +
                    '<td style="font-size:.875rem;color:#64748b">' + (r.check_out || '–') + '</td>' +
                    '<td style="font-size:.82rem;color:#64748b">' + (r.duration || '–') + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '</tr>';
            });

            html += '</tbody></table></div>';
            document.getElementById('att-body').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('att-body').innerHTML =
                '<div class="text-center py-3 text-danger" style="font-size:.82rem"><i class="bi bi-exclamation-circle me-1"></i>Gagal memuat data.</div>';
        });
}

loadAttendance();
setInterval(loadAttendance, 30000);
</script>
@endpush
@endsection
