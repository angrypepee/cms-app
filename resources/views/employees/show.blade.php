@extends('layouts.app')
@section('title', $employee->name)
@section('page-title', $employee->name)
@section('content')
@php
    $totalGaji          = $employee->payrollSlips->where('status','published')->sum('take_home_pay');
    $totalApresiasi     = $employee->appreciationBudgets->flatMap(fn($b) => $b->claims)->where('status','approved')->sum('amount');
    $totalReimbursement = $employee->reimbursements->where('status','approved')->sum('amount');
    $grandTotal         = $totalGaji + $totalApresiasi + $totalReimbursement;
@endphp
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
                <div class="d-flex gap-2 justify-content-center mb-1">
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
                    <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('Hapus karyawan ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3 me-1"></i>Hapus</button>
                    </form>
                </div>
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
                                    <span class="badge bg-{{ $employee->employee_category->badgeColor() }} bg-opacity-10 text-{{ $employee->employee_category->badgeColor() }} badge-pill" style="font-size:.72rem">{{ $employee->employee_category->label() }}</span>
                                @else
                                    <span class="text-muted" style="font-size:.85rem">-</span>
                                @endif
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Gaji Pokok</div>
                            <div class="col-7 fw-semibold text-success" style="font-size:.85rem">Rp {{ number_format($employee->base_salary, 0, ',', '.') }}</div>
                        </div>
                    </li>
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
                    @if($employee->join_date)
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Bergabung</div>
                            <div class="col-7" style="font-size:.85rem">{{ $employee->join_date->isoFormat('D MMMM YYYY') }}</div>
                        </div>
                    </li>
                    @php
                        $now       = \Carbon\Carbon::now();
                        $joined    = $employee->join_date;
                        $years     = $joined->diffInYears($now);
                        $months    = $joined->copy()->addYears($years)->diffInMonths($now);
                        $masaKerja = $years > 0
                            ? $years . ' Tahun' . ($months > 0 ? ' ' . $months . ' Bulan' : '')
                            : ($months > 0 ? $months . ' Bulan' : 'Kurang dari 1 bulan');
                    @endphp
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Masa Kerja</div>
                            <div class="col-7">
                                <span class="fw-semibold" style="font-size:.85rem;color:#2563eb">{{ $masaKerja }}</span>
                            </div>
                        </div>
                    </li>
                    @endif
                    @if($employee->contract_start)
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Kontrak</div>
                            <div class="col-7" style="font-size:.82rem">
                                {{ $employee->contract_start->isoFormat('D MMM YYYY') }}
                                @if($employee->contract_end)
                                    <span class="text-muted mx-1">&rarr;</span>
                                    {{ $employee->contract_end->isoFormat('D MMM YYYY') }}
                                @else
                                    <span class="text-muted">&rarr; Permanen</span>
                                @endif
                            </div>
                        </div>
                    </li>
                    @php [$cbLabel, $cbColor] = $employee->contractBadge(); @endphp
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Status Kontrak</div>
                            <div class="col-7">
                                <span class="badge bg-{{ $cbColor }} bg-opacity-10 text-{{ $cbColor }}" style="font-size:.72rem">{{ $cbLabel }}</span>
                                @if($employee->contract_end && $employee->contractStatus() === 'expiring')
                                    <div class="text-warning" style="font-size:.72rem;margin-top:.2rem">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        Berakhir {{ $employee->contract_end->diffForHumans() }}
                                    </div>
                                @endif
                            </div>
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
                <span class="card-title"><i class="bi bi-wallet2 me-2 text-success"></i>Ringkasan Total Pembayaran</span>
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
                            <div class="text-muted" style="font-size:.75rem">{{ $employee->reimbursements->where('status','approved')->count() }} reimbursement disetujui</div>
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

    {{-- Documents --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title mb-0"><i class="bi bi-folder2-open me-2 text-primary"></i>Dokumen Pendukung</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#uploadDocForm" aria-expanded="false">
                    <i class="bi bi-upload me-1"></i>Upload Dokumen
                </button>
            </div>

            {{-- Upload Form --}}
            <div class="collapse" id="uploadDocForm">
                <div class="card-body border-bottom bg-light py-3 px-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible py-2 mb-3" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('employee-documents.store', $employee) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-medium" style="font-size:.85rem">Jenis Dokumen <span class="text-danger">*</span></label>
                                <select name="document_type" class="form-select form-select-sm @error('document_type') is-invalid @enderror" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    @foreach(\App\Models\EmployeeDocument::typeOptions() as $val => $label)
                                        <option value="{{ $val }}" {{ old('document_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('document_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium" style="font-size:.85rem">Nama / Keterangan <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control form-control-sm @error('label') is-invalid @enderror"
                                       value="{{ old('label') }}" placeholder="cth. KTP Budi Santoso" required>
                                @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium" style="font-size:.85rem">File <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control form-control-sm @error('file') is-invalid @enderror"
                                       accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" required>
                                <div class="form-text" style="font-size:.72rem">PDF, JPG, PNG, WEBP, DOC, DOCX, XLS, XLSX — maks. 10 MB</div>
                                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-upload"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Document List --}}
            @php $documents = $employee->documents; @endphp
            @if($documents->isEmpty())
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-folder2-open fs-1 d-block mb-2 opacity-25"></i>
                    Belum ada dokumen. Klik <strong>Upload Dokumen</strong> untuk menambahkan.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:36px"></th>
                                <th>Nama Dokumen</th>
                                <th>Jenis</th>
                                <th>Ukuran</th>
                                <th>Diunggah</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                            <tr>
                                <td class="text-center">
                                    <i class="bi {{ $doc->typeIcon() }} text-primary fs-5"></i>
                                </td>
                                <td>
                                    <span class="fw-medium" style="font-size:.88rem">{{ $doc->label }}</span>
                                    <div class="text-muted" style="font-size:.75rem">{{ $doc->original_name }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem">
                                        {{ $doc->typeLabel() }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size:.82rem">{{ $doc->fileSizeFormatted() }}</td>
                                <td class="text-muted" style="font-size:.8rem">
                                    {{ $doc->created_at->format('d M Y') }}<br>
                                    <span style="font-size:.72rem">{{ $doc->uploader?->name ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        @if($doc->isViewable())
                                            {{-- View in modal for images, new tab for PDF --}}
                                            @if($doc->isImage())
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#docViewModal"
                                                    data-doc-src="{{ route('employee-documents.show', [$employee, $doc]) }}"
                                                    data-doc-name="{{ $doc->label }}"
                                                    data-doc-type="image">
                                                    <i class="bi bi-eye me-1"></i>Lihat
                                                </button>
                                            @else
                                                <a href="{{ route('employee-documents.show', [$employee, $doc]) }}"
                                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye me-1"></i>Lihat
                                                </a>
                                            @endif
                                        @endif
                                        <a href="{{ route('employee-documents.show', [$employee, $doc]) }}?download=1"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form method="POST" action="{{ route('employee-documents.destroy', [$employee, $doc]) }}"
                                              onsubmit="return confirm('Hapus dokumen ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Payment History (Grouped Tabs) --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs px-3 pt-3" id="paymentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="payroll-tab" data-bs-toggle="tab" data-bs-target="#payroll-pane" type="button" role="tab">
                            <i class="bi bi-receipt-cutoff me-1"></i>Slip Gaji
                            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.72rem">{{ $employee->payrollSlips->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="apresiasi-tab" data-bs-toggle="tab" data-bs-target="#apresiasi-pane" type="button" role="tab">
                            <i class="bi bi-stars me-1"></i>Uang Apresiasi
                            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.72rem">{{ $employee->appreciationBudgets->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reimb-tab" data-bs-toggle="tab" data-bs-target="#reimb-pane" type="button" role="tab">
                            <i class="bi bi-receipt me-1"></i>Reimbursement
                            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.72rem">{{ $employee->reimbursements->count() }}</span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content" id="paymentTabsContent">

                {{-- Tab: Slip Gaji --}}
                <div class="tab-pane fade show active" id="payroll-pane" role="tabpanel">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                        <span class="text-muted" style="font-size:.85rem">
                            {{ $employee->payrollSlips->count() }} slip &mdash;
                            <span class="text-success fw-semibold">Rp {{ number_format($totalGaji, 0, ',', '.') }}</span> total published
                        </span>
                        <a href="{{ route('payroll-slips.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>Buat Slip
                        </a>
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
                                    @foreach($employee->payrollSlips->sortByDesc('created_at') as $slip)
                                    <tr>
                                        <td><span class="font-monospace text-muted" style="font-size:.78rem">{{ $slip->slip_number }}</span></td>
                                        <td>{{ $slip->period_label }}</td>
                                        <td class="fw-semibold text-success">Rp {{ number_format($slip->take_home_pay, 0, ',', '.') }}</td>
                                        <td><span class="badge badge-pill {{ $slip->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $slip->status === 'published' ? 'Published' : 'Draft' }}</span></td>
                                        <td><a href="{{ route('payroll-slips.show', $slip) }}" class="btn btn-sm btn-outline-secondary">Lihat</a></td>
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
                            {{ $employee->appreciationBudgets->count() }} anggaran &mdash;
                            <span class="text-warning fw-semibold">Rp {{ number_format($totalApresiasi, 0, ',', '.') }}</span> total klaim disetujui
                        </span>
                        <a href="{{ route('appreciation.create') }}?employee_id={{ $employee->id }}" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-plus-lg me-1"></i>Buat Anggaran
                        </a>
                    </div>
                    @if($employee->appreciationBudgets->isEmpty())
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
                                    @foreach($employee->appreciationBudgets->sortByDesc('year') as $budget)
                                    @php
                                        $used = $budget->claims->where('status','approved')->sum('amount');
                                        $remaining = $budget->total_amount - $used;
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $budget->year }}</td>
                                        <td>Rp {{ number_format($budget->total_amount, 0, ',', '.') }}</td>
                                        <td class="text-danger">Rp {{ number_format($used, 0, ',', '.') }}</td>
                                        <td class="{{ $remaining < 0 ? 'text-danger' : 'text-success' }} fw-semibold">
                                            Rp {{ number_format($remaining, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                {{ $budget->claims->count() }} klaim
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('appreciation.show', $budget) }}" class="btn btn-sm btn-outline-primary">
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
                            {{ $employee->reimbursements->count() }} pengajuan &mdash;
                            <span class="text-primary fw-semibold">Rp {{ number_format($totalReimbursement, 0, ',', '.') }}</span> total disetujui
                        </span>
                    </div>
                    @if($employee->reimbursements->isEmpty())
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
                                    @foreach($employee->reimbursements->sortByDesc('created_at') as $r)
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
                                            <a href="{{ route('reimbursements.show', $r) }}" class="btn btn-sm btn-outline-primary">
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

            </div>
        </div>
    </div>

    {{-- Image preview modal --}}
    <div class="modal fade" id="docViewModal" tabindex="-1" aria-labelledby="docViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="docViewModalLabel">Pratinjau Dokumen</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-2">
                    <img id="docViewImg" src="" alt="" class="img-fluid rounded" style="max-height:75vh">
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-open upload form if there are validation errors for it
    @if($errors->any())
        var uploadForm = document.getElementById('uploadDocForm');
        if (uploadForm) { new bootstrap.Collapse(uploadForm, { show: true }); }
    @endif

    // Image preview modal
    var docViewModal = document.getElementById('docViewModal');
    if (docViewModal) {
        docViewModal.addEventListener('show.bs.modal', function (event) {
            var btn   = event.relatedTarget;
            var src   = btn.getAttribute('data-doc-src');
            var name  = btn.getAttribute('data-doc-name');
            document.getElementById('docViewImg').src = src;
            document.getElementById('docViewModalLabel').textContent = name;
        });
        docViewModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('docViewImg').src = '';
        });
    }
});
</script>
@endpush
@endsection
