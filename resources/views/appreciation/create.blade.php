@extends('layouts.app')
@section('title', 'Tambah Anggaran Apresiasi')
@section('page-title', 'Tambah Anggaran Apresiasi')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-stars me-2 text-warning"></i>Anggaran Uang Apresiasi</span>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-4" style="font-size:.87rem">
                    Tentukan total anggaran apresiasi tahunan untuk seorang karyawan. Karyawan dapat mengajukan permohonan (klaim) dari anggaran ini sepanjang tahun.
                </p>
                <form method="POST" action="{{ route('appreciation.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Perusahaan <span class="text-danger">*</span></label>
                            <select name="company_id" id="companySelect" class="form-select @error('company_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Perusahaan --</option>
                                @foreach($companies as $co)
                                    <option value="{{ $co->id }}" {{ old('company_id') == $co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                                @endforeach
                            </select>
                            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Karyawan <span class="text-danger">*</span></label>
                            <select name="employee_id" id="employeeSelect" class="form-select @error('employee_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" data-company="{{ $emp->company_id }}"
                                        {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-medium">Tahun <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control @error('year') is-invalid @enderror"
                                value="{{ old('year', date('Y')) }}" min="2000" max="2100" required>
                            @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-medium">Total Anggaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="total_amount" class="form-control @error('total_amount') is-invalid @enderror"
                                    value="{{ old('total_amount', 0) }}" min="0" step="1000" required>
                                @error('total_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium">Catatan</label>
                            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                placeholder="Keterangan kebijakan apresiasi...">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 d-flex gap-2 justify-content-end pt-2">
                            <a href="{{ route('appreciation.index') }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.getElementById('companySelect').addEventListener('change', function() {
    var cid = this.value;
    document.querySelectorAll('#employeeSelect option[data-company]').forEach(function(opt) {
        opt.style.display = (!cid || opt.dataset.company === cid) ? '' : 'none';
    });
    document.getElementById('employeeSelect').value = '';
});
</script>
@endpush
@endsection
