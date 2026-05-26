@extends('layouts.app')
@section('title', 'Edit Slip Gaji')
@section('page-title', 'Edit Slip Gaji')
@section('content')
<form method="POST" action="{{ route('payroll-slips.update', $payrollSlip) }}" id="slipForm">
@csrf @method('PUT')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><span class="card-title"><i class="bi bi-person-badge me-2 text-primary"></i>Informasi Karyawan</span></div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Perusahaan <span class="text-danger">*</span></label>
                        <select name="company_id" id="companySelect" class="form-select @error('company_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Perusahaan --</option>
                            @foreach($companies as $co)
                                <option value="{{ $co->id }}" {{ old('company_id', $payrollSlip->company_id) == $co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                            @endforeach
                        </select>
                        @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Karyawan <span class="text-danger">*</span></label>
                        <select name="employee_id" id="employeeSelect" class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="{{ $payrollSlip->employee_id }}">{{ $payrollSlip->employee->name }}</option>
                        </select>
                        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Bulan <span class="text-danger">*</span></label>
                        <select name="period_month" class="form-select @error('period_month') is-invalid @enderror" required>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ old('period_month', $payrollSlip->period_month) == $m ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                            @endforeach
                        </select>
                        @error('period_month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Tahun <span class="text-danger">*</span></label>
                        <select name="period_year" class="form-select @error('period_year') is-invalid @enderror" required>
                            @foreach(range(date('Y'), date('Y')-4) as $y)
                                <option value="{{ $y }}" {{ old('period_year', $payrollSlip->period_year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                        @error('period_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Catatan</label>
                        <textarea name="notes" rows="2" class="form-control">{{ old('notes', $payrollSlip->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-plus-circle me-2 text-success"></i>Pendapatan</span>
                <button type="button" class="btn btn-sm btn-success" onclick="addItemWithLabel('income','')"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addItemWithLabel('income','Gaji Pokok')">+ Gaji Pokok</button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addItemWithLabel('income','Tunjangan Jabatan')">+ T. Jabatan</button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addItemWithLabel('income','Tunjangan Makan')">+ T. Makan</button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addItemWithLabel('income','Tunjangan Transport')">+ T. Transport</button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addItemWithLabel('income','Bonus')">+ Bonus</button>
                </div>
                <div id="incomeItems"></div>
                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                    <span class="text-muted" style="font-size:.85rem">Total Pendapatan</span>
                    <span class="fw-bold text-success" id="total-income">Rp 0</span>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-dash-circle me-2 text-danger"></i>Potongan</span>
                <button type="button" class="btn btn-sm btn-danger" onclick="addItemWithLabel('deduction','')"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="addItemWithLabel('deduction','BPJS Kesehatan')">+ BPJS Kes.</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="addItemWithLabel('deduction','BPJS Ketenagakerjaan')">+ BPJS TK</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="addItemWithLabel('deduction','PPh 21')">+ PPh 21</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="addItemWithLabel('deduction','Pinjaman')">+ Pinjaman</button>
                </div>
                <div id="deductionItems"></div>
                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                    <span class="text-muted" style="font-size:.85rem">Total Potongan</span>
                    <span class="fw-bold text-danger" id="total-deduction">Rp 0</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card sticky-top" style="top:80px">
            <div class="card-header"><span class="card-title"><i class="bi bi-calculator me-2 text-primary"></i>Ringkasan</span></div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted" style="font-size:.88rem">Total Pendapatan</span>
                    <span class="fw-semibold text-success" id="sum-income">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted" style="font-size:.88rem">Total Potongan</span>
                    <span class="fw-semibold text-danger" id="sum-deduction">Rp 0</span>
                </div>
                <div class="border-top pt-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold" style="font-size:.95rem">Take Home Pay</span>
                        <span class="fw-bold text-primary fs-5" id="sum-thp">Rp 0</span>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="action" value="publish" class="btn btn-primary"><i class="bi bi-send me-1"></i>Simpan & Publish</button>
                    <button type="submit" name="action" value="draft" class="btn btn-outline-secondary"><i class="bi bi-floppy me-1"></i>Simpan Draft</button>
                </div>
                <a href="{{ route('payroll-slips.show', $payrollSlip) }}" class="btn btn-link btn-sm w-100 mt-2 text-muted">Batal</a>
            </div>
        </div>
    </div>
</div>
</form>
@push('scripts')
<script>
const baseRoute = '{{ route("companies.employees", ["company" => ":id"]) }}';
const existingEmployeeId = {{ $payrollSlip->employee_id }};
let incomeCount = 0, deductionCount = 0;

document.getElementById('companySelect').addEventListener('change', function(){
    const id = this.value;
    const sel = document.getElementById('employeeSelect');
    if (!id) { sel.innerHTML = '<option value="">-- Pilih karyawan --</option>'; return; }
    fetch(baseRoute.replace(':id', id))
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">-- Pilih karyawan --</option>';
            data.forEach(e => {
                const sel2 = e.id == existingEmployeeId ? ' selected' : '';
                sel.innerHTML += `<option value="${e.id}"${sel2}>${e.name} (${e.employee_id})</option>`;
            });
        });
});

function fmt(n) { return 'Rp ' + Math.max(0, n).toLocaleString('id-ID'); }

function recalculate() {
    let inc = 0, ded = 0;
    document.querySelectorAll('#incomeItems .item-amount').forEach(i => inc += (parseFloat(i.value) || 0));
    document.querySelectorAll('#deductionItems .item-amount').forEach(i => ded += (parseFloat(i.value) || 0));
    document.getElementById('total-income').textContent = fmt(inc);
    document.getElementById('total-deduction').textContent = fmt(ded);
    document.getElementById('sum-income').textContent = fmt(inc);
    document.getElementById('sum-deduction').textContent = fmt(ded);
    document.getElementById('sum-thp').textContent = fmt(inc - ded);
}

function addItem(type, label, amount) {
    const container = document.getElementById(type + 'Items');
    const idx = type === 'income' ? incomeCount++ : deductionCount++;
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="hidden" name="items[${idx}][type]" value="${type}">
        <input type="text" name="items[${idx}][label]" class="form-control" placeholder="Keterangan" value="${label}" required>
        <span class="input-group-text">Rp</span>
        <input type="number" name="items[${idx}][amount]" class="form-control item-amount text-end" placeholder="0" value="${amount || ''}" min="0" step="1000" required oninput="recalculate()">
        <button type="button" class="btn btn-outline-secondary" onclick="this.closest('.input-group').remove(); recalculate()"><i class="bi bi-x"></i></button>
    `;
    container.appendChild(div);
    recalculate();
}

function addItemWithLabel(type, label) {
    addItem(type, label, '');
    const container = document.getElementById(type + 'Items');
    const last = container.lastElementChild;
    if (last) { const inp = last.querySelector('input[type="text"]'); if(inp) inp.focus(); }
}

document.getElementById('slipForm').addEventListener('submit', function(){
    let idx = 0;
    document.querySelectorAll('#incomeItems .input-group, #deductionItems .input-group').forEach(row => {
        row.querySelector('[name*="[label]"]').name = `items[${idx}][label]`;
        row.querySelector('[name*="[amount]"]').name = `items[${idx}][amount]`;
        row.querySelector('[name*="[type]"]').name = `items[${idx}][type]`;
        idx++;
    });
});

// Load existing items
const existingItems = @json($payrollSlip->items);
existingItems.forEach(item => addItem(item.type, item.label, item.amount));

// Trigger employee load for current company
document.getElementById('companySelect').dispatchEvent(new Event('change'));
</script>
@endpush
@endsection
