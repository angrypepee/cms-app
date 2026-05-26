@extends('layouts.app')
@section('title', 'Portal Karyawan')
@section('page-title', $employee->name)

@section('content')

{{-- Year Filter Bar --}}
<div class="card mb-3">
    <div class="card-body py-2 px-4 d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-funnel text-primary"></i>
            <span class="fw-semibold" style="font-size:.85rem;color:#1e293b">Periode Tahun</span>
            <span class="badge bg-primary-subtle text-primary" style="font-size:.7rem">{{ $year }}{{ $year == $currentYear ? ' · Sekarang' : '' }}</span>
        </div>
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="text-muted" style="font-size:.78rem">Tampilkan tahun:</label>
            <select name="year" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                @foreach($availableYears as $y)
                    <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endforeach
            </select>
            @if($year != $currentYear)
                <a href="{{ route('my.dashboard') }}" class="btn btn-sm btn-outline-secondary" title="Reset ke tahun ini">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </form>
    </div>
</div>

{{-- Quick Summary Strip --}}
@php
    $unread = auth()->user()->unreadNotifications()->count();
    $todayAttendance = \App\Models\Attendance::where('employee_id', $employee->id)
        ->where('date', now()->toDateString())
        ->first();
    $pendingOvertimeCount = \App\Models\OvertimeRequest::where('employee_id', $employee->id)
        ->where('status', 'pending')->count();
@endphp

