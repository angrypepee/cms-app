@extends('layouts.app')
@section('title','Klien')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1" style="font-size:1.15rem;font-weight:700"><i class="bi bi-briefcase me-2 text-primary"></i>Daftar Klien</h4>
        <div class="text-muted" style="font-size:.82rem">Daftar perusahaan klien (B2B) untuk quotation &amp; invoice.</div>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#clientModal"
            onclick="prepareClientModal()">
        <i class="bi bi-plus-circle me-1"></i> Tambah Klien
    </button>
</div>

@if(session('success'))<div class="alert alert-success py-2" style="font-size:.85rem">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2" style="font-size:.85rem">{{ session('error') }}</div>@endif

<form method="GET" class="mb-3">
    <div class="input-group" style="max-width:400px">
        <input type="search" name="q" class="form-control form-control-sm" placeholder="Cari nama / email / kontak..." value="{{ $q }}">
        <button class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i></button>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead style="font-size:.8rem">
                <tr>
                    <th>Nama Klien</th>
                    <th>Kontak</th>
                    <th>Email / Telp</th>
                    <th>NPWP</th>
                    <th>Issued by</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $c)
                <tr>
                    <td>
                        <a href="{{ route('clients.show', $c) }}" class="text-decoration-none fw-semibold" style="font-size:.88rem;color:#1e293b">{{ $c->name }}</a>
                        @if($c->address)<div class="text-muted" style="font-size:.72rem">{{ \Illuminate\Support\Str::limit($c->address, 60) }}</div>@endif
                    </td>
                    <td style="font-size:.82rem">{{ $c->contact_person ?: '-' }}</td>
                    <td style="font-size:.78rem">
                        @if($c->email)<div>{{ $c->email }}</div>@endif
                        @if($c->phone)<div class="text-muted">{{ $c->phone }}</div>@endif
                    </td>
                    <td class="font-monospace" style="font-size:.76rem">{{ $c->npwp ?: '-' }}</td>
                    <td style="font-size:.78rem">{{ $c->company->name ?? '-' }}</td>
                    <td>
                        @if($c->is_active)
                            <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.7rem">Aktif</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.7rem">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary btn-icon" data-bs-toggle="modal" data-bs-target="#clientModal"
                                onclick='prepareClientModal(@json($c))' title="Edit"><i class="bi bi-pencil"></i></button>
                        <form action="{{ route('clients.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus klien {{ $c->name }}?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger btn-icon" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-inbox d-block fs-3 opacity-25 mb-1"></i>Belum ada klien.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($clients->hasPages())<div class="px-3 py-2 border-top">{{ $clients->links() }}</div>@endif
</div>

{{-- Modal --}}
<div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" method="POST" id="clientForm">
        @csrf
        <input type="hidden" name="_method" id="clientFormMethod" value="POST">
        <div class="modal-header">
            <h5 class="modal-title" id="clientModalTitle">Tambah Klien</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label small fw-semibold">Nama Klien <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="cf_name" class="form-control" required maxlength="200">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Issued by (Perusahaan Internal)</label>
                    <select name="company_id" id="cf_company_id" class="form-select">
                        <option value="">— Pilih —</option>
                        @foreach($companies as $co)<option value="{{ $co->id }}">{{ $co->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Kontak Person</label>
                    <input type="text" name="contact_person" id="cf_contact_person" class="form-control" maxlength="150">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">NPWP</label>
                    <input type="text" name="npwp" id="cf_npwp" class="form-control" maxlength="50">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Email</label>
                    <input type="email" name="email" id="cf_email" class="form-control" maxlength="150">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Telepon</label>
                    <input type="text" name="phone" id="cf_phone" class="form-control" maxlength="50">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Alamat</label>
                    <textarea name="address" id="cf_address" class="form-control" rows="2" maxlength="1000"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Catatan</label>
                    <textarea name="notes" id="cf_notes" class="form-control" rows="2" maxlength="1000"></textarea>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="cf_is_active" name="is_active" value="1" checked>
                        <label for="cf_is_active" class="form-check-label small">Klien Aktif</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Simpan</button>
        </div>
    </form>
  </div>
</div>

<script>
function prepareClientModal(client) {
    var form = document.getElementById('clientForm');
    var isEdit = !!client;
    document.getElementById('clientModalTitle').textContent = isEdit ? 'Edit Klien' : 'Tambah Klien';
    document.getElementById('clientFormMethod').value = isEdit ? 'PUT' : 'POST';
    form.action = isEdit
        ? "{{ url('clients') }}/" + client.id
        : "{{ route('clients.store') }}";
    ['name','contact_person','email','phone','npwp','address','notes','company_id'].forEach(function(k){
        var el = document.getElementById('cf_'+k);
        if(el) el.value = isEdit ? (client[k] || '') : '';
    });
    document.getElementById('cf_is_active').checked = isEdit ? !!client.is_active : true;
}
</script>
@endsection
