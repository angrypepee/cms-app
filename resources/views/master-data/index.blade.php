@extends('layouts.app')

@section('page-title', 'Data Master')

@section('content')
@php $tab = request('tab', 'positions'); @endphp

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Data Master</h4>
        <p class="text-muted small mb-0">Kelola daftar Jabatan, Departemen, Pihak Pertama, dan Kategori Karyawan</p>
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
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'first-parties' ? 'active' : '' }}" href="{{ route('master-data.index', ['tab'=>'first-parties']) }}">
                    <i class="bi bi-building me-1"></i>Pihak Pertama
                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ $firstParties->count() }}</span>
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
                            <button
                                class="btn btn-sm btn-outline-secondary btn-edit-position"
                                data-id="{{ $p->id }}"
                                data-name="{{ $p->name }}"
                                data-description="{{ $p->description }}"
                                data-is-active="{{ $p->is_active ? '1' : '0' }}"
                            >
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
                            <button
                                class="btn btn-sm btn-outline-secondary btn-edit-department"
                                data-id="{{ $d->id }}"
                                data-name="{{ $d->name }}"
                                data-description="{{ $d->description }}"
                                data-is-active="{{ $d->is_active ? '1' : '0' }}"
                            >
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

    {{-- ── First Parties tab ───────────────────────────────────── --}}
    @elseif($tab === 'first-parties')
        <div class="d-flex align-items-center justify-content-between mb-3">
            <small class="text-muted">Master pihak pertama untuk dipakai ulang pada dokumen kontrak dan dokumen legal lainnya.</small>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFirstPartyModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Pihak Pertama
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Perusahaan</th>
                        <th>Perwakilan</th>
                        <th>Jabatan</th>
                        <th>Alamat</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($firstParties as $fp)
                    <tr>
                        <td class="fw-semibold">{{ $fp->name }}</td>
                        <td>{{ $fp->representative_name ?: '—' }}</td>
                        <td>{{ $fp->representative_position ?: '—' }}</td>
                        <td class="text-muted small">{{ $fp->address ?: '—' }}</td>
                        <td class="text-center">
                            @if($fp->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button
                                class="btn btn-sm btn-outline-secondary btn-edit-first-party"
                                data-id="{{ $fp->id }}"
                                data-name="{{ $fp->name }}"
                                data-representative-name="{{ $fp->representative_name }}"
                                data-representative-position="{{ $fp->representative_position }}"
                                data-address="{{ $fp->address }}"
                                data-is-active="{{ $fp->is_active ? '1' : '0' }}"
                            >
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('master-data.first-parties.destroy', $fp) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus data pihak pertama {{ $fp->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data pihak pertama.</td></tr>
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

{{-- ── Modals: First Parties ───────────────────────────────────── --}}
<div class="modal fade" id="addFirstPartyModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('master-data.first-parties.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Tambah Pihak Pertama</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                        <input name="name" class="form-control" required maxlength="255" placeholder="Contoh: PT Lingkar Inovasi Muda">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Perwakilan</label>
                        <input name="representative_name" class="form-control" maxlength="255" placeholder="Contoh: Budi Santoso">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jabatan Perwakilan</label>
                        <input name="representative_position" class="form-control" maxlength="255" placeholder="Contoh: Direktur Utama">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addFpActive" checked>
                        <label class="form-check-label" for="addFpActive">Aktif (muncul di form dokumen)</label>
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

<div class="modal fade" id="editFirstPartyModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editFirstPartyForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Pihak Pertama</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                        <input name="name" id="edit-fp-name" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Perwakilan</label>
                        <input name="representative_name" id="edit-fp-representative-name" class="form-control" maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jabatan Perwakilan</label>
                        <input name="representative_position" id="edit-fp-representative-position" class="form-control" maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" id="edit-fp-address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit-fp-active">
                        <label class="form-check-label" for="edit-fp-active">Aktif (muncul di form dokumen)</label>
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
function editFirstParty(id, name, representativeName, representativePosition, address, isActive) {
    document.getElementById('editFirstPartyForm').action = '{{ url("master-data/first-parties") }}/' + id;
    document.getElementById('edit-fp-name').value = name || '';
    document.getElementById('edit-fp-representative-name').value = representativeName || '';
    document.getElementById('edit-fp-representative-position').value = representativePosition || '';
    document.getElementById('edit-fp-address').value = address || '';
    document.getElementById('edit-fp-active').checked = !!isActive;
    new bootstrap.Modal(document.getElementById('editFirstPartyModal')).show();
}

document.addEventListener('click', function (event) {
    const positionButton = event.target.closest('.btn-edit-position');
    if (positionButton) {
        editPosition(
            positionButton.dataset.id,
            positionButton.dataset.name,
            positionButton.dataset.description,
            positionButton.dataset.isActive === '1'
        );
        return;
    }

    const departmentButton = event.target.closest('.btn-edit-department');
    if (departmentButton) {
        editDepartment(
            departmentButton.dataset.id,
            departmentButton.dataset.name,
            departmentButton.dataset.description,
            departmentButton.dataset.isActive === '1'
        );
        return;
    }

    const firstPartyButton = event.target.closest('.btn-edit-first-party');
    if (firstPartyButton) {
        editFirstParty(
            firstPartyButton.dataset.id,
            firstPartyButton.dataset.name,
            firstPartyButton.dataset.representativeName,
            firstPartyButton.dataset.representativePosition,
            firstPartyButton.dataset.address,
            firstPartyButton.dataset.isActive === '1'
        );
    }
});
</script>
@endpush
