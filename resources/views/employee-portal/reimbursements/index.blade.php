@extends('layouts.app')

@section('title', 'Reimbursement Saya')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-receipt me-2 text-primary"></i>Reimbursement Saya</h4>
            <p class="text-muted mb-0" style="font-size:.88rem">Ajukan dan pantau permohonan reimbursement Anda</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitModal">
            <i class="bi bi-plus-lg me-1"></i>Ajukan Reimbursement
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- List --}}
    @if($reimbursements->isEmpty())
        <div class="card"><div class="card-body text-center py-5 text-muted">
            <i class="bi bi-receipt fs-1 d-block mb-2 opacity-25"></i>
            Belum ada permohonan reimbursement. Klik <strong>Ajukan Reimbursement</strong> untuk memulai.
        </div></div>
    @else
        <div class="card">
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
                            <td>
                                <span class="fw-medium">{{ $r->title }}</span>
                            </td>
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
                                <div class="d-flex gap-1">
                                    <a href="{{ route('my.reimbursements.show', $r) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </a>
                                    @if($r->isPending())
                                    <form method="POST" action="{{ route('my.reimbursements.destroy', $r) }}"
                                          onsubmit="return confirm('Batalkan permohonan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
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

{{-- Submit Modal --}}
<div class="modal fade" id="submitModal" tabindex="-1" aria-labelledby="submitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submitModalLabel">
                    <i class="bi bi-receipt me-2 text-primary"></i>Ajukan Reimbursement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('my.reimbursements.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">Judul / Keterangan <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" placeholder="cth. Tiket kereta dinas ke Jakarta" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Kategori</label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach(\App\Models\Reimbursement::$categories as $val => $label)
                                    <option value="{{ $val }}" {{ old('category') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Jumlah (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                   value="{{ old('amount') }}" min="1" step="1" required>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium">Approver <span class="text-danger">*</span></label>
                            <select name="approver_id" class="form-select @error('approver_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Approver --</option>
                                @foreach($approvers as $approver)
                                    <option value="{{ $approver->id }}" {{ old('approver_id') == $approver->id ? 'selected' : '' }}>
                                        {{ $approver->name }} ({{ $approver->role?->label() }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text" style="font-size:.75rem">Tag atasan / admin untuk menyetujui permohonan ini.</div>
                            @error('approver_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium">Deskripsi</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="3" placeholder="Keterangan tambahan (opsional)">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium">Dokumen Pendukung</label>
                            <div id="docList" class="d-flex flex-column gap-2 mb-2"></div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="addDocBtn">
                                <i class="bi bi-paperclip me-1"></i>Tambah Dokumen
                            </button>
                            <div class="form-text" style="font-size:.75rem">PDF, JPG, PNG, WEBP, DOC, DOCX — maks. 10 MB per file</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Ajukan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let docIndex = 0;
document.getElementById('addDocBtn').addEventListener('click', function () {
    const idx  = docIndex++;
    const row  = document.createElement('div');
    row.className = 'input-group input-group-sm';
    row.innerHTML = `
        <input type="text" name="doc_labels[${idx}]" class="form-control" placeholder="Label dokumen" style="max-width:200px">
        <input type="file" name="documents[${idx}]" class="form-control"
               accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()">
            <i class="bi bi-trash3"></i>
        </button>`;
    document.getElementById('docList').appendChild(row);
});

// Re-open modal on validation error
@if($errors->any())
    document.addEventListener('DOMContentLoaded', function () {
        const modal = new bootstrap.Modal(document.getElementById('submitModal'));
        modal.show();
    });
@endif
</script>
@endpush
