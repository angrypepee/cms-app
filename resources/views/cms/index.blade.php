@extends('layouts.app')

@section('title', 'CMS')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1" style="font-size:1.15rem;font-weight:700"><i class="bi bi-palette me-2 text-primary"></i>CMS — Branding &amp; Logo</h4>
        <div class="text-muted" style="font-size:.82rem">Kelola logo aplikasi dan logo perusahaan yang tampil pada slip gaji.</div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success py-2" style="font-size:.85rem">{{ session('success') }}</div>
@endif
@if(session('warning'))
    <div class="alert alert-warning py-2" style="font-size:.85rem">{{ session('warning') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger py-2" style="font-size:.85rem">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
@endif

<ul class="nav nav-tabs mb-3" id="cmsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-app" data-bs-toggle="tab" data-bs-target="#pane-app" type="button">
            <i class="bi bi-app-indicator me-1"></i> Logo Aplikasi
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-comp" data-bs-toggle="tab" data-bs-target="#pane-comp" type="button">
            <i class="bi bi-buildings me-1"></i> Logo Perusahaan
            <span class="badge bg-light text-dark ms-1">{{ $companies->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content">

    {{-- ── App Branding Tab ── --}}
    <div class="tab-pane fade show active" id="pane-app" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('cms.app-branding.update') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <label class="form-label small fw-semibold d-block">Preview</label>
                            <div class="border rounded p-3 d-inline-flex align-items-center justify-content-center"
                                 style="width:140px;height:140px;background:#f8fafc">
                                @if($appLogo)
                                    <img src="{{ asset('storage/'.$appLogo) }}" alt="App Logo"
                                         style="max-width:100%;max-height:100%;object-fit:contain">
                                @else
                                    <i class="bi bi-image text-muted" style="font-size:2.4rem"></i>
                                @endif
                            </div>
                            <div class="text-muted mt-2" style="font-size:.72rem">
                                Tampil di sidebar &amp; halaman login.
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Nama Aplikasi</label>
                                <input type="text" name="app_name" class="form-control" maxlength="100"
                                       value="{{ old('app_name', $appName) }}" placeholder="LIM Management">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tagline</label>
                                <input type="text" name="app_tagline" class="form-control" maxlength="150"
                                       value="{{ old('app_tagline', $appTagline) }}" placeholder="Sistem Penggajian">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Upload Logo Aplikasi</label>
                                <input type="file" name="app_logo" class="form-control"
                                       accept=".jpg,.jpeg,.png,.svg,.webp">
                                <div class="form-text" style="font-size:.72rem">
                                    JPG, PNG, SVG, atau WebP — maks. 1 MB. Direkomendasikan kotak (1:1) dengan background transparan.
                                </div>
                            </div>

                            @if($appLogo)
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="removeAppLogo"
                                           name="remove_logo" value="1">
                                    <label for="removeAppLogo" class="form-check-label small text-danger">
                                        Hapus logo aplikasi (kembali ke ikon default)
                                    </label>
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-save me-1"></i> Simpan Branding Aplikasi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Company Logo Tab ── --}}
    <div class="tab-pane fade" id="pane-comp" role="tabpanel">
        <div class="card">
            <div class="card-body p-0">
                @if($companies->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-buildings fs-1 d-block mb-2 opacity-25"></i>
                        Belum ada perusahaan terdaftar.
                        <div class="mt-2">
                            <a href="{{ route('companies.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Perusahaan
                            </a>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info border-0 rounded-0 mb-0 py-2" style="font-size:.8rem">
                        <i class="bi bi-info-circle me-1"></i>
                        Logo perusahaan akan otomatis tampil pada <strong>slip gaji</strong> &amp; PDF cetak untuk karyawan yang terkait perusahaan tersebut.
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead style="font-size:.8rem">
                                <tr>
                                    <th style="width:90px">Logo</th>
                                    <th>Perusahaan</th>
                                    <th style="min-width:340px">Ganti Logo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companies as $c)
                                <tr>
                                    <td>
                                        <div class="border rounded d-inline-flex align-items-center justify-content-center"
                                             style="width:64px;height:64px;background:#f8fafc">
                                            @if($c->logo)
                                                <img src="{{ asset('storage/'.$c->logo) }}" alt="{{ $c->name }}"
                                                     style="max-width:100%;max-height:100%;object-fit:contain">
                                            @else
                                                <i class="bi bi-building text-muted" style="font-size:1.4rem"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="font-size:.88rem">{{ $c->name }}</div>
                                        @if($c->tagline)
                                            <div class="text-muted" style="font-size:.74rem">{{ $c->tagline }}</div>
                                        @endif
                                        <div class="text-muted" style="font-size:.72rem">
                                            <i class="bi bi-people"></i> {{ $c->employees()->count() }} karyawan
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('cms.companies.logo.update', $c) }}"
                                              enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-start">
                                            @csrf
                                            <input type="file" name="logo" class="form-control form-control-sm"
                                                   accept=".jpg,.jpeg,.png,.svg,.webp" style="max-width:230px">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bi bi-upload"></i> Upload
                                            </button>
                                            @if($c->logo)
                                                <button type="submit" name="remove_logo" value="1"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Hapus logo {{ $c->name }}?');">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </form>
                                        <div class="form-text mt-1" style="font-size:.7rem">JPG/PNG/SVG/WebP — maks. 1 MB.</div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
