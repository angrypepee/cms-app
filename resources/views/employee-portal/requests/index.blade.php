@extends('layouts.app')

@section('page-title', 'Permohonan Saya')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Permohonan Saya</h4>
        <p class="text-muted small mb-0">Kirim permohonan, pengaduan, atau surat keterangan ke admin</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#buatPermohonanModal">
        <i class="bi bi-plus-lg me-1"></i> Buat Permohonan
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tipe</th>
                        <th>Subjek</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>
                                <span class="badge {{ $req->typeBadgeClass() }} badge-pill">{{ $req->typeLabel() }}</span>
                            </td>
                            <td class="small" style="max-width:250px">
                                <div class="text-truncate" title="{{ $req->subject }}">{{ $req->subject }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $req->statusBadgeClass() }} badge-pill">{{ $req->statusLabel() }}</span>
                            </td>
                            <td class="small text-muted">{{ $req->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ route('my.requests.show', $req) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada permohonan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($requests->hasPages())
        <div class="card-footer">{{ $requests->links() }}</div>
    @endif
</div>

{{-- Buat Permohonan Modal --}}
<div class="modal fade" id="buatPermohonanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('my.requests.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Permohonan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Tipe Permohonan <span class="text-danger">*</span></label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">-- Pilih tipe --</option>
                            <option value="permohonan"       @selected(old('type')=='permohonan')>Permohonan</option>
                            <option value="pendanaan"        @selected(old('type')=='pendanaan')>Pendanaan / Pinjaman</option>
                            <option value="surat_keterangan" @selected(old('type')=='surat_keterangan')>Surat Keterangan</option>
                            <option value="pengaduan"        @selected(old('type')=='pengaduan')>Pengaduan</option>
                            <option value="lainnya"          @selected(old('type')=='lainnya')>Lainnya</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subjek <span class="text-danger">*</span></label>
                        <input name="subject" class="form-control @error('subject') is-invalid @enderror"
                               value="{{ old('subject') }}" required maxlength="200" placeholder="Judul singkat permohonan">
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pesan <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control @error('message') is-invalid @enderror"
                            rows="5" required minlength="10" maxlength="3000"
                            placeholder="Jelaskan permohonan Anda secara lengkap...">{{ old('message') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Permohonan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('buatPermohonanModal')).show();
});
@endif
</script>
@endpush
