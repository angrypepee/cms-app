@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')
@section('breadcrumb', 'Pengguna')

@section('content')

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-check-circle-fill"></i>{{ session('success') }}
    </div>
@endif

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-people-fill me-2 text-primary"></i>Daftar Pengguna</span>
        <span class="badge bg-secondary">{{ $users->count() }} akun</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Jabatan/Title</th>
                    <th>Role</th>
                    <th>Terdaftar</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td class="fw-medium">
                        {{ $user->name }}
                        @if($user->id === auth()->id())
                            <span class="badge bg-primary bg-opacity-10 text-primary ms-1" style="font-size:.65rem">Anda</span>
                        @endif
                    </td>
                    <td class="text-muted" style="font-size:.82rem">{{ $user->email }}</td>
                    <td class="text-muted" style="font-size:.82rem">{{ $user->title ?? '—' }}</td>
                    <td>
                        @if($user->role)
                            <span class="badge bg-{{ $user->role->badgeColor() }} bg-opacity-10 text-{{ $user->role->badgeColor() }}" style="font-size:.72rem">
                                {{ $user->role->label() }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-muted" style="font-size:.78rem">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-shield-lock me-1"></i>Edit Role
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-warning"
                            data-bs-toggle="modal"
                            data-bs-target="#resetModal"
                            data-user-id="{{ $user->id }}"
                            data-user-name="{{ $user->name }}">
                            <i class="bi bi-key me-1"></i>Reset Password
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Reset Password Modal --}}
<div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="resetModalLabel"><i class="bi bi-key me-2 text-warning"></i>Reset Password</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resetForm" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p class="text-muted mb-3" style="font-size:.85rem">
                        Reset password untuk: <strong id="resetUserName"></strong>
                    </p>
                    @if($errors->has('new_password'))
                        <div class="alert alert-danger py-2" style="font-size:.83rem">{{ $errors->first('new_password') }}</div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.85rem">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" class="form-control form-control-sm"
                            required minlength="8" placeholder="Min. 8 karakter">
                    </div>
                    <div class="mb-1">
                        <label class="form-label" style="font-size:.85rem">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_password_confirmation" class="form-control form-control-sm"
                            required placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-warning"><i class="bi bi-key me-1"></i>Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('resetModal').addEventListener('show.bs.modal', function(e) {
    var btn = e.relatedTarget;
    document.getElementById('resetUserName').textContent = btn.dataset.userName;
    document.getElementById('resetForm').action = '{{ url("users") }}/' + btn.dataset.userId + '/reset-password';
    // clear inputs each open
    this.querySelectorAll('input[type=password]').forEach(function(i){ i.value=''; });
});
</script>
@endpush

@endsection
