@extends('layouts.app')

@section('page-title', 'Buat Pengumuman')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <h4 class="mb-0 fw-bold">Buat Pengumuman</h4>
</div>

<div class="card" style="max-width:760px">
    <div class="card-body">
        <form method="POST" action="{{ route('announcements.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Judul <span class="text-danger">*</span></label>
                <input name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title') }}" required maxlength="200" placeholder="Judul pengumuman">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Isi Pengumuman <span class="text-danger">*</span></label>
                <textarea name="content" rows="8"
                    class="form-control @error('content') is-invalid @enderror"
                    required placeholder="Tulis isi pengumuman di sini...">{{ old('content') }}</textarea>
                @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Perusahaan</label>
                    <select name="company_id" class="form-select">
                        <option value="">Semua Perusahaan</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" @selected(old('company_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Biarkan kosong untuk semua perusahaan</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Publikasi</label>
                    <input name="published_at" type="datetime-local" class="form-control"
                           value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}">
                    <div class="form-text">Kosongkan untuk simpan sebagai draft</div>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kedaluwarsa</label>
                    <input name="expires_at" type="datetime-local" class="form-control" value="{{ old('expires_at') }}">
                    <div class="form-text">Opsional</div>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" value="1"
                               @checked(old('is_pinned'))>
                        <label class="form-check-label" for="is_pinned">
                            <i class="bi bi-pin me-1"></i> Sematkan di atas
                        </label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i> Publikasikan
                </button>
                <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
