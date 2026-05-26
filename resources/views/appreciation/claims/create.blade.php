@extends('layouts.app')
@section('title', 'Ajukan Permohonan Apresiasi')
@section('page-title', 'Ajukan Permohonan Apresiasi')
@section('content')

@php
    $remaining = $appreciation->remainingAmount();
@endphp

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="alert alert-info mb-3" style="font-size:.87rem">
            <i class="bi bi-info-circle me-1"></i>
            Sisa anggaran apresiasi untuk <strong>{{ $appreciation->employee->name }}</strong> tahun <strong>{{ $appreciation->year }}</strong>:
            <strong>Rp {{ number_format($remaining, 0, ',', '.') }}</strong>
        </div>
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-file-earmark-plus me-2 text-primary"></i>Permohonan Baru</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('appreciation.claims.store', $appreciation) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">Judul Permohonan <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title') }}" placeholder="cth. Pembelian peralatan, Biaya pelatihan..." required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium">Deskripsi</label>
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                placeholder="Penjelasan keperluan permohonan...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nominal yang Diminta <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                    value="{{ old('amount', 0) }}" min="1" step="1000" max="{{ $remaining }}" required>
                                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text">Maks: Rp {{ number_format($remaining, 0, ',', '.') }}</div>
                        </div>

                        {{-- Document uploads --}}
                        <div class="col-12 border-top pt-3 mt-1">
                            <label class="form-label fw-medium">Bukti / Dokumen Pendukung</label>
                            <div id="docList">
                                <div class="row g-2 mb-2 doc-row">
                                    <div class="col-md-5">
                                        <input type="text" name="doc_labels[]" class="form-control form-control-sm" placeholder="Label dokumen">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="file" name="documents[]" class="form-control form-control-sm"
                                            accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-doc"><i class="bi bi-x"></i></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addDoc" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-plus me-1"></i>Tambah Dokumen
                            </button>
                            @error('documents.*')<div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 d-flex gap-2 justify-content-end pt-2">
                            <a href="{{ route('appreciation.show', $appreciation) }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Ajukan Permohonan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.getElementById('addDoc').addEventListener('click', function() {
    var row = document.querySelector('.doc-row').cloneNode(true);
    row.querySelectorAll('input').forEach(function(i) { i.value = ''; });
    document.getElementById('docList').appendChild(row);
});
document.getElementById('docList').addEventListener('click', function(e) {
    if (e.target.closest('.remove-doc') && document.querySelectorAll('.doc-row').length > 1) {
        e.target.closest('.doc-row').remove();
    }
});
</script>
@endpush
@endsection
