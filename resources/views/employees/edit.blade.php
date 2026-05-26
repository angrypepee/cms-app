@extends('layouts.app')
@section('title', 'Edit Karyawan')
@section('page-title', 'Edit Karyawan')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit: {{ $employee->name }}</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('employees.update', $employee) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Perusahaan <span class="text-danger">*</span></label>
                            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Perusahaan --</option>
                                @foreach($companies as $co)
                                    <option value="{{ $co->id }}" {{ old('company_id', $employee->company_id) == $co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                                @endforeach
                            </select>
                            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">ID Karyawan</label>
                            <input type="text" class="form-control font-monospace bg-light" value="{{ $employee->employee_id }}" readonly disabled>
                            <div class="form-text" style="font-size:.72rem"><i class="bi bi-lock me-1"></i>ID karyawan dibuat otomatis oleh sistem dan tidak dapat diubah.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            @php $curPos = old('position', $employee->position); $hasPos = $positions->contains('name', $curPos); @endphp
                            <label class="form-label fw-medium">Jabatan</label>
                            <select name="position" class="form-select @error('position') is-invalid @enderror">
                                <option value="">— Pilih Jabatan —</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->name }}" {{ $curPos === $pos->name ? 'selected' : '' }}>{{ $pos->name }}</option>
                                @endforeach
                                @if($curPos && !$hasPos)
                                    <option value="{{ $curPos }}" selected>{{ $curPos }} (legacy)</option>
                                @endif
                            </select>
                            <small class="text-muted">Belum ada? <a href="{{ route('master-data.index', ['tab'=>'positions']) }}" target="_blank">Kelola di Data Master</a></small>
                            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            @php $curDept = old('department', $employee->department); $hasDept = $departments->contains('name', $curDept); @endphp
                            <label class="form-label fw-medium">Departemen</label>
                            <select name="department" class="form-select @error('department') is-invalid @enderror">
                                <option value="">— Pilih Departemen —</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->name }}" {{ $curDept === $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                                @if($curDept && !$hasDept)
                                    <option value="{{ $curDept }}" selected>{{ $curDept }} (legacy)</option>
                                @endif
                            </select>
                            <small class="text-muted">Belum ada? <a href="{{ route('master-data.index', ['tab'=>'departments']) }}" target="_blank">Kelola di Data Master</a></small>
                            @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Kategori Karyawan <span class="text-danger">*</span></label>
                            <select name="employee_category" class="form-select @error('employee_category') is-invalid @enderror" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->value }}" {{ old('employee_category', $employee->employee_category?->value) === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                                @endforeach
                            </select>
                            @error('employee_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Gaji Pokok <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="base_salary" class="form-control @error('base_salary') is-invalid @enderror" value="{{ old('base_salary', $employee->base_salary) }}" min="0" step="1000" required>
                                @error('base_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text" style="font-size:.72rem">Digunakan untuk auto-generate slip gaji saat transfer.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Grade / Level</label>
                            <input type="text" name="grade" class="form-control @error('grade') is-invalid @enderror"
                                value="{{ old('grade', $employee->grade) }}" placeholder="Cth: G3, Senior, Junior">
                            @error('grade')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->email) }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">No. Telepon</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tanggal Bergabung</label>
                            <input type="date" name="join_date" class="form-control @error('join_date') is-invalid @enderror" value="{{ old('join_date', $employee->join_date?->format('Y-m-d')) }}">
                            @error('join_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Contract Dates --}}
                        <div class="col-12 mt-2">
                            <p class="fw-semibold text-muted text-uppercase mb-2" style="font-size:.7rem;letter-spacing:.07em">Kontrak Kerja</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tanggal Mulai Kontrak</label>
                            <input type="date" name="contract_start" class="form-control @error('contract_start') is-invalid @enderror"
                                value="{{ old('contract_start', $employee->contract_start?->format('Y-m-d')) }}">
                            @error('contract_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tanggal Berakhir Kontrak</label>
                            <input type="date" name="contract_end" class="form-control @error('contract_end') is-invalid @enderror"
                                value="{{ old('contract_end', $employee->contract_end?->format('Y-m-d')) }}">
                            <div class="form-text" style="font-size:.72rem">Kosongkan jika karyawan tetap / tanpa batas waktu.</div>
                            @error('contract_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="isActive">Karyawan Aktif</label>
                            </div>
                        </div>

                        {{-- Salary Agreement (used to auto-generate monthly slips) --}}
                        <div class="col-12 mt-2">
                            <p class="fw-semibold text-muted text-uppercase mb-2" style="font-size:.7rem;letter-spacing:.07em">
                                <i class="bi bi-file-earmark-ruled me-1"></i>Kesepakatan Gaji (Tunjangan & Potongan)
                            </p>
                            <div class="alert alert-info py-2 mb-2" style="font-size:.78rem">
                                Komponen di sini akan otomatis dipakai setiap bulan saat <strong>Transfer Payroll</strong>. <em>Gaji Pokok</em> sudah otomatis ditambahkan dari kolom di atas — Anda hanya perlu mengisi tunjangan & potongan lainnya.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle" id="salaryComponentsTable" style="font-size:.85rem">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:45%">Label Komponen</th>
                                            <th style="width:20%">Jenis</th>
                                            <th style="width:25%">Jumlah (Rp)</th>
                                            <th style="width:10%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="salaryComponentsBody">
                                        @php $existing = old('salary_components', $employee->salary_components ?? []); @endphp
                                        @forelse($existing as $i => $row)
                                            <tr>
                                                <td><input type="text" name="salary_components[{{ $i }}][label]" class="form-control form-control-sm" value="{{ $row['label'] ?? '' }}" placeholder="cth. Uang Makan"></td>
                                                <td>
                                                    <select name="salary_components[{{ $i }}][type]" class="form-select form-select-sm">
                                                        <option value="income"    {{ ($row['type'] ?? '') === 'income'    ? 'selected' : '' }}>Pendapatan</option>
                                                        <option value="deduction" {{ ($row['type'] ?? '') === 'deduction' ? 'selected' : '' }}>Potongan</option>
                                                    </select>
                                                </td>
                                                <td><input type="number" min="0" step="1000" name="salary_components[{{ $i }}][amount]" class="form-control form-control-sm" value="{{ $row['amount'] ?? '' }}" placeholder="0"></td>
                                                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="bi bi-x-lg"></i></button></td>
                                            </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addSalaryComponentBtn">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Komponen
                            </button>
                        </div>

                        {{-- BPJS & Financial Data --}}
                        <div class="col-12 mt-2">
                            <p class="fw-semibold text-muted text-uppercase mb-2" style="font-size:.7rem;letter-spacing:.07em">BPJS & Data Keuangan</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">No. BPJS Kesehatan</label>
                            <input type="text" name="bpjs_kesehatan" class="form-control @error('bpjs_kesehatan') is-invalid @enderror"
                                value="{{ old('bpjs_kesehatan', $employee->bpjs_kesehatan) }}"
                                placeholder="Kosongkan jika belum terdaftar">
                            @error('bpjs_kesehatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">No. BPJS Ketenagakerjaan</label>
                            <input type="text" name="bpjs_ketenagakerjaan" class="form-control @error('bpjs_ketenagakerjaan') is-invalid @enderror"
                                value="{{ old('bpjs_ketenagakerjaan', $employee->bpjs_ketenagakerjaan) }}"
                                placeholder="Kosongkan jika belum terdaftar">
                            @error('bpjs_ketenagakerjaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">NPWP</label>
                            <input type="text" name="npwp" class="form-control @error('npwp') is-invalid @enderror"
                                value="{{ old('npwp', $employee->npwp) }}"
                                placeholder="Nomor NPWP">
                            @error('npwp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nama Bank</label>
                            <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                                value="{{ old('bank_name', $employee->bank_name) }}"
                                placeholder="Contoh: BCA, Mandiri, BNI">
                            @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">No. Rekening</label>
                            <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror"
                                value="{{ old('bank_account', $employee->bank_account) }}"
                                placeholder="Nomor rekening bank">
                            @error('bank_account')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 d-flex gap-2 justify-content-end pt-2">
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Login Account Card --}}
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-person-lock me-2 text-primary"></i>Akun Login Karyawan</span>
            </div>
            <div class="card-body px-4 py-4">
                @if($employee->hasLoginAccount())
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0">
                            <i class="bi bi-person-check-fill text-success fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $employee->user->email }}</div>
                            <div class="text-muted" style="font-size:.82rem">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $employee->user->role->label() }}</span>
                                &nbsp;·&nbsp; Dibuat {{ $employee->user->created_at->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#resetEmpPassModal">
                            <i class="bi bi-key me-1"></i>Reset Password
                        </button>
                        <form method="POST" action="{{ route('employees.revoke-account', $employee) }}"
                              onsubmit="return confirm('Hapus akun login {{ $employee->name }}? Karyawan tidak akan bisa login lagi.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-person-x me-1"></i>Hapus Akun Login
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-muted mb-3" style="font-size:.9rem">
                        <i class="bi bi-info-circle me-1"></i>
                        Karyawan ini belum memiliki akun login. Buat akun agar karyawan dapat login dan melihat data mereka sendiri.
                    </p>
                    <form method="POST" action="{{ route('employees.create-account', $employee) }}" class="row g-3" style="max-width:480px">
                        @csrf
                        <div class="col-12">
                            <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="email@karyawan.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   minlength="8" required autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-plus me-1"></i>Buat Akun Login
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Reset Password Modal --}}
@if($employee->hasLoginAccount())
<div class="modal fade" id="resetEmpPassModal" tabindex="-1" aria-labelledby="resetEmpPassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetEmpPassModalLabel">
                    <i class="bi bi-key me-2 text-warning"></i>Reset Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('users.reset-password', $employee->user) }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p class="text-muted mb-3" style="font-size:.88rem">
                        Reset password untuk <strong>{{ $employee->name }}</strong> ({{ $employee->user->email }}).
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" minlength="8" required autocomplete="new-password">
                        @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label fw-medium">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-key me-1"></i>Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($errors->has('new_password') || $errors->has('new_password_confirmation'))
        var modal = document.getElementById('resetEmpPassModal');
        if (modal) { new bootstrap.Modal(modal).show(); }
    @endif

    // Salary agreement: add component row
    (function() {
        var btn = document.getElementById('addSalaryComponentBtn');
        var body = document.getElementById('salaryComponentsBody');
        if (!btn || !body) return;
        btn.addEventListener('click', function() {
            var idx = body.querySelectorAll('tr').length;
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td><input type="text" name="salary_components['+idx+'][label]" class="form-control form-control-sm" placeholder="cth. Tunjangan Jabatan"></td>'+
                '<td><select name="salary_components['+idx+'][type]" class="form-select form-select-sm">'+
                    '<option value="income">Pendapatan</option><option value="deduction">Potongan</option>'+
                '</select></td>'+
                '<td><input type="number" min="0" step="1000" name="salary_components['+idx+'][amount]" class="form-control form-control-sm" placeholder="0"></td>'+
                '<td><button type="button" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button></td>';
            tr.querySelector('button').addEventListener('click', function() { tr.remove(); });
            body.appendChild(tr);
        });
    })();
});
</script>
@endpush
@endsection
