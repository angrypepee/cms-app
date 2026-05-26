{{--
    Shared CRUD form for Payroll Slip (create + edit).
    Expects: $companies, $payrollSlip (model or null), $isEdit (bool).
--}}
@php
    $isEdit       = $isEdit ?? false;
    $slip         = $payrollSlip ?? null;
    $formAction   = $isEdit ? route('payroll-slips.update', $slip) : route('payroll-slips.store');
    $cancelHref   = $isEdit ? route('payroll-slips.show', $slip)    : route('payroll-slips.index');
    $oldCompanyId = old('company_id',  $slip->company_id  ?? null);
    $oldEmployeeId= old('employee_id', $slip->employee_id ?? null);
    $oldMonth     = old('period_month',$slip->period_month ?? (int) date('n'));
    $oldYear      = old('period_year', $slip->period_year  ?? (int) date('Y'));
    $oldNotes     = old('notes',       $slip->notes ?? '');
    $oldPayDate   = old('payment_date', optional($slip?->payment_date)->format('Y-m-d'));
    $oldRelDate   = old('released_at',  optional($slip?->released_at)->format('Y-m-d'));
    $oldSignAdmin = old('signed_at',          optional($slip?->signed_at)->format('Y-m-d'));
    $oldSignEmp   = old('employee_signed_at', optional($slip?->employee_signed_at)->format('Y-m-d'));
    $existingItems = $slip?->items ?? collect();
@endphp

<form method="POST" action="{{ $formAction }}" id="slipForm">
@csrf
@if($isEdit) @method('PUT') @endif
<div class="row g-4">
    {{-- Left: Main Form --}}
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
                                <option value="{{ $co->id }}" {{ $oldCompanyId == $co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                            @endforeach
                        </select>
                        @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Karyawan <span class="text-danger">*</span></label>
                        <select name="employee_id" id="employeeSelect" class="form-select @error('employee_id') is-invalid @enderror" required>
                            @if($isEdit && $slip?->employee)
                                <option value="{{ $slip->employee_id }}">{{ $slip->employee->name }}</option>
                            @else
                                <option value="">-- Pilih karyawan --</option>
                            @endif
                        </select>
                        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Bulan <span class="text-danger">*</span></label>
                        <select name="period_month" class="form-select @error('period_month') is-invalid @enderror" required>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ $oldMonth == $m ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                            @endforeach
                        </select>
                        @error('period_month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Tahun <span class="text-danger">*</span></label>
                        <select name="period_year" class="form-select @error('period_year') is-invalid @enderror" required>
                            @foreach(range((int) date('Y') + 1, (int) date('Y') - 4) as $y)
                                <option value="{{ $y }}" {{ $oldYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                        @error('period_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Catatan</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Catatan tambahan (opsional)">{{ $oldNotes }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tanggal & Tanda Tangan --}}
        <div class="card mb-4">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-calendar-check me-2 text-primary"></i>Tanggal &amp; Tanda Tangan</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Tanggal Pembayaran</label>
                        <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ $oldPayDate }}">
                        @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Tanggal gaji ditransfer ke karyawan.</small>
                    </div>

                    @if(auth()->check() && auth()->user()->isAdmin())
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Tanggal Penerbitan Slip</label>
                        <input type="date" name="released_at" class="form-control @error('released_at') is-invalid @enderror" value="{{ $oldRelDate }}">
                        @error('released_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Tampil di footer slip (PDF).</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Tanggal TTD Admin / HR</label>
                        <input type="date" name="signed_at" class="form-control @error('signed_at') is-invalid @enderror" value="{{ $oldSignAdmin }}">
                        @error('signed_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">
                            @if($isEdit && $slip?->signer)
                                Ditandatangani oleh: <strong>{{ $slip->signer->name }}</strong>
                            @else
                                Kosongkan jika belum ditandatangani.
                            @endif
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Tanggal TTD Karyawan</label>
                        <input type="date" name="employee_signed_at" class="form-control @error('employee_signed_at') is-invalid @enderror" value="{{ $oldSignEmp }}">
                        @error('employee_signed_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Tanggal karyawan menyetujui / menerima slip.</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pendapatan --}}
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
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addItemWithLabel('income','Lembur')">+ Lembur</button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addItemWithLabel('income','Bonus')">+ Bonus</button>
                </div>
                <div id="incomeItems"></div>
                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                    <span class="text-muted" style="font-size:.85rem">Total Pendapatan</span>
                    <span class="fw-bold text-success" id="total-income">Rp 0</span>
                </div>
            </div>
        </div>

        {{-- Potongan --}}
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
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="addItemWithLabel('deduction','Keterlambatan')">+ Keterlambatan</button>
                </div>
                <div id="deductionItems"></div>
                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                    <span class="text-muted" style="font-size:.85rem">Total Potongan</span>
                    <span class="fw-bold text-danger" id="total-deduction">Rp 0</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Summary Sidebar --}}
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
                    <button type="submit" name="action" value="publish" class="btn btn-primary"><i class="bi bi-send me-1"></i>Simpan &amp; Publish</button>
                    <button type="submit" name="action" value="draft" class="btn btn-outline-secondary"><i class="bi bi-floppy me-1"></i>Simpan Draft</button>
                </div>
                <a href="{{ $cancelHref }}" class="btn btn-link btn-sm w-100 mt-2 text-muted">Batal</a>
            </div>
        </div>
    </div>
</div>
</form>

@push('scripts')
<script>
const baseRoute = '{{ route("companies.employees", ["company" => ":id"]) }}';
const isEdit            = {{ $isEdit ? 'true' : 'false' }};
const existingEmployeeId = {{ $oldEmployeeId ? (int) $oldEmployeeId : 'null' }};
let incomeCount = 0, deductionCount = 0;

document.getElementById('companySelect').addEventListener('change', function(){
    const id  = this.value;
    const sel = document.getElementById('employeeSelect');
    sel.innerHTML = '<option value="">-- Memuat... --</option>';
    if (!id) { sel.innerHTML = '<option value="">-- Pilih karyawan --</option>'; return; }
    fetch(baseRoute.replace(':id', id))
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">-- Pilih karyawan --</option>';
            data.forEach(e => {
                const selAttr = (existingEmployeeId && e.id == existingEmployeeId) ? ' selected' : '';
                sel.innerHTML += `<option value="${e.id}"${selAttr}>${e.name} (${e.employee_id})</option>`;
            });
        })
        .catch(() => { sel.innerHTML = '<option value="">-- Error --</option>'; });
});

function fmt(n) { return 'Rp ' + Math.max(0, n).toLocaleString('id-ID'); }

function recalculate() {
    let inc = 0, ded = 0;
    document.querySelectorAll('#incomeItems .item-amount').forEach(i => inc += (parseFloat(i.value) || 0));
    document.querySelectorAll('#deductionItems .item-amount').forEach(i => ded += (parseFloat(i.value) || 0));
    document.getElementById('total-income').textContent     = fmt(inc);
    document.getElementById('total-deduction').textContent  = fmt(ded);
    document.getElementById('sum-income').textContent       = fmt(inc);
    document.getElementById('sum-deduction').textContent    = fmt(ded);
    document.getElementById('sum-thp').textContent          = fmt(inc - ded);
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

// Reindex items on submit so server gets contiguous indexes regardless of deletions
document.getElementById('slipForm').addEventListener('submit', function(){
    let idx = 0;
    document.querySelectorAll('#incomeItems .input-group, #deductionItems .input-group').forEach(row => {
        row.querySelector('[name*="[label]"]').name  = `items[${idx}][label]`;
        row.querySelector('[name*="[amount]"]').name = `items[${idx}][amount]`;
        row.querySelector('[name*="[type]"]').name   = `items[${idx}][type]`;
        idx++;
    });
});

// Initial population
@if($isEdit)
    // Load existing items
    const existingItems = @json($existingItems);
    existingItems.forEach(item => addItem(item.type, item.label, item.amount));
    // Load employees for current company, preserving selection
    document.getElementById('companySelect').dispatchEvent(new Event('change'));
@else
    @if(old('items'))
        // Re-render submitted items after validation failure
        const oldItems = @json(old('items'));
        oldItems.forEach(item => addItem(item.type, item.label, item.amount));
    @else
        // Sensible defaults for a fresh slip
        addItemWithLabel('income', 'Gaji Pokok');
        addItemWithLabel('deduction', 'BPJS Kesehatan');
    @endif
    // If a company was preselected (validation failure), reload its employees
    if (document.getElementById('companySelect').value) {
        document.getElementById('companySelect').dispatchEvent(new Event('change'));
    }
@endif
</script>
@endpush