{{-- Absensi Card --}}
<div class="card mb-4" style="border-left:4px solid {{ $todayAttendance?->check_in ? ($todayAttendance->check_out ? '#16a34a' : '#2563eb') : '#e2e8f0' }}">
    <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;border-radius:.75rem;background:{{ $todayAttendance?->check_in ? ($todayAttendance->check_out ? '#f0fdf4' : '#eff6ff') : '#f8fafc' }};display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;color:{{ $todayAttendance?->check_in ? ($todayAttendance->check_out ? '#16a34a' : '#2563eb') : '#94a3b8' }}">
                <i class="bi bi-{{ $todayAttendance?->check_in ? ($todayAttendance->check_out ? 'check-circle-fill' : 'door-open-fill') : 'clock' }}"></i>
            </div>
            <div>
                <div class="fw-semibold" style="font-size:.9rem;color:#1e293b">
                    Absensi Hari Ini &mdash; {{ now()->isoFormat('dddd, D MMM YYYY') }}
                </div>
                <div class="text-muted" style="font-size:.78rem">
                    @if($todayAttendance?->check_in)
                        Masuk: <strong>{{ substr($todayAttendance->check_in, 0, 5) }}</strong>
                        @if($todayAttendance->check_out)
                            &nbsp;&bull;&nbsp; Keluar: <strong>{{ substr($todayAttendance->check_out, 0, 5) }}</strong>
                            &nbsp;&bull;&nbsp; Durasi: <strong>{{ $todayAttendance->durationLabel() }}</strong>
                        @else
                            &nbsp;&bull;&nbsp; <span style="color:#2563eb">Sedang bekerja...</span>
                        @endif
                    @else
                        Belum absen masuk hari ini
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if(!$todayAttendance?->check_in)
                <form method="POST" action="{{ route('attendance.check-in') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Absen Masuk
                    </button>
                </form>
            @elseif(!$todayAttendance->check_out)
                <form method="POST" action="{{ route('attendance.check-out') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                        <i class="bi bi-box-arrow-right me-1"></i>Absen Keluar
                    </button>
                </form>
            @else
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2" style="font-size:.8rem"><i class="bi bi-check-circle me-1"></i>Selesai</span>
            @endif
            <a href="{{ route('my.overtime') }}" class="btn btn-outline-warning btn-sm px-3">
                <i class="bi bi-clock-history me-1"></i>Ajukan Lembur
                @if($pendingOvertimeCount > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $pendingOvertimeCount }}</span>
                @endif
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:42px;height:42px;border-radius:.65rem;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0;color:#2563eb"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <div class="fw-bold" style="font-size:1.5rem;line-height:1;color:#1e293b">{{ $approvedLeaveDays }}</div>
                    <div class="text-muted" style="font-size:.74rem">Hari Cuti {{ $year }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('my.leaves') }}" class="card h-100 text-decoration-none {{ $pendingLeavesCount > 0 ? 'border-warning border-opacity-50' : '' }}">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:42px;height:42px;border-radius:.65rem;background:{{ $pendingLeavesCount > 0 ? '#fffbeb' : '#f8fafc' }};display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0;color:{{ $pendingLeavesCount > 0 ? '#d97706' : '#94a3b8' }}"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="fw-bold" style="font-size:1.5rem;line-height:1;color:{{ $pendingLeavesCount > 0 ? '#d97706' : '#1e293b' }}">{{ $pendingLeavesCount }}</div>
                    <div class="text-muted" style="font-size:.74rem">Cuti Menunggu</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('my.requests') }}" class="card h-100 text-decoration-none {{ $pendingRequestsCount > 0 ? 'border-info border-opacity-50' : '' }}">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:42px;height:42px;border-radius:.65rem;background:{{ $pendingRequestsCount > 0 ? '#ecfeff' : '#f8fafc' }};display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0;color:{{ $pendingRequestsCount > 0 ? '#0891b2' : '#94a3b8' }}"><i class="bi bi-envelope-paper"></i></div>
                <div>
                    <div class="fw-bold" style="font-size:1.5rem;line-height:1;color:{{ $pendingRequestsCount > 0 ? '#0891b2' : '#1e293b' }}">{{ $pendingRequestsCount }}</div>
                    <div class="text-muted" style="font-size:.74rem">Permohonan Pending</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('notifications.index') }}" class="card h-100 text-decoration-none {{ $unread > 0 ? 'border-primary border-opacity-50' : '' }}">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div style="width:42px;height:42px;border-radius:.65rem;background:{{ $unread > 0 ? '#eff6ff' : '#f8fafc' }};display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0;color:{{ $unread > 0 ? '#2563eb' : '#94a3b8' }}"><i class="bi bi-bell{{ $unread > 0 ? '-fill' : '' }}"></i></div>
                <div>
                    <div class="fw-bold" style="font-size:1.5rem;line-height:1;color:{{ $unread > 0 ? '#2563eb' : '#1e293b' }}">{{ $unread }}</div>
                    <div class="text-muted" style="font-size:.74rem">Notif Belum Dibaca</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- Profile Card --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body p-4 text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" style="width:80px;height:80px">
                    <i class="bi bi-person-fill text-primary fs-2"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $employee->name }}</h5>
                <p class="text-muted mb-1" style="font-size:.85rem">{{ $employee->position ?? 'Karyawan' }}</p>
                <p class="text-muted mb-3" style="font-size:.8rem">{{ $employee->company->name ?? '-' }}</p>
            </div>
            <div class="card-footer bg-transparent p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">ID Karyawan</div>
                            <div class="col-7 fw-medium font-monospace" style="font-size:.82rem">{{ $employee->employee_id }}</div>
                        </div>
                    </li>
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Departemen</div>
                            <div class="col-7" style="font-size:.85rem">{{ $employee->department ?? '-' }}</div>
                        </div>
                    </li>
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Kategori</div>
                            <div class="col-7">
                                @if($employee->employee_category)
                                    <span class="badge bg-{{ $employee->employee_category->badgeColor() }} bg-opacity-10 text-{{ $employee->employee_category->badgeColor() }} badge-pill" style="font-size:.72rem">
                                        {{ $employee->employee_category->label() }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:.85rem">-</span>
                                @endif
                            </div>
                        </div>
                    </li>
                    @if($employee->grade)
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Grade</div>
                            <div class="col-7" style="font-size:.85rem">{{ $employee->grade }}</div>
                        </div>
                    </li>
                    @endif
                    @if($employee->email)
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Email</div>
                            <div class="col-7" style="font-size:.82rem">{{ $employee->email }}</div>
                        </div>
                    </li>
                    @endif
                    @if($employee->phone)
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Telepon</div>
                            <div class="col-7" style="font-size:.85rem">{{ $employee->phone }}</div>
                        </div>
                    </li>
                    @endif
                    @if($employee->bank_name || $employee->bank_account)
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Bank</div>
                            <div class="col-7" style="font-size:.85rem">{{ $employee->bank_name ?? '-' }}</div>
                        </div>
                    </li>
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">No. Rekening</div>
                            <div class="col-7 font-monospace" style="font-size:.82rem">{{ $employee->bank_account ?? '-' }}</div>
                        </div>
                    </li>
                    @endif
                    @if($employee->join_date)
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Bergabung</div>
                            <div class="col-7" style="font-size:.85rem">{{ $employee->join_date->format('d M Y') }}</div>
                        </div>
                    </li>
                    @endif
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Status</div>
                            <div class="col-7">
                                @if($employee->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.72rem">Aktif</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem">Nonaktif</span>
                                @endif
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Earnings Summary --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-wallet2 me-2 text-success"></i>Ringkasan Total Pembayaran <span class="text-muted fw-normal" style="font-size:.78rem">· Tahun {{ $year }}</span></span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted mb-1" style="font-size:.8rem"><i class="bi bi-receipt-cutoff me-1"></i>Total Gaji Diterima</div>
                            <div class="fw-bold text-success fs-5">Rp {{ number_format($totalGaji, 0, ',', '.') }}</div>
                            <div class="text-muted" style="font-size:.75rem">{{ $employee->payrollSlips->where('status','published')->count() }} slip published</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted mb-1" style="font-size:.8rem"><i class="bi bi-stars me-1"></i>Total Apresiasi Diterima</div>
                            <div class="fw-bold text-warning fs-5">Rp {{ number_format($totalApresiasi, 0, ',', '.') }}</div>
                            <div class="text-muted" style="font-size:.75rem">dari klaim apresiasi disetujui</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted mb-1" style="font-size:.8rem"><i class="bi bi-receipt me-1"></i>Total Reimbursement Disetujui</div>
                            <div class="fw-bold text-primary fs-5">Rp {{ number_format($totalReimbursement, 0, ',', '.') }}</div>
                            <div class="text-muted" style="font-size:.75rem">{{ $reimbursements->where('status','approved')->count() }} reimbursement disetujui</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="bg-success bg-opacity-10 border border-success border-opacity-25 rounded p-3 h-100">
                            <div class="text-muted mb-1" style="font-size:.8rem"><i class="bi bi-cash-stack me-1"></i>Grand Total Pembayaran</div>
                            <div class="fw-bold text-success fs-4">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
                            <div class="text-muted" style="font-size:.75rem">gaji + apresiasi + reimburse</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment History Tabs --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs px-3 pt-3" id="paymentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="slip-tab" data-bs-toggle="tab" data-bs-target="#slip-pane" type="button" role="tab">
                            <i class="bi bi-receipt-cutoff me-1"></i>Slip Gaji
                            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.72rem">{{ $employee->payrollSlips->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="apresiasi-tab" data-bs-toggle="tab" data-bs-target="#apresiasi-pane" type="button" role="tab">
                            <i class="bi bi-stars me-1"></i>Uang Apresiasi
                            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.72rem">{{ $budgets->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reimb-tab" data-bs-toggle="tab" data-bs-target="#reimb-pane" type="button" role="tab">
                            <i class="bi bi-receipt me-1"></i>Reimbursement
                            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.72rem">{{ $reimbursements->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="leave-tab" data-bs-toggle="tab" data-bs-target="#leave-pane" type="button" role="tab">
                            <i class="bi bi-calendar-check me-1"></i>Cuti &amp; Izin
                            @if($pendingLeavesCount > 0)
                            <span class="badge rounded-pill ms-1" style="background:#fef9c3;color:#a16207;font-size:.65rem;border:1px solid #fde047">{{ $pendingLeavesCount }}</span>
                            @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.72rem">{{ $leaveRequests->count() }}</span>
                            @endif
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content" id="paymentTabsContent">

                {{-- Tab: Slip Gaji --}}
                <div class="tab-pane fade show active" id="slip-pane" role="tabpanel">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                        <span class="text-muted" style="font-size:.85rem">
                            {{ $employee->payrollSlips->count() }} slip &mdash;
                            <span class="text-success fw-semibold">Rp {{ number_format($totalGaji, 0, ',', '.') }}</span> total diterima
                        </span>
                        <a href="{{ route('my.slips') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    @if($employee->payrollSlips->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 opacity-25"></i>Belum ada slip gaji.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>No. Slip</th>
                                        <th>Periode</th>
                                        <th>Take Home Pay</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employee->payrollSlips as $slip)
                                    <tr>
                                        <td><span class="font-monospace text-muted" style="font-size:.78rem">{{ $slip->slip_number }}</span></td>
                                        <td>{{ $slip->period_label }}</td>
                                        <td class="fw-semibold text-success">Rp {{ number_format($slip->take_home_pay, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge badge-pill {{ $slip->status === 'published' ? 'badge-published' : 'badge-draft' }}">
                                                {{ $slip->status === 'published' ? 'Published' : 'Draft' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($slip->status === 'published')
                                                <a href="{{ route('my.slips.show', $slip) }}" class="btn btn-sm btn-outline-secondary">Lihat</a>
                                            @else
                                                <span class="text-muted" style="font-size:.8rem">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Tab: Uang Apresiasi --}}
                <div class="tab-pane fade" id="apresiasi-pane" role="tabpanel">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                        <span class="text-muted" style="font-size:.85rem">
                            {{ $budgets->count() }} anggaran &mdash;
                            <span class="text-warning fw-semibold">Rp {{ number_format($totalApresiasi, 0, ',', '.') }}</span> total diterima
                        </span>
                        <a href="{{ route('my.appreciation') }}" class="btn btn-sm btn-outline-warning">Kelola Klaim</a>
                    </div>
                    @if($budgets->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-stars fs-1 d-block mb-2 opacity-25"></i>Belum ada anggaran apresiasi.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Tahun</th>
                                        <th>Total Anggaran</th>
                                        <th>Terpakai</th>
                                        <th>Sisa</th>
                                        <th>Klaim</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($budgets as $budget)
                                    @php
                                        $usedAmt      = $budget->claims->where('status','approved')->sum('amount');
                                        $remainingAmt = $budget->total_amount - $usedAmt;
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $budget->year }}</td>
                                        <td>Rp {{ number_format($budget->total_amount, 0, ',', '.') }}</td>
                                        <td class="text-danger">Rp {{ number_format($usedAmt, 0, ',', '.') }}</td>
                                        <td class="{{ $remainingAmt < 0 ? 'text-danger' : 'text-success' }} fw-semibold">
                                            Rp {{ number_format($remainingAmt, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                {{ $budget->claims->count() }} klaim
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('my.appreciation') }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Tab: Reimbursement --}}
                <div class="tab-pane fade" id="reimb-pane" role="tabpanel">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                        <span class="text-muted" style="font-size:.85rem">
                            {{ $reimbursements->count() }} pengajuan &mdash;
                            <span class="text-primary fw-semibold">Rp {{ number_format($totalReimbursement, 0, ',', '.') }}</span> total disetujui
                        </span>
                        <a href="{{ route('my.reimbursements') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    @if($reimbursements->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-receipt fs-1 d-block mb-2 opacity-25"></i>Belum ada riwayat reimbursement.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
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
                                    <tr>
                                        <td style="font-size:.88rem">{{ $r->title }}</td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem">
                                                {{ $r->categoryLabel() }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-success">Rp {{ number_format($r->amount, 0, ',', '.') }}</td>
                                        <td style="font-size:.85rem">{{ $r->approver?->name ?? '-' }}</td>
                                        <td><span class="badge badge-pill {{ $r->statusBadgeClass() }}">{{ $r->statusLabel() }}</span></td>
                                        <td class="text-muted" style="font-size:.82rem">{{ $r->created_at->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('my.reimbursements.show', $r) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Tab: Cuti & Izin --}}
                <div class="tab-pane fade" id="leave-pane" role="tabpanel">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                        <span class="text-muted" style="font-size:.85rem">
                            {{ $leaveRequests->count() }} pengajuan &mdash;
                            <span class="fw-semibold" style="color:#2563eb">{{ $approvedLeaveDays }} hari disetujui</span> tahun {{ $year }}
                        </span>
                        <a href="{{ route('my.leaves') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    @if($leaveRequests->isEmpty())
                        <div class="text-center py-5" style="color:#94a3b8">
                            <i class="bi bi-calendar-check d-block mb-2" style="font-size:2rem;opacity:.3"></i>
                            Belum ada pengajuan cuti.
                            <br><a href="{{ route('my.leaves') }}" class="text-primary">Ajukan sekarang</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Jenis Cuti</th>
                                        <th>Tanggal</th>
                                        <th>Durasi</th>
                                        <th>Status</th>
                                        <th>Diajukan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaveRequests as $lr)
                                    <tr>
                                        <td>
                                            <span class="fw-medium" style="font-size:.875rem">{{ $lr->leaveType->name ?? '-' }}</span>
                                            @if($lr->reason)<br><span class="text-muted" style="font-size:.76rem">{{ Str::limit($lr->reason, 40) }}</span>@endif
                                        </td>
                                        <td style="font-size:.85rem">
                                            {{ $lr->start_date->format('d M Y') }}
                                            @if(!$lr->start_date->equalTo($lr->end_date))
                                                <br><span class="text-muted" style="font-size:.78rem">&ndash; {{ $lr->end_date->format('d M Y') }}</span>
                                            @endif
                                        </td>
                                        <td style="font-size:.85rem">{{ $lr->days_count }} hari</td>
                                        <td><span class="badge badge-pill {{ $lr->statusBadgeClass() }}">{{ $lr->statusLabel() }}</span></td>
                                        <td class="text-muted" style="font-size:.8rem">{{ $lr->created_at->format('d M Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

</div>
@endsection
