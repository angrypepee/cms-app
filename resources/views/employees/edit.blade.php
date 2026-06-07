@extends('layouts.app')
@section('title', 'Edit Karyawan')
@section('page-title', 'Edit Karyawan')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit: {{ $employee->name }}</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('employees.update', $employee) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Perusahaan <span class="text-danger">*</span></label>
                            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Perusahaan --</option>
                                @foreach($companies as $co)
                                    <option value="{{ $co->id }}" {{ old('company_id', $employee->company_id) == $co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                                @endforeach
                            </select>
                            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">ID Karyawan</label>
                            <input type="text" class="form-control font-monospace bg-light" value="{{ $employee->employee_id }}" readonly disabled>
                            <div class="form-text" style="font-size:.72rem"><i class="bi bi-lock me-1"></i>ID karyawan dibuat otomatis oleh sistem dan tidak dapat diubah.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            @php $curPos = old('position', $employee->position); $hasPos = $positions->contains('name', $curPos); @endphp
                            <label class="form-label fw-medium">Jabatan</label>
                            <select name="position" class="form-select @error('position') is-invalid @enderror">
                                <option value="">— Pilih Jabatan —</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->name }}" {{ $curPos === $pos->name ? 'selected' : '' }}>{{ $pos->name }}</option>
                                @endforeach
                                @if($curPos && !$hasPos)
                                    <option value="{{ $curPos }}" selected>{{ $curPos }} (legacy)</option>
                                @endif
                            </select>
                            <small class="text-muted">Belum ada? <a href="{{ route('master-data.index', ['tab'=>'positions']) }}" target="_blank">Kelola di Data Master</a></small>
                            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            @php $curDept = old('department', $employee->department); $hasDept = $departments->contains('name', $curDept); @endphp
                            <label class="form-label fw-medium">Departemen</label>
                            <select name="department" class="form-select @error('department') is-invalid @enderror">
                                <option value="">— Pilih Departemen —</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->name }}" {{ $curDept === $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                                @if($curDept && !$hasDept)
                                    <option value="{{ $curDept }}" selected>{{ $curDept }} (legacy)</option>
                                @endif
                            </select>
                            <small class="text-muted">Belum ada? <a href="{{ route('master-data.index', ['tab'=>'departments']) }}" target="_blank">Kelola di Data Master</a></small>
                            @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Kategori Karyawan <span class="text-danger">*</span></label>
                            <select name="employee_category" class="form-select @error('employee_category') is-invalid @enderror" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->value }}" {{ old('employee_category', $employee->employee_category?->value) === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                                @endforeach
                            </select>
                            @error('employee_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Gaji Pokok</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill text-muted"></i></span>
                                <input type="text" class="form-control bg-light text-muted" value="Rp {{ $employee->base_salary ? number_format($employee->base_salary, 0, ',', '.') : 'Belum diatur' }}" readonly disabled>
                            </div>
                            <div class="form-text" style="font-size:.72rem"><i class="bi bi-info-circle me-1"></i>Gaji diatur dari <a href="{{ route('contract-documents.create', ['employee_id' => $employee->id]) }}">Kontrak Kerja</a>. Simpan kontrak untuk menyinkron nilai gaji.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Grade / Level</label>
                            <input type="text" name="grade" class="form-control @error('grade') is-invalid @enderror"
                                value="{{ old('grade', $employee->grade) }}" placeholder="Cth: G3, Senior, Junior">
                            @error('grade')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->email) }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">No. Telepon</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tanggal Bergabung</label>
                            <input type="date" name="join_date" class="form-control @error('join_date') is-invalid @enderror" value="{{ old('join_date', $employee->join_date?->format('Y-m-d')) }}">
                            @error('join_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Main Contract Document (source of truth) --}}
                        <div class="col-12 mt-2">
                            <p class="fw-semibold text-muted text-uppercase mb-2" style="font-size:.7rem;letter-spacing:.07em">Kontrak Kerja (Dokumen Utama)</p>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-3 bg-light">
                                @if($mainContractDocument)
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <div>
                                            <div class="fw-semibold">{{ $mainContractDocument->contract_number ?? '-' }}</div>
                                            <div class="text-muted" style="font-size:.8rem">
                                                Tanggal kontrak: {{ $mainContractDocument->contract_date?->isoFormat('D MMMM YYYY') ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('contract-documents.show', $mainContractDocument) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>Lihat Dokumen Utama
                                            </a>
                                            <a href="{{ route('contract-documents.index', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-list-ul me-1"></i>Daftar Kontrak
                                            </a>
                                        </div>
                                    </div>
                                    <div class="small text-muted" style="font-size:.78rem">
                                        Periode tersinkron otomatis dari dokumen kontrak utama:
                                        <strong>{{ $mainContractDocument->start_date?->isoFormat('D MMM YYYY') ?? '-' }}</strong>
                                        <span class="mx-1">s/d</span>
                                        <strong>{{ $mainContractDocument->end_date?->isoFormat('D MMM YYYY') ?? 'Permanen' }}</strong>.
                                    </div>
                                    @if($mainContractDocument->isSigned())
                                        <div class="mt-1 text-success" style="font-size:.78rem">
                                            <i class="bi bi-patch-check me-1"></i>Kontrak sudah ditandatangani.
                                        </div>
                                    @endif
                                @else
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="text-muted" style="font-size:.82rem">
                                            Belum ada dokumen kontrak utama. Buat kontrak dari menu Kontrak agar periode kerja otomatis sinkron ke data karyawan.
                                        </div>
                                        <a href="{{ route('contract-documents.create', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i>Buat Kontrak
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="isActive">Karyawan Aktif</label>
                            </div>
                        </div>

                        {{-- BPJS & Financial Data --}}
                        <div class="col-12 mt-2">
                            <p class="fw-semibold text-muted text-uppercase mb-2" style="font-size:.7rem;letter-spacing:.07em">BPJS &amp; Data Keuangan</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">No. BPJS Kesehatan</label>
                            <input type="text" name="bpjs_kesehatan" class="form-control @error('bpjs_kesehatan') is-invalid @enderror"
                                value="{{ old('bpjs_kesehatan', $employee->bpjs_kesehatan) }}"
                                placeholder="Kosongkan jika belum terdaftar">
                            @error('bpjs_kesehatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">No. BPJS Ketenagakerjaan</label>
                            <input type="text" name="bpjs_ketenagakerjaan" class="form-control @error('bpjs_ketenagakerjaan') is-invalid @enderror"
                                value="{{ old('bpjs_ketenagakerjaan', $employee->bpjs_ketenagakerjaan) }}"
                                placeholder="Kosongkan jika belum terdaftar">
                            @error('bpjs_ketenagakerjaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">NPWP</label>
                            <input type="text" name="npwp" class="form-control @error('npwp') is-invalid @enderror"
                                value="{{ old('npwp', $employee->npwp) }}"
                                placeholder="Nomor NPWP">
                            @error('npwp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nama Bank</label>
                            <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                                value="{{ old('bank_name', $employee->bank_name) }}"
                                placeholder="Contoh: BCA, Mandiri, BNI">
                            @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">No. Rekening</label>
                            <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror"
                                value="{{ old('bank_account', $employee->bank_account) }}"
                                placeholder="Nomor rekening bank">
                            @error('bank_account')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Links & Portfolio --}}
                        <div class="col-12 mt-2">
                            <p class="fw-semibold text-muted text-uppercase mb-2" style="font-size:.7rem;letter-spacing:.07em">
                                <i class="bi bi-link-45deg me-1"></i>Profil &amp; Tautan Profesional
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium"><i class="bi bi-github me-1"></i>GitHub</label>
                            <input type="url" name="github_url" class="form-control @error('github_url') is-invalid @enderror"
                                value="{{ old('github_url', $employee->github_url) }}"
                                placeholder="https://github.com/username">
                            @error('github_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium"><i class="bi bi-gitlab me-1"></i>GitLab</label>
                            <input type="url" name="gitlab_url" class="form-control @error('gitlab_url') is-invalid @enderror"
                                value="{{ old('gitlab_url', $employee->gitlab_url) }}"
                                placeholder="https://gitlab.com/username">
                            @error('gitlab_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium"><i class="bi bi-linkedin me-1"></i>LinkedIn</label>
                            <input type="url" name="linkedin_url" class="form-control @error('linkedin_url') is-invalid @enderror"
                                value="{{ old('linkedin_url', $employee->linkedin_url) }}"
                                placeholder="https://linkedin.com/in/username">
                            @error('linkedin_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium"><i class="bi bi-globe me-1"></i>Website / Portfolio</label>
                            <input type="url" name="portfolio_url" class="form-control @error('portfolio_url') is-invalid @enderror"
                                value="{{ old('portfolio_url', $employee->portfolio_url) }}"
                                placeholder="https://portofolio.com">
                            @error('portfolio_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 d-flex gap-2 justify-content-end pt-2">
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Login Account Card --}}
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-person-lock me-2 text-primary"></i>Akun Login Karyawan</span>
            </div>
            <div class="card-body px-4 py-4">
                @if($employee->hasLoginAccount())
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                            <i class="bi bi-person-check-fill text-success fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $employee->user->email }}</div>
                            <div class="text-muted" style="font-size:.82rem">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $employee->user->role->label() }}</span>
                                &nbsp;·&nbsp; Dibuat {{ $employee->user->created_at->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#resetEmpPassModal">
                            <i class="bi bi-key me-1"></i>Reset Password
                        </button>
                        <form method="POST" action="{{ route('employees.revoke-account', $employee) }}"
                              onsubmit="return confirm('Hapus akun login {{ $employee->name }}? Karyawan tidak akan bisa login lagi.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-person-x me-1"></i>Hapus Akun Login
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-muted mb-3" style="font-size:.9rem">
                        <i class="bi bi-info-circle me-1"></i>
                        Karyawan ini belum memiliki akun login. Buat akun agar karyawan dapat login dan melihat data mereka sendiri.
                    </p>
                    <form method="POST" action="{{ route('employees.create-account', $employee) }}" class="row g-3" style="max-width:480px">
                        @csrf
                        <div class="col-12">
                            <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="email@karyawan.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   minlength="8" required autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-plus me-1"></i>Buat Akun Login
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Dokumen Pendukung Card --}}
<div class="row justify-content-center mt-4">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-folder2-open me-2 text-primary"></i>Dokumen Pendukung</span>
            </div>

            {{-- Add form --}}
            <div class="card-body border-bottom bg-light py-3 px-4">
                @if(session('success') && !session('portfolio_success'))
                    <div class="alert alert-success alert-dismissible py-2 mb-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="d-flex gap-3 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="docTypeFile" onclick="switchDocType('file')">
                        <i class="bi bi-upload me-1"></i>Upload File
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="docTypeLink" onclick="switchDocType('link')">
                        <i class="bi bi-link-45deg me-1"></i>Tambah Link
                    </button>
                </div>
                <form method="POST" action="{{ route('employee-documents.store', $employee) }}" enctype="multipart/form-data" id="docAddForm">
                    @csrf
                    <input type="hidden" name="entry_type" id="docEntryType" value="file">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-medium" style="font-size:.85rem">Jenis <span class="text-danger">*</span></label>
                            <select name="document_type" class="form-select form-select-sm @error('document_type') is-invalid @enderror" required>
                                <option value="">-- Pilih --</option>
                                @foreach(\App\Models\EmployeeDocument::typeOptions() as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('document_type') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium" style="font-size:.85rem">Nama / Keterangan <span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control form-control-sm @error('label') is-invalid @enderror"
                                value="{{ old('label') }}" placeholder="cth. KTP 2026" required>
                        </div>
                        <div class="col-md-4" id="docFileField">
                            <label class="form-label fw-medium" style="font-size:.85rem">File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control form-control-sm @error('file') is-invalid @enderror"
                                accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx">
                            <div class="form-text" style="font-size:.7rem">PDF, JPG, PNG, DOC, DOCX, XLS, XLSX — maks. 10 MB</div>
                        </div>
                        <div class="col-md-4 d-none" id="docLinkField">
                            <label class="form-label fw-medium" style="font-size:.85rem">URL <span class="text-danger">*</span></label>
                            <input type="url" name="url" class="form-control form-control-sm @error('url') is-invalid @enderror"
                                value="{{ old('url') }}" placeholder="https://drive.google.com/...">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Document list --}}
            @if($employee->documents->isEmpty())
                <div class="card-body text-center py-4 text-muted" style="font-size:.85rem">
                    <i class="bi bi-folder2-open fs-3 d-block mb-1 opacity-25"></i>Belum ada dokumen pendukung.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.85rem">
                    <thead class="table-light">
                        <tr><th>Jenis</th><th>Nama</th><th>Tipe</th><th>Ukuran</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($employee->documents as $doc)
                        <tr>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem">{{ $doc->typeLabel() }}</span></td>
                            <td class="fw-medium">{{ $doc->label }}</td>
                            <td>
                                @if($doc->url)
                                    <span class="badge bg-info bg-opacity-10 text-info" style="font-size:.7rem"><i class="bi bi-link-45deg me-1"></i>Link</span>
                                @else
                                    <span class="badge bg-light text-muted" style="font-size:.7rem"><i class="bi bi-file-earmark me-1"></i>File</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $doc->url ? '—' : $doc->fileSizeFormatted() }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($doc->url)
                                        <a href="{{ $doc->url }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i></a>
                                    @elseif($doc->isViewable())
                                        <a href="{{ route('employee-documents.show', [$employee, $doc]) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                    @endif
                                    @if(!$doc->url)
                                        <a href="{{ route('employee-documents.show', [$employee, $doc]) }}?download=1" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                                    @endif
                                    <form method="POST" action="{{ route('employee-documents.destroy', [$employee, $doc]) }}" onsubmit="return confirm('Hapus dokumen ini?')">
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
            @endif
        </div>
    </div>
</div>

{{-- Portfolio Card --}}
<div class="row justify-content-center mt-4">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-person-badge me-2 text-primary"></i>Portfolio &amp; CV</span>
            </div>

            <div class="card-body border-bottom bg-light py-3 px-4">
                @if(session('portfolio_success'))
                    <div class="alert alert-success alert-dismissible py-2 mb-3" role="alert">
                        {{ session('portfolio_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="d-flex gap-3 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="pfTypeFile" onclick="switchPfType('file')">
                        <i class="bi bi-upload me-1"></i>Upload File
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="pfTypeLink" onclick="switchPfType('link')">
                        <i class="bi bi-link-45deg me-1"></i>Tambah Link
                    </button>
                </div>
                <form method="POST" action="{{ route('employee-portfolios.store', $employee) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="entry_type" id="pfEntryType" value="file">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-medium" style="font-size:.85rem">Nama / Keterangan <span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control form-control-sm"
                                placeholder="cth. CV 2026, GitHub, Sertifikat AWS" required>
                        </div>
                        <div class="col-md-6" id="pfFileField">
                            <label class="form-label fw-medium" style="font-size:.85rem">File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control form-control-sm"
                                accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.zip">
                            <div class="form-text" style="font-size:.7rem">PDF, JPG, PNG, DOC, DOCX, ZIP — maks. 20 MB</div>
                        </div>
                        <div class="col-md-6 d-none" id="pfLinkField">
                            <label class="form-label fw-medium" style="font-size:.85rem">URL <span class="text-danger">*</span></label>
                            <input type="url" name="url" class="form-control form-control-sm"
                                placeholder="https://github.com/username, https://behance.net/...">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            @if($employee->portfolios->isEmpty())
                <div class="card-body text-center py-4 text-muted" style="font-size:.85rem">
                    <i class="bi bi-person-badge fs-3 d-block mb-1 opacity-25"></i>Belum ada portfolio.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.85rem">
                    <thead class="table-light">
                        <tr><th>Nama</th><th>Tipe</th><th>Ukuran</th><th>Diunggah</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($employee->portfolios as $pf)
                        <tr>
                            <td class="fw-medium">{{ $pf->label }}</td>
                            <td>
                                @if($pf->url)
                                    <span class="badge bg-info bg-opacity-10 text-info" style="font-size:.7rem"><i class="bi bi-link-45deg me-1"></i>Link</span>
                                @else
                                    <span class="badge bg-light text-muted" style="font-size:.7rem"><i class="bi bi-file-earmark me-1"></i>{{ strtoupper(pathinfo($pf->original_name ?? '', PATHINFO_EXTENSION)) ?: 'File' }}</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $pf->url ? '—' : $pf->fileSizeFormatted() }}</td>
                            <td class="text-muted" style="font-size:.8rem">{{ $pf->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($pf->url)
                                        <a href="{{ $pf->url }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i></a>
                                    @elseif($pf->isViewable())
                                        <a href="{{ route('employee-portfolios.show', [$employee, $pf]) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                    @endif
                                    @if(!$pf->url)
                                        <a href="{{ route('employee-portfolios.show', [$employee, $pf]) }}?download=1" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                                    @endif
                                    <form method="POST" action="{{ route('employee-portfolios.destroy', [$employee, $pf]) }}" onsubmit="return confirm('Hapus portfolio ini?')">
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
            @endif
        </div>
    </div>
</div>

{{-- Reset Password Modal --}}
@if($employee->hasLoginAccount())
<div class="modal fade" id="resetEmpPassModal" tabindex="-1" aria-labelledby="resetEmpPassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetEmpPassModalLabel">
                    <i class="bi bi-key me-2 text-warning"></i>Reset Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('users.reset-password', $employee->user) }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p class="text-muted mb-3" style="font-size:.88rem">
                        Reset password untuk <strong>{{ $employee->name }}</strong> ({{ $employee->user->email }}).
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" minlength="8" required autocomplete="new-password">
                        @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label fw-medium">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-key me-1"></i>Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div id="resetPassModalFlag" data-open="{{ ($errors->has('new_password') || $errors->has('new_password_confirmation')) ? '1' : '0' }}" class="d-none"></div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var resetPassModalFlag = document.getElementById('resetPassModalFlag');
    var shouldOpenResetModal = resetPassModalFlag && resetPassModalFlag.dataset.open === '1';
    if (shouldOpenResetModal) {
        var modal = document.getElementById('resetEmpPassModal');
        if (modal) { new bootstrap.Modal(modal).show(); }
    }
});

function switchDocType(type) {
    document.getElementById('docEntryType').value = type;
    var fileField = document.getElementById('docFileField');
    var linkField = document.getElementById('docLinkField');
    var btnFile   = document.getElementById('docTypeFile');
    var btnLink   = document.getElementById('docTypeLink');
    if (type === 'file') {
        fileField.classList.remove('d-none'); linkField.classList.add('d-none');
        btnFile.classList.replace('btn-outline-secondary', 'btn-outline-primary');
        btnLink.classList.replace('btn-outline-primary', 'btn-outline-secondary');
        fileField.querySelector('input[type=file]').required = true;
        linkField.querySelector('input[type=url]').required  = false;
    } else {
        linkField.classList.remove('d-none'); fileField.classList.add('d-none');
        btnLink.classList.replace('btn-outline-secondary', 'btn-outline-primary');
        btnFile.classList.replace('btn-outline-primary', 'btn-outline-secondary');
        linkField.querySelector('input[type=url]').required  = true;
        fileField.querySelector('input[type=file]').required = false;
    }
}

function switchPfType(type) {
    document.getElementById('pfEntryType').value = type;
    var fileField = document.getElementById('pfFileField');
    var linkField = document.getElementById('pfLinkField');
    var btnFile   = document.getElementById('pfTypeFile');
    var btnLink   = document.getElementById('pfTypeLink');
    if (type === 'file') {
        fileField.classList.remove('d-none'); linkField.classList.add('d-none');
        btnFile.classList.replace('btn-outline-secondary', 'btn-outline-primary');
        btnLink.classList.replace('btn-outline-primary', 'btn-outline-secondary');
        fileField.querySelector('input[type=file]').required = true;
        linkField.querySelector('input[type=url]').required  = false;
    } else {
        linkField.classList.remove('d-none'); fileField.classList.add('d-none');
        btnLink.classList.replace('btn-outline-secondary', 'btn-outline-primary');
        btnFile.classList.replace('btn-outline-primary', 'btn-outline-secondary');
        linkField.querySelector('input[type=url]').required  = true;
        fileField.querySelector('input[type=file]').required = false;
    }
}
</script>
@endpush
@endsection
