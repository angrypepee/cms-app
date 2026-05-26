@extends('layouts.app')

@section('page-title', 'Tipe Cuti')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Tipe Cuti</h4>
        <p class="text-muted small mb-0">Kelola jenis-jenis cuti karyawan</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Tipe
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama Tipe</th>
                        <th>Maks. Hari/Tahun</th>
                        <th>Dibayar</th>
                        <th>Permohonan</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveTypes as $lt)
                        <tr>
                            <td>
                                <span class="d-inline-block rounded-circle me-2" style="width:10px;height:10px;background:{{ $lt->color }};vertical-align:middle;"></span>
                                <span class="fw-semibold">{{ $lt->name }}</span>
                            </td>
                            <td>{{ $lt->maxLabel() }}</td>
                            <td>
                                @if($lt->is_paid)
                                    <span class="badge badge-approved badge-pill">Ya</span>
                                @else
                                    <span class="badge badge-rejected badge-pill">Tidak</span>
                                @endif
                            </td>
                            <td>{{ $lt->leave_requests_count }} permohonan</td>
                            <td>
                                @if($lt->is_active)
                                    <span class="badge badge-approved badge-pill">Aktif</span>
                                @else
                                    <span class="badge badge-rejected badge-pill">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-secondary"
                                        onclick="editType({{ $lt->id }}, '{{ $lt->name }}', {{ $lt->max_days_per_year }}, {{ $lt->is_paid ? 1 : 0 }}, '{{ $lt->color }}', {{ $lt->is_active ? 1 : 0 }})"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if($lt->leave_requests_count == 0)
                                        <form method="POST" action="{{ route('leaves.types.destroy', $lt) }}" onsubmit="return confirm('Hapus tipe cuti ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Belum ada tipe cuti</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('leaves.types.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tipe Cuti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input name="name" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Maks. Hari per Tahun</label>
                        <input name="max_days_per_year" type="number" class="form-control" min="0" value="0">
                        <div class="form-text">0 = tidak terbatas</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_paid" id="add-is-paid" value="1" checked>
                            <label class="form-check-label" for="add-is-paid">Cuti Berbayar</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna</label>
                        <input name="color" type="color" class="form-control form-control-color" value="#2563eb">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="add-is-active" value="1" checked>
                            <label class="form-check-label" for="add-is-active">Aktif</label>
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

{{-- Edit Modal --}}
<div class="modal fade" id="editTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editTypeForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tipe Cuti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input name="name" id="edit-name" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Maks. Hari per Tahun</label>
                        <input name="max_days_per_year" id="edit-max" type="number" class="form-control" min="0">
                        <div class="form-text">0 = tidak terbatas</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_paid" id="edit-is-paid" value="1">
                            <label class="form-check-label" for="edit-is-paid">Cuti Berbayar</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna</label>
                        <input name="color" id="edit-color" type="color" class="form-control form-control-color">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit-is-active" value="1">
                            <label class="form-check-label" for="edit-is-active">Aktif</label>
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
@endsection

@push('scripts')
<script>
function editType(id, name, maxDays, isPaid, color, isActive) {
    document.getElementById('editTypeForm').action = '{{ url("leaves/types") }}/' + id;
    document.getElementById('edit-name').value   = name;
    document.getElementById('edit-max').value    = maxDays;
    document.getElementById('edit-color').value  = color;
    document.getElementById('edit-is-paid').checked   = isPaid == 1;
    document.getElementById('edit-is-active').checked = isActive == 1;
    new bootstrap.Modal(document.getElementById('editTypeModal')).show();
}
</script>
@endpush
