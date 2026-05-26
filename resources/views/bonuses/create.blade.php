@extends('layouts.app')
@section('title', 'Tambah Bonus')
@section('page-title', 'Tambah Bonus')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-gift me-2 text-primary"></i>Tambah Bonus Karyawan</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('bonuses.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">Jenis Bonus <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="bonus_type" id="typeTHR" value="thr"
                                        {{ old('bonus_type', 'thr') === 'thr' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="typeTHR">
                                        <i class="bi bi-gift text-warning me-1"></i>Bonus Tahunan (THR)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="bonus_type" id="typeProject" value="project"
                                        {{ old('bonus_type') === 'project' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="typeProject">
                                        <i class="bi bi-briefcase text-info me-1"></i>Bonus Project
                                    </label>
                                </div>
                            </div>
                            @error('bonus_type')<div class="text-danger" style="font-size:.8rem">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium">Judul / Keterangan <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title') }}" placeholder="cth. THR Lebaran 2026 / Bonus Project Alpha" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

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
                                    <option value="{{ $emp->id }}" data-company="{{ $emp->company_id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nominal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                    value="{{ old('amount', 0) }}" min="0" step="1000" required>
                                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-medium">Tahun <span class="text-danger">*</span></label>
                            <input type="number" name="period_year" class="form-control @error('period_year') is-invalid @enderror"
                                value="{{ old('period_year', date('Y')) }}" min="2000" max="2100" required>
                            @error('period_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-medium">Bulan</label>
                            <select name="period_month" class="form-select @error('period_month') is-invalid @enderror">
                                <option value="">-- Opsional --</option>
                                @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $m)
                                    <option value="{{ $i+1 }}" {{ old('period_month') == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                            @error('period_month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tanggal Pembayaran</label>
                            <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror"
                                value="{{ old('payment_date') }}">
                            @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium">Catatan</label>
                            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                placeholder="Keterangan tambahan...">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 d-flex gap-2 justify-content-end pt-2">
                            <a href="{{ route('bonuses.index') }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Bonus</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
// Filter employees by company
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
