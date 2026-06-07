@extends('layouts.app')
@section('title', $employee->name)
@section('page-title', $employee->name)
@section('content')
@php
    $isAdmin = auth()->user()->isAdmin();
    $totalGaji          = $isAdmin ? $employee->payrollSlips->where('status','published')->sum('take_home_pay') : 0;
    $totalApresiasi     = $isAdmin ? $employee->appreciationBudgets->flatMap(fn($b) => $b->claims)->where('status','approved')->sum('amount') : 0;
    $totalReimbursement = $isAdmin ? $employee->reimbursements->where('status','approved')->sum('amount') : 0;
    $grandTotal         = $totalGaji + $totalApresiasi + $totalReimbursement;
    $initials = collect(explode(' ', $employee->name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
    $colors   = ['#2563eb','#7c3aed','#0891b2','#16a34a','#d97706','#dc2626','#0d9488'];
    $bgColor  = $colors[crc32($employee->employee_id) % count($colors)];
@endphp
<div class="row g-4">
    {{-- Profile Card --}}
    <div class="col-lg-4">
        <div class="card">
            {{-- Header gradient --}}
            <div style="background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 60%,#2563eb 100%);padding:1.5rem;color:#fff;border-radius:.5rem .5rem 0 0;position:relative;overflow:hidden">
                <div style="position:absolute;right:-30px;top:-30px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.07)"></div>
                <div class="d-flex align-items-center gap-3 position-relative" style="z-index:1">
                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;color:#fff;flex-shrink:0;border:2px solid rgba(255,255,255,.3)">{{ $initials }}</div>
                    <div>
                        <div style="font-size:1.05rem;font-weight:700;line-height:1.2">{{ $employee->name }}</div>
                        <div style="font-size:.78rem;opacity:.75;margin-top:.15rem">{{ $employee->position ?? 'Karyawan' }}</div>
                        <div style="font-size:.72rem;opacity:.6">{{ $employee->company->name ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="card-body text-center py-3 px-4">
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
                    <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('Hapus karyawan ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3 me-1"></i>Hapus</button>
                    </form>
                </div>
                {{-- Social links --}}
                @if($employee->github_url || $employee->gitlab_url || $employee->linkedin_url || $employee->portfolio_url)
                <div class="d-flex justify-content-center gap-2 mt-3 flex-wrap">
                    @if($employee->github_url)
                        <a href="{{ $employee->github_url }}" target="_blank" class="btn btn-sm btn-outline-dark"><i class="bi bi-github"></i></a>
                    @endif
                    @if($employee->gitlab_url)
                        <a href="{{ $employee->gitlab_url }}" target="_blank" class="btn btn-sm" style="border:1px solid #fc6d26;color:#fc6d26"><i class="bi bi-gitlab"></i></a>
                    @endif
                    @if($employee->linkedin_url)
                        <a href="{{ $employee->linkedin_url }}" target="_blank" class="btn btn-sm" style="border:1px solid #0a66c2;color:#0a66c2"><i class="bi bi-linkedin"></i></a>
                    @endif
                    @if($employee->portfolio_url)
                        <a href="{{ $employee->portfolio_url }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-globe"></i></a>
                    @endif
                </div>
                @endif
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
                    @if($isAdmin)
                    <li class="list-group-item px-4 py-3">
                        <div class="row g-0">
                            <div class="col-5 text-muted" style="font-size:.8rem">Gaji Pokok</div>
                            <div class="col-7 fw-semibold text-success" style="font-size:.85rem">Rp {{ number_format($employee->base_salary, 0, ',', '.') }}</div>
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
    {{-- Earnings Summary — admin only --}}
    @if($isAdmin)
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
    @else
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body p-4 d-flex flex-column gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px">
                        <i class="bi bi-person-badge-fill text-primary fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $employee->name }}</div>
                        <div class="text-muted" style="font-size:.85rem">{{ $employee->position ?? '-' }} · {{ $employee->department ?? '-' }}</div>
                        <div class="text-muted" style="font-size:.8rem">{{ $employee->company->name ?? '-' }}</div>
                    </div>
                </div>
                <div class="row g-2" style="font-size:.85rem">
                    @if($employee->grade)<div class="col-6"><span class="text-muted">Grade:</span> <strong>{{ $employee->grade }}</strong></div>@endif
                    @if($employee->employee_category)<div class="col-6"><span class="text-muted">Kategori:</span> <span class="badge bg-{{ $employee->employee_category->badgeColor() }} bg-opacity-10 text-{{ $employee->employee_category->badgeColor() }}">{{ $employee->employee_category->label() }}</span></div>@endif
                    @if($employee->contract_start)<div class="col-12"><span class="text-muted">Periode Kontrak:</span> <strong>{{ $employee->contract_start->isoFormat('D MMM YYYY') }}</strong><span class="text-muted mx-1">→</span><strong>{{ $employee->contract_end?->isoFormat('D MMM YYYY') ?? 'Permanen' }}</strong></div>@endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Contract Details --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title mb-0"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Contract Details</span>
                <div class="d-flex gap-2">
                    <a href="{{ route('contract-documents.index', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-list-ul me-1"></i>Daftar Kontrak
                    </a>
                    <a href="{{ route('contract-documents.create', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Kontrak Baru
                    </a>
                </div>
            </div>

            @if($employee->contractDocuments->isEmpty())
                <div class="card-body text-center py-4 text-muted">
                    <i class="bi bi-file-earmark-x fs-3 d-block mb-2 opacity-50"></i>
                    Belum ada kontrak kerja untuk karyawan ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nomor Kontrak</th>
                                <th>Tanggal</th>
                                <th>Nilai</th>
                                <th>Dibuat Oleh</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employee->contractDocuments as $contract)
                                <tr>
                                    <td class="fw-semibold">{{ $contract->contract_number }}</td>
                                    <td style="font-size:.82rem">{{ $contract->contract_date?->isoFormat('D MMM YYYY') ?? '-' }}</td>
                                    <td style="font-size:.82rem">{{ $contract->contract_value ? 'Rp ' . number_format($contract->contract_value, 0, ',', '.') : '-' }}</td>
                                    <td style="font-size:.82rem">{{ $contract->creator?->name ?? '-' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('contract-documents.show', $contract) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                                        <a href="{{ route('contract-documents.edit', $contract) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
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

    {{-- Links & Portfolio --}}
    @if($employee->github_url || $employee->gitlab_url || $employee->linkedin_url || $employee->portfolio_url || $employee->portfolios->isNotEmpty())
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title mb-0"><i class="bi bi-link-45deg me-2 text-primary"></i>Profil Profesional &amp; Portfolio</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#uploadPortfolioForm" aria-expanded="false">
                    <i class="bi bi-upload me-1"></i>Upload Portfolio
                </button>
            </div>

            {{-- Links --}}
            @if($employee->github_url || $employee->gitlab_url || $employee->linkedin_url || $employee->portfolio_url)
            <div class="card-body pb-0 pt-3">
                <div class="d-flex flex-wrap gap-2">
                    @if($employee->github_url)
                        <a href="{{ $employee->github_url }}" target="_blank" class="btn btn-outline-dark btn-sm">
                            <i class="bi bi-github me-1"></i>GitHub
                        </a>
                    @endif
                    @if($employee->gitlab_url)
                        <a href="{{ $employee->gitlab_url }}" target="_blank" class="btn btn-sm" style="border:1px solid #fc6d26;color:#fc6d26">
                            <i class="bi bi-gitlab me-1"></i>GitLab
                        </a>
                    @endif
                    @if($employee->linkedin_url)
                        <a href="{{ $employee->linkedin_url }}" target="_blank" class="btn btn-sm" style="border:1px solid #0a66c2;color:#0a66c2">
                            <i class="bi bi-linkedin me-1"></i>LinkedIn
                        </a>
                    @endif
                    @if($employee->portfolio_url)
                        <a href="{{ $employee->portfolio_url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-globe me-1"></i>Website / Portfolio
                        </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Upload form --}}
            <div class="collapse" id="uploadPortfolioForm">
                <div class="card-body border-top bg-light py-3 px-4">
                    @if(session('portfolio_success'))
                        <div class="alert alert-success alert-dismissible py-2 mb-3" role="alert">
                            {{ session('portfolio_success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('employee-portfolios.store', $employee) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-medium" style="font-size:.85rem">Nama / Keterangan <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control form-control-sm @error('label') is-invalid @enderror"
                                    value="{{ old('label') }}" placeholder="cth. CV 2026, Sertifikat AWS, Project X" required>
                                @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium" style="font-size:.85rem">File <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control form-control-sm @error('file') is-invalid @enderror"
                                    accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.zip" required>
                                <div class="form-text" style="font-size:.72rem">PDF, JPG, PNG, DOC, DOCX, ZIP — maks. 20 MB</div>
                                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-upload"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Portfolio list --}}
            @if($employee->portfolios->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama Portfolio</th>
                            <th>Ukuran</th>
                            <th>Diunggah</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee->portfolios as $pf)
                        <tr>
                            <td>
                                <i class="bi bi-file-earmark{{ $pf->isPdf() ? '-pdf text-danger' : ($pf->isImage() ? '-image text-info' : '') }} me-2"></i>
                                <span class="fw-medium" style="font-size:.88rem">{{ $pf->label }}</span>
                                <div class="text-muted" style="font-size:.75rem">{{ $pf->original_name }}</div>
                            </td>
                            <td class="text-muted" style="font-size:.82rem">{{ $pf->fileSizeFormatted() }}</td>
                            <td class="text-muted" style="font-size:.8rem">
                                {{ $pf->created_at->format('d M Y') }}<br>
                                <span style="font-size:.72rem">{{ $pf->uploader?->name ?? '-' }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($pf->isViewable())
                                        <a href="{{ route('employee-portfolios.show', [$employee, $pf]) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                    @endif
                                    <a href="{{ route('employee-portfolios.show', [$employee, $pf]) }}?download=1" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                                    <form method="POST" action="{{ route('employee-portfolios.destroy', [$employee, $pf]) }}"
                                        onsubmit="return confirm('Hapus portfolio ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body text-center py-4 text-muted" style="font-size:.85rem">
                <i class="bi bi-folder2-open d-block mb-1 fs-3 opacity-25"></i>
                Belum ada file portfolio. Klik <strong>Upload Portfolio</strong> untuk menambahkan.
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Projects --}}
    @if($employee->projects->isNotEmpty())
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title mb-0"><i class="bi bi-kanban me-2 text-primary"></i>Project yang Diikuti</span>
                <a href="{{ route('project-plan.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.85rem">
                    <thead class="table-light">
                        <tr>
                            <th>Project</th>
                            <th>Klien</th>
                            <th>Peran</th>
                            <th>Bergabung</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee->projects as $proj)
                        @php [$statusLabel, $statusColor] = $proj->statusBadge(); @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $proj->name }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $proj->code }}</div>
                            </td>
                            <td class="text-muted">{{ $proj->client->name ?? '—' }}</td>
                            <td>
                                @if($proj->pivot->role)
                                    <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.75rem">{{ $proj->pivot->role }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                @php
                                    $joinedAt = $proj->pivot->joined_at;
                                    echo $joinedAt ? (\Carbon\Carbon::parse($joinedAt)->isoFormat('D MMM YYYY')) : '—';
                                @endphp
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }}" style="font-size:.72rem">{{ $statusLabel }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('project-plan.show', $proj) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

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
