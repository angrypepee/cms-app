@extends('layouts.app')
@section('title', 'Kontrak Kerja - ' . $employee->name)
@section('page-title', 'Kontrak Kerja')

@section('content')
@php
    [$contractLabel, $contractColor] = $employee->contractBadge();
    $requiredTypes = $documentTypes;
    $hasFilters = false;
@endphp

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:64px;height:64px">
                        <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Kontrak Kerja</div>
                        <h4 class="mb-0" style="font-size:1.25rem">{{ $employee->name }}</h4>
                        <div class="text-muted" style="font-size:.82rem">{{ $employee->employee_id }}</div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('contract-documents.create', ['employee_id' => $employee->id]) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Buat Dokumen Kontrak
                    </a>
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Edit Data Karyawan
                    </a>
                    <a href="{{ route('contract-documents.index', ['employee_id' => $employee->id]) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-folder2-open me-1"></i>Daftar Dokumen Kontrak
                    </a>
                    <a href="{{ route('kontrak-kerja.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
                    </a>
                </div>

                <hr>
                <div class="small text-muted mb-2">Status Kontrak</div>
                <span class="badge bg-{{ $contractColor }} bg-opacity-10 text-{{ $contractColor }}" style="font-size:.78rem">{{ $contractLabel }}</span>
                @if($employee->contractStatus() === 'expiring')
                    <div class="text-warning mt-2" style="font-size:.78rem">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Berakhir {{ $employee->contract_end?->diffForHumans() }}
                    </div>
                @elseif($employee->contractStatus() === 'expired')
                    <div class="text-danger mt-2" style="font-size:.78rem">
                        <i class="bi bi-x-circle-fill me-1"></i>Habis {{ $employee->contract_end?->isoFormat('D MMM YYYY') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-card-list me-2 text-primary"></i>Ringkasan Kontrak</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Perusahaan</div>
                            <div class="fw-semibold">{{ $employee->company->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Jabatan</div>
                            <div class="fw-semibold">{{ $employee->position ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Periode Kontrak</div>
                            <div class="fw-semibold">
                                {{ $employee->contract_start?->isoFormat('D MMM YYYY') ?? '-' }}
                                <span class="text-muted">s/d</span>
                                {{ $employee->contract_end?->isoFormat('D MMM YYYY') ?? 'Permanen' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Bank / Rekening</div>
                            <div class="fw-semibold">{{ $employee->bank_name ?? '-' }}</div>
                            <div class="text-muted" style="font-size:.82rem">{{ $employee->bank_account ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="card-title mb-0"><i class="bi bi-check2-square me-2 text-primary"></i>Checklist Dokumen Kontrak</span>
        <span class="text-muted" style="font-size:.82rem">{{ $employee->documents->count() }} dokumen tersimpan</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($requiredTypes as $type => $label)
                @php
                    $docs = $documentsByType->get($type, collect());
                    $latestDoc = $docs->first();
                @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div>
                                <div class="fw-semibold">{{ $label }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $docs->count() }} file</div>
                            </div>
                            @if($latestDoc)
                                <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.72rem">Tersedia</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:.72rem">Belum ada</span>
                            @endif
                        </div>
                        @if($latestDoc)
                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                @if($latestDoc->isViewable())
                                    <a href="{{ route('employee-documents.show', [$employee, $latestDoc]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Lihat
                                    </a>
                                @endif
                                <a href="{{ route('employee-documents.show', [$employee, $latestDoc]) }}?download=1" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download me-1"></i>Unduh
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="card-title mb-0"><i class="bi bi-folder2-open me-2 text-primary"></i>Dokumen Surat Kontrak Kerja</span>
        <span class="text-muted" style="font-size:.82rem">Upload KTP, NPWP, Ijazah, Rekening, dan syarat administrasi. Dokumen kontrak dikelola di menu Kontrak.</span>
    </div>
    <div class="card-body border-bottom bg-light py-3 px-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible py-2 mb-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2 mb-3" role="alert">
                Mohon periksa kembali input dokumen yang diunggah.
            </div>
        @endif
        <form method="POST" action="{{ route('employee-documents.store', $employee) }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-medium" style="font-size:.85rem">Jenis Dokumen <span class="text-danger">*</span></label>
                    <select name="document_type" class="form-select form-select-sm @error('document_type') is-invalid @enderror" required>
                        <option value="">-- Pilih Jenis --</option>
                        @foreach($documentTypes as $type => $label)
                            <option value="{{ $type }}" {{ old('document_type') === $type ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('document_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium" style="font-size:.85rem">Nama / Keterangan <span class="text-danger">*</span></label>
                    <input type="text" name="label" class="form-control form-control-sm @error('label') is-invalid @enderror"
                           value="{{ old('label') }}" placeholder="cth. KTP / NPWP / Ijazah" required>
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

    @if($employee->documents->isEmpty())
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-folder2-open fs-1 d-block mb-2 opacity-25"></i>
            Belum ada dokumen kontrak. Upload dokumen pertama dari form di atas.
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
                    @foreach($employee->documents as $doc)
                        <tr>
                            <td class="text-center"><i class="bi {{ $doc->typeIcon() }} text-primary fs-5"></i></td>
                            <td>
                                <span class="fw-medium" style="font-size:.88rem">{{ $doc->label }}</span>
                                <div class="text-muted" style="font-size:.75rem">{{ $doc->original_name }}</div>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem">{{ $doc->typeLabel() }}</span></td>
                            <td class="text-muted" style="font-size:.82rem">{{ $doc->fileSizeFormatted() }}</td>
                            <td class="text-muted" style="font-size:.8rem">{{ $doc->created_at->format('d M Y') }}<br><span style="font-size:.72rem">{{ $doc->uploader?->name ?? '-' }}</span></td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($doc->isViewable())
                                        @if($doc->isImage())
                                            <a href="{{ route('employee-documents.show', [$employee, $doc]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>Lihat
                                            </a>
                                        @else
                                            <a href="{{ route('employee-documents.show', [$employee, $doc]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>Lihat
                                            </a>
                                        @endif
                                    @endif
                                    <a href="{{ route('employee-documents.show', [$employee, $doc]) }}?download=1" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <form method="POST" action="{{ route('employee-documents.destroy', [$employee, $doc]) }}" onsubmit="return confirm('Hapus dokumen ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
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

<div class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="card-title mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Template Referensi Kontrak</span>
        <a href="{{ route('contract-documents.create', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Isi Template Ini
        </a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <div class="border rounded p-3 bg-light">
                    <strong>Surat Perjanjian Kerja</strong>
                    <div class="text-muted mt-1" style="font-size:.85rem">Nomor, tanggal, lokasi, pihak pertama, pihak kedua, ruang lingkup, jangka waktu, nilai kontrak, termin pembayaran, HKI, NDA, sanksi, dan penyelesaian sengketa dapat diisi melalui CRUD Dokumen Kontrak.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection