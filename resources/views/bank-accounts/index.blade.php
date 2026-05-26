@extends('layouts.app')
@section('title','Rekening Bank')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-1"><i class="bi bi-bank text-primary"></i> Rekening Bank</h3>
        <p class="text-muted mb-0 small">Daftar rekening untuk dicantumkan pada invoice & instruksi pembayaran</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bankModal" onclick="prepareBankModal()">
        <i class="bi bi-plus-circle"></i> Tambah Rekening
    </button>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(isset($errors) && $errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bank</th>
                    <th>Atas Nama</th>
                    <th>Nomor Rekening</th>
                    <th>Cabang</th>
                    <th>Perusahaan</th>
                    <th class="text-center">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($accounts as $a)
                    <tr>
                        <td><i class="bi bi-bank2"></i> <strong>{{ $a->bank_name }}</strong></td>
                        <td>{{ $a->account_name }}</td>
                        <td><code>{{ $a->account_number }}</code></td>
                        <td>{{ $a->branch ?: '—' }}</td>
                        <td>{{ $a->company->name ?? '—' }}</td>
                        <td class="text-center">
                            @if($a->is_default)<span class="badge bg-success">Default</span>@endif
                            @if(!$a->is_active)<span class="badge bg-secondary">Nonaktif</span>@endif
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal" data-bs-target="#bankModal"
                                onclick='prepareBankModal(@json($a))'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('bank-accounts.destroy', $a) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus rekening ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada rekening bank. Tambahkan untuk ditampilkan pada invoice.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="bankModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="bankForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="bm_method" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bm_title">Tambah Rekening Bank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bank <span class="text-danger">*</span></label>
                            <input name="bank_name" id="bm_bank_name" class="form-control" required maxlength="100" placeholder="BCA / Mandiri / BNI">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Rekening <span class="text-danger">*</span></label>
                            <input name="account_number" id="bm_account_number" class="form-control" required maxlength="50">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Atas Nama <span class="text-danger">*</span></label>
                            <input name="account_name" id="bm_account_name" class="form-control" required maxlength="150">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cabang</label>
                            <input name="branch" id="bm_branch" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SWIFT Code</label>
                            <input name="swift_code" id="bm_swift" class="form-control" maxlength="30">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Perusahaan</label>
                            <select name="company_id" id="bm_company" class="form-select">
                                <option value="">— Semua —</option>
                                @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="hidden" name="is_default" value="0">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1" id="bm_default">
                                <label class="form-check-label" for="bm_default">Jadikan default (ditampilkan duluan)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="bm_active" checked>
                                <label class="form-check-label" for="bm_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const STORE_URL = "{{ route('bank-accounts.store') }}";
function prepareBankModal(data = null) {
    const f = document.getElementById('bankForm');
    if (data) {
        f.action = `{{ url('bank-accounts') }}/${data.id}`;
        document.getElementById('bm_method').value = 'PUT';
        document.getElementById('bm_title').textContent = 'Edit Rekening Bank';
        document.getElementById('bm_bank_name').value      = data.bank_name ?? '';
        document.getElementById('bm_account_number').value = data.account_number ?? '';
        document.getElementById('bm_account_name').value   = data.account_name ?? '';
        document.getElementById('bm_branch').value         = data.branch ?? '';
        document.getElementById('bm_swift').value          = data.swift_code ?? '';
        document.getElementById('bm_company').value        = data.company_id ?? '';
        document.getElementById('bm_default').checked      = !!data.is_default;
        document.getElementById('bm_active').checked       = !!data.is_active;
    } else {
        f.action = STORE_URL;
        document.getElementById('bm_method').value = 'POST';
        document.getElementById('bm_title').textContent = 'Tambah Rekening Bank';
        f.reset();
        document.getElementById('bm_active').checked = true;
    }
}
</script>
@endsection
