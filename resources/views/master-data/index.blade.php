@extends('layouts.app')

@section('page-title', 'Data Master')

@section('content')
@php $tab = request('tab', 'positions'); @endphp

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Data Master</h4>
        <p class="text-muted small mb-0">Kelola daftar Jabatan, Departemen, dan Kategori Karyawan</p>
    </div>
</div>

<div class="card">
    <div class="card-header p-0">
        <ul class="nav nav-tabs card-header-tabs px-3 pt-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'positions' ? 'active' : '' }}" href="{{ route('master-data.index', ['tab'=>'positions']) }}">
                    <i class="bi bi-briefcase me-1"></i>Jabatan
                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ $positions->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'departments' ? 'active' : '' }}" href="{{ route('master-data.index', ['tab'=>'departments']) }}">
                    <i class="bi bi-diagram-3 me-1"></i>Departemen
                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ $departments->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'categories' ? 'active' : '' }}" href="{{ route('master-data.index', ['tab'=>'categories']) }}">
                    <i class="bi bi-tags me-1"></i>Kategori Karyawan
                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ count($categories) }}</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">

    {{-- ── Positions tab ───────────────────────────────────────── --}}
    @if($tab === 'positions')
        <div class="d-flex align-items-center justify-content-between mb-3">
            <small class="text-muted">Daftar jabatan yang akan muncul sebagai pilihan saat membuat / mengedit karyawan.</small>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPositionModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Jabatan
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Jabatan</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Karyawan</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($positions as $p)
                    <tr>
                        <td class="fw-semibold">{{ $p->name }}</td>
                        <td class="text-muted small">{{ $p->description ?: '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ $p->employees_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($p->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary"
                                onclick="editPosition({{ $p->id }}, @js($p->name), @js($p->description), {{ $p->is_active ? 'true' : 'false' }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('master-data.positions.destroy', $p) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus jabatan {{ $p->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data jabatan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

    {{-- ── Departments tab ─────────────────────────────────────── --}}
    @elseif($tab === 'departments')
        <div class="d-flex align-items-center justify-content-between mb-3">
            <small class="text-muted">Daftar departemen yang akan muncul sebagai pilihan saat membuat / mengedit karyawan.</small>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Departemen
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Departemen</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Karyawan</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($departments as $d)
                    <tr>
                        <td class="fw-semibold">{{ $d->name }}</td>
                        <td class="text-muted small">{{ $d->description ?: '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ $d->employees_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($d->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary"
                                onclick="editDepartment({{ $d->id }}, @js($d->name), @js($d->description), {{ $d->is_active ? 'true' : 'false' }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('master-data.departments.destroy', $d) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus departemen {{ $d->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data departemen.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

    {{-- ── Categories tab (read-only, system enum) ────────────── --}}
    @else
        <div class="alert alert-info py-2 small mb-3">
            <i class="bi bi-info-circle me-1"></i>
            Kategori Karyawan adalah daftar sistem yang sudah disesuaikan dengan logika penggajian
            (warna badge, perhitungan kontrak, dsb) dan tidak dapat diubah dari halaman ini.
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Kategori</th>
                        <th class="text-center">Karyawan</th>
                        <th class="text-end" style="width:140px">Badge</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($categories as $cat)
                    <tr>
                        <td class="font-monospace text-muted small">{{ $cat->value }}</td>
                        <td class="fw-semibold">{{ $cat->label() }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ $categoryCounts[$cat->value] ?? 0 }}</span>
                        </td>
                        <td class="text-end">
                            <span class="badge bg-{{ $cat->badgeColor() }} bg-opacity-10 text-{{ $cat->badgeColor() }}">
                                {{ $cat->label() }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    </div>
</div>

{{-- ── Modals: Positions ───────────────────────────────────────── --}}
<div class="modal fade" id="addPositionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('master-data.positions.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Tambah Jabatan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                        <input name="name" class="form-control" required maxlength="150" placeholder="Cth: Software Engineer">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addPosActive" checked>
                        <label class="form-check-label" for="addPosActive">Aktif (tampil di pilihan)</label>
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

<div class="modal fade" id="editPositionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editPositionForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Jabatan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                        <input name="name" id="edit-pos-name" class="form-control" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="edit-pos-description" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit-pos-active">
                        <label class="form-check-label" for="edit-pos-active">Aktif (tampil di pilihan)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Modals: Departments ─────────────────────────────────────── --}}
<div class="modal fade" id="addDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('master-data.departments.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Tambah Departemen</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Departemen <span class="text-danger">*</span></label>
                        <input name="name" class="form-control" required maxlength="150" placeholder="Cth: Engineering">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addDeptActive" checked>
                        <label class="form-check-label" for="addDeptActive">Aktif (tampil di pilihan)</label>
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

<div class="modal fade" id="editDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editDepartmentForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Departemen</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Departemen <span class="text-danger">*</span></label>
                        <input name="name" id="edit-dept-name" class="form-control" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="edit-dept-description" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit-dept-active">
                        <label class="form-check-label" for="edit-dept-active">Aktif (tampil di pilihan)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editPosition(id, name, description, isActive) {
    document.getElementById('editPositionForm').action = '{{ url("master-data/positions") }}/' + id;
    document.getElementById('edit-pos-name').value = name || '';
    document.getElementById('edit-pos-description').value = description || '';
    document.getElementById('edit-pos-active').checked = !!isActive;
    new bootstrap.Modal(document.getElementById('editPositionModal')).show();
}
function editDepartment(id, name, description, isActive) {
    document.getElementById('editDepartmentForm').action = '{{ url("master-data/departments") }}/' + id;
    document.getElementById('edit-dept-name').value = name || '';
    document.getElementById('edit-dept-description').value = description || '';
    document.getElementById('edit-dept-active').checked = !!isActive;
    new bootstrap.Modal(document.getElementById('editDepartmentModal')).show();
}
</script>
@endpush
