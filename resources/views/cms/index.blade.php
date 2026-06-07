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
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-contract-template" data-bs-toggle="tab" data-bs-target="#pane-contract-template" type="button">
            <i class="bi bi-file-earmark-text me-1"></i> Template Kontrak
        </button></li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-repo" data-bs-toggle="tab" data-bs-target="#pane-repo" type="button">
            <i class="bi bi-github me-1"></i> Integrasi Repo
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

    {{-- ── Contract Template Tab ── --}}
    <div class="tab-pane fade" id="pane-contract-template" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-info py-2" style="font-size:.82rem">
                    Template ini digunakan saat tombol <strong>Isi Contoh Otomatis</strong> di menu Dokumen Kontrak ditekan.
                </div>

                <form method="POST" action="{{ route('cms.contract-template.update') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Lokasi</label>
                            <input type="text" name="location" class="form-control" value="{{ $contractTemplate['location'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nama Proyek / Pekerjaan</label>
                            <input type="text" name="project_name" class="form-control" value="{{ $contractTemplate['project_name'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Durasi Default</label>
                            <input type="text" name="duration_text" class="form-control" value="{{ $contractTemplate['duration_text'] ?? '' }}" placeholder="contoh: 4 bulan">
                        </div>

                        <div class="col-12"><div class="border-top pt-3 mt-1"><h6 class="mb-0">Pihak Pertama & Kedua</h6></div></div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nama Pihak Pertama</label>
                            <input type="text" name="first_party_name" class="form-control" value="{{ $contractTemplate['first_party_name'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jabatan Pihak Pertama</label>
                            <input type="text" name="first_party_position" class="form-control" value="{{ $contractTemplate['first_party_position'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Perusahaan Pihak Pertama</label>
                            <input type="text" name="first_party_company" class="form-control" value="{{ $contractTemplate['first_party_company'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Alamat Pihak Pertama</label>
                            <textarea name="first_party_address" rows="3" class="form-control">{{ $contractTemplate['first_party_address'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Alamat Pihak Kedua</label>
                            <textarea name="second_party_address" rows="3" class="form-control">{{ $contractTemplate['second_party_address'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nama Pihak Kedua</label>
                            <input type="text" name="second_party_name" class="form-control" value="{{ $contractTemplate['second_party_name'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">No KTP Pihak Kedua</label>
                            <input type="text" name="second_party_ktp" class="form-control" value="{{ $contractTemplate['second_party_ktp'] ?? '' }}">
                        </div>

                        <div class="col-12"><div class="border-top pt-3 mt-1"><h6 class="mb-0">Isi Perjanjian</h6></div></div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;letter-spacing:.03em">Pasal 1</span>
                                Ruang Lingkup Pekerjaan
                            </label>
                            <textarea name="scope_of_work" rows="5" class="form-control js-cms-contract-richtext">{{ $contractTemplate['scope_of_work'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;letter-spacing:.03em">Pasal 2</span>
                                Hak &amp; Kewajiban Para Pihak
                            </label>
                            <textarea name="rights_obligations" rows="5" class="form-control js-cms-contract-richtext">{{ $contractTemplate['rights_obligations'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;letter-spacing:.03em">Pasal 3</span>
                                Hak Kekayaan Intelektual (HKI)
                            </label>
                            <textarea name="hki_terms" rows="5" class="form-control js-cms-contract-richtext">{{ $contractTemplate['hki_terms'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;letter-spacing:.03em">Pasal 4</span>
                                Kerahasiaan / NDA
                            </label>
                            <textarea name="nda_terms" rows="5" class="form-control js-cms-contract-richtext">{{ $contractTemplate['nda_terms'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;letter-spacing:.03em">Pasal 5</span>
                                Berakhirnya Perintah Kerja &amp; Sanksi
                            </label>
                            <textarea name="sanctions_terms" rows="5" class="form-control js-cms-contract-richtext">{{ $contractTemplate['sanctions_terms'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;letter-spacing:.03em">Pasal 6</span>
                                Penyelesaian Perselisihan
                            </label>
                            <textarea name="dispute_terms" rows="4" class="form-control js-cms-contract-richtext">{{ $contractTemplate['dispute_terms'] ?? '' }}</textarea>
                        </div>

                        <div class="col-12"><div class="border-top pt-3 mt-1"><h6 class="mb-0">Pembayaran</h6></div></div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Metode Pembayaran</label>
                            <input type="text" name="payment_method" class="form-control" value="{{ $contractTemplate['payment_method'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Nilai Kontrak</label>
                            <input type="text" name="contract_value" class="form-control" value="{{ $contractTemplate['contract_value'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nilai Kontrak (terbilang)</label>
                            <input type="text" name="contract_value_text" class="form-control" value="{{ $contractTemplate['contract_value_text'] ?? '' }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-2">
                                <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:.7rem;letter-spacing:.03em">Lampiran</span>
                                Rincian / Termin Pembayaran
                            </label>
                            <textarea name="payment_terms" rows="4" class="form-control js-cms-contract-richtext">{{ $contractTemplate['payment_terms'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nama Bank</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ $contractTemplate['bank_name'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">No Rekening</label>
                            <input type="text" name="bank_account" class="form-control" value="{{ $contractTemplate['bank_account'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Atas Nama</label>
                            <input type="text" name="bank_account_name" class="form-control" value="{{ $contractTemplate['bank_account_name'] ?? '' }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Catatan Penutup</label>
                            <textarea name="notes" rows="4" class="form-control js-cms-contract-richtext">{{ $contractTemplate['notes'] ?? '' }}</textarea>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-save me-1"></i> Simpan Template Kontrak
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Tab: Integrasi Repo --}}
    <div class="tab-pane fade" id="pane-repo" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-github me-2 text-primary"></i>Token Akses GitHub &amp; GitLab</span>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2 mb-4" style="font-size:.82rem">
                    <i class="bi bi-info-circle me-1"></i>
                    Token digunakan untuk membaca data kontributor dari repository project. Token disimpan terenkripsi dan hanya digunakan untuk membaca data (<code>read:user</code>, <code>repo:read</code>). Biarkan kosong jika repo bersifat publik.
                </div>
                <form method="POST" action="{{ route('cms.repo-tokens.update') }}">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="bi bi-github me-1"></i> GitHub Personal Access Token</label>
                            <input type="password" name="github_token" class="form-control"
                                value="{{ $githubToken ? str_repeat('•', 20) : '' }}"
                                placeholder="ghp_xxxxxxxxxxxxxxxxxxxx"
                                autocomplete="off">
                            <div class="form-text" style="font-size:.75rem">
                                Buat di: <a href="https://github.com/settings/tokens" target="_blank">github.com/settings/tokens</a> — scope: <code>read:user</code>, <code>repo</code>
                                @if($githubToken)<span class="badge bg-success bg-opacity-10 text-success ms-2">Token tersimpan</span>@endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="bi bi-gitlab me-1"></i> GitLab Personal Access Token</label>
                            <input type="password" name="gitlab_token" class="form-control"
                                value="{{ $gitlabToken ? str_repeat('•', 20) : '' }}"
                                placeholder="glpat-xxxxxxxxxxxxxxxxxxxx"
                                autocomplete="off">
                            <div class="form-text" style="font-size:.75rem">
                                Buat di: <a href="https://gitlab.com/-/user_settings/personal_access_tokens" target="_blank">gitlab.com/-/user_settings/personal_access_tokens</a> — scope: <code>read_api</code>
                                @if($gitlabToken)<span class="badge bg-success bg-opacity-10 text-success ms-2">Token tersimpan</span>@endif
                            </div>
                        </div>
                        <div class="col-12 d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-save me-1"></i> Simpan Token
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
    const templateForm = document.querySelector('form[action*="cms/contract-template"]');

    if (window.tinymce) {
        tinymce.init({
            selector: 'textarea.js-cms-contract-richtext',
            menubar: false,
            min_height: 220,
            plugins: 'lists link table code autolink help wordcount',
            toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | alignleft aligncenter alignright | link table | removeformat code',
            branding: false,
            promotion: false,
            statusbar: true,
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; }',
            setup: function (editor) {
                editor.on('change keyup setcontent', function () {
                    editor.save();
                });
            }
        });
    }

    if (templateForm) {
        templateForm.addEventListener('submit', function () {
            if (window.tinymce) {
                tinymce.triggerSave();
            }
        });
    }

    const hash = window.location.hash;
    if (!hash) return;

    const trigger = document.querySelector(`[data-bs-target="${hash}"]`);
    if (!trigger || !window.bootstrap || !bootstrap.Tab) return;

    bootstrap.Tab.getOrCreateInstance(trigger).show();
})();
</script>
@endpush
