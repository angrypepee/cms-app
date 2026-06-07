@extends('layouts.app')
@section('title', $project->code)

@section('content')
@php [$lbl,$clr] = $project->statusBadge(); @endphp

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('projects.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Daftar Project</a>
        <h4 class="mb-0 mt-1" style="font-size:1.2rem;font-weight:700">
            <i class="bi bi-kanban me-2 text-primary"></i>
            <span class="font-monospace">{{ $project->code }}</span>
            <span class="badge bg-{{ $clr }} bg-opacity-10 text-{{ $clr }} ms-2" style="font-size:.72rem">{{ $lbl }}</span>
        </h4>
        <div class="text-muted" style="font-size:.85rem">{{ $project->name }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('quotations.create', ['project_id'=>$project->id,'client_id'=>$project->client_id]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-plus"></i> Quotation</a>
        <a href="{{ route('invoices.create', ['project_id'=>$project->id,'client_id'=>$project->client_id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-receipt"></i> Invoice</a>
        <a href="{{ route('projects.edit',$project) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success py-2 small">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2 small">{{ session('error') }}</div>@endif

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <div class="card h-100"><div class="card-body">
            <h6 class="text-muted text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.06em">Detail Project</h6>
            <dl class="row mb-0 small">
                <dt class="col-sm-3 text-muted fw-normal">Klien</dt>
                <dd class="col-sm-9"><a href="{{ route('clients.show',$project->client) }}">{{ $project->client->name }}</a></dd>
                <dt class="col-sm-3 text-muted fw-normal">Penerbit</dt>
                <dd class="col-sm-9">{{ $project->company->name ?? '-' }}</dd>
                <dt class="col-sm-3 text-muted fw-normal">Periode</dt>
                <dd class="col-sm-9">{{ $project->start_date?->isoFormat('D MMM YYYY') ?? '-' }} → {{ $project->end_date?->isoFormat('D MMM YYYY') ?? '-' }}</dd>
                <dt class="col-sm-3 text-muted fw-normal">Budget</dt>
                <dd class="col-sm-9 font-monospace">Rp {{ number_format($project->budget,0,',','.') }}</dd>
                @if($project->description)
                <dt class="col-sm-3 text-muted fw-normal">Deskripsi</dt>
                <dd class="col-sm-9" style="white-space:pre-line">{{ $project->description }}</dd>
                @endif
                @if($project->notes)
                <dt class="col-sm-3 text-muted fw-normal">Catatan</dt>
                <dd class="col-sm-9 text-muted" style="white-space:pre-line">{{ $project->notes }}</dd>
                @endif
            </dl>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <h6 class="text-muted text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.06em">Ringkasan Finansial</h6>
            @php
                $totalQuoted = $project->quotations->sum('total');
                $totalInvoiced = $project->invoices->sum('total');
                $totalPaid = $project->invoices->sum('paid_amount');
            @endphp
            <div class="d-flex justify-content-between small py-1"><span class="text-muted">Total Quotation</span><strong class="font-monospace">Rp {{ number_format($totalQuoted,0,',','.') }}</strong></div>
            <div class="d-flex justify-content-between small py-1"><span class="text-muted">Total Invoice</span><strong class="font-monospace">Rp {{ number_format($totalInvoiced,0,',','.') }}</strong></div>
            <div class="d-flex justify-content-between small py-1 border-top mt-1 pt-2"><span class="text-success">Dibayar</span><strong class="font-monospace text-success">Rp {{ number_format($totalPaid,0,',','.') }}</strong></div>
            <div class="d-flex justify-content-between small py-1"><span class="text-warning">Outstanding</span><strong class="font-monospace text-warning">Rp {{ number_format($totalInvoiced - $totalPaid,0,',','.') }}</strong></div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <h6 class="text-muted text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.06em">Quotation</h6>
            @forelse($project->quotations as $q)
                @php [$ql,$qc] = $q->statusBadge(); @endphp
                <div class="d-flex justify-content-between border-bottom py-2 small">
                    <a href="{{ route('quotations.show',$q) }}" class="font-monospace text-decoration-none">{{ $q->quotation_number }}</a>
                    <div class="d-flex align-items-center gap-2">
                        <span class="font-monospace">Rp {{ number_format($q->total,0,',','.') }}</span>
                        <span class="badge bg-{{ $qc }} bg-opacity-10 text-{{ $qc }}" style="font-size:.68rem">{{ $ql }}</span>
                    </div>
                </div>
            @empty<div class="text-muted small">Belum ada quotation.</div>@endforelse
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <h6 class="text-muted text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.06em">Invoice</h6>
            @forelse($project->invoices as $i)
                @php [$il,$ic] = $i->statusBadge(); @endphp
                <div class="d-flex justify-content-between border-bottom py-2 small">
                    <a href="{{ route('invoices.show',$i) }}" class="font-monospace text-decoration-none">{{ $i->invoice_number }}</a>
                    <div class="d-flex align-items-center gap-2">
                        <span class="font-monospace">Rp {{ number_format($i->total,0,',','.') }}</span>
                        <span class="badge bg-{{ $ic }} bg-opacity-10 text-{{ $ic }}" style="font-size:.68rem">{{ $il }}</span>
                    </div>
                </div>
            @empty<div class="text-muted small">Belum ada invoice.</div>@endforelse
        </div></div>
    </div>

    {{-- Links --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title mb-0"><i class="bi bi-link-45deg me-2 text-primary"></i>Tautan Project</span>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLinkModal">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Tautan
                </button>
            </div>
            @if($project->links->isEmpty())
                <div class="card-body text-center py-4 text-muted" style="font-size:.85rem">
                    <i class="bi bi-link-45deg d-block mb-1 fs-3 opacity-25"></i>Belum ada tautan. Tambahkan link repository, staging, figma, docs, dsb.
                </div>
            @else
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($project->links as $link)
                            <div class="d-flex align-items-center gap-2 border rounded px-3 py-2" style="font-size:.85rem">
                                <i class="{{ $link->typeIcon() }}" style="color:{{ $link->typeColor() }};font-size:1rem"></i>
                                <div>
                                    <a href="{{ $link->url }}" target="_blank" class="fw-medium text-decoration-none">{{ $link->label }}</a>
                                    <div class="text-muted" style="font-size:.72rem">{{ $link->typeLabel() }}</div>
                                </div>
                                <form method="POST" action="{{ route('projects.links.destroy', [$project, $link]) }}"
                                    onsubmit="return confirm('Hapus tautan ini?')" class="ms-2">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Hapus">
                                        <i class="bi bi-x-lg" style="font-size:.7rem"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Files --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title mb-0"><i class="bi bi-folder2-open me-2 text-primary"></i>File Pendukung Project</span>
                <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#uploadFileForm">
                    <i class="bi bi-upload me-1"></i>Upload File
                </button>
            </div>
            <div class="collapse" id="uploadFileForm">
                <div class="card-body border-bottom bg-light py-3 px-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible py-2 mb-3" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('projects.files.store', $project) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-medium" style="font-size:.85rem">Nama / Keterangan <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control form-control-sm @error('label') is-invalid @enderror"
                                    value="{{ old('label') }}" placeholder="cth. Wireframe v1, SRS, MoM Meeting" required>
                                @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium" style="font-size:.85rem">File <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control form-control-sm @error('file') is-invalid @enderror"
                                    accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xlsx,.xls,.zip,.rar,.pptx,.ppt" required>
                                <div class="form-text" style="font-size:.72rem">PDF, JPG, PNG, DOC, DOCX, XLSX, PPT, ZIP — maks. 20 MB</div>
                                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-upload"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @if($project->files->isEmpty())
                <div class="card-body text-center py-4 text-muted" style="font-size:.85rem">
                    <i class="bi bi-folder2-open d-block mb-1 fs-3 opacity-25"></i>Belum ada file. Klik <strong>Upload File</strong> untuk menambahkan.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.85rem">
                        <thead class="table-light">
                            <tr>
                                <th>File</th>
                                <th>Ukuran</th>
                                <th>Diunggah Oleh</th>
                                <th>Tanggal</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project->files as $pf)
                            <tr>
                                <td>
                                    <i class="bi bi-file-earmark{{ $pf->isPdf() ? '-pdf text-danger' : ($pf->isImage() ? '-image text-info' : '') }} me-2"></i>
                                    <span class="fw-medium">{{ $pf->label }}</span>
                                    <div class="text-muted" style="font-size:.75rem">{{ $pf->original_name }}</div>
                                </td>
                                <td class="text-muted">{{ $pf->fileSizeFormatted() }}</td>
                                <td class="text-muted">{{ $pf->uploader?->name ?? '-' }}</td>
                                <td class="text-muted">{{ $pf->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        @if($pf->isViewable())
                                            <a href="{{ route('projects.files.show', [$project, $pf]) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                        @endif
                                        <a href="{{ route('projects.files.show', [$project, $pf]) }}?download=1" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                                        <form method="POST" action="{{ route('projects.files.destroy', [$project, $pf]) }}"
                                            onsubmit="return confirm('Hapus file ini?')">
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

{{-- Modal: Tambah Tautan --}}
<div class="modal fade" id="addLinkModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('projects.links.store', $project) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-link-45deg me-2"></i>Tambah Tautan Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Jenis Tautan <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            @foreach(\App\Models\ProjectLink::typeOptions() as $key => [$label, $icon, $color])
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Label / Nama <span class="text-danger">*</span></label>
                        <input type="text" name="label" class="form-control" required maxlength="150"
                            placeholder="cth. Frontend Repo, Staging App, Design System">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">URL <span class="text-danger">*</span></label>
                        <input type="url" name="url" class="form-control" required maxlength="500"
                            placeholder="https://...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambahkan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
