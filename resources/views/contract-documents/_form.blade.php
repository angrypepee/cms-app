@csrf
@php
    $documentModel = $contractDocument ?? null;
    $selectedEmployee = old('employee_id', $documentModel?->employee_id ?? $selectedEmployeeId ?? request('employee_id'));
    $generatedNumber = old('contract_number', $documentModel?->contract_number ?? $suggestedContractNumber ?? '');
    $contractDate = old('contract_date', $documentModel?->contract_date?->format('Y-m-d'));
    $startDate = old('start_date', $documentModel?->start_date?->format('Y-m-d'));
    $endDate = old('end_date', $documentModel?->end_date?->format('Y-m-d'));
    $partyOne = old('first_party_name', $documentModel?->first_party_name);
    $partyTwo = old('second_party_name', $documentModel?->second_party_name);
    $companyName = $documentModel?->employee?->company?->name;
    $contractTemplate = $contractTemplate ?? [];
    $firstParties = $firstParties ?? collect();
    $firstPartiesJson = $firstParties->map(function ($fp) {
        return [
            'id' => $fp->id,
            'name' => $fp->name,
            'representative_name' => $fp->representative_name,
            'representative_position' => $fp->representative_position,
            'address' => $fp->address,
        ];
    })->values();@endphp

<div class="row g-4">
    <div class="col-lg-7">
        <div class="alert alert-info py-2 mb-3" style="font-size:.85rem">
            Isi form di kiri seperti menyusun surat perjanjian kerja. Panel kanan menampilkan template dokumen agar hasil input menyerupai surat resmi.
        </div>
        <div class="d-flex justify-content-end mb-3 gap-2">
            <a href="{{ route('cms.index') }}#pane-contract-template" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-gear me-1"></i>Edit Template
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm" id="autofill-contract-template">
                <i class="bi bi-magic me-1"></i>Isi Contoh Otomatis
            </button>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Karyawan *</label>
                <select id="contract-employee-select" name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ (string) $selectedEmployee === (string) $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }} - {{ $employee->company->name ?? '-' }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Nomor Kontrak *</label>
                <div class="input-group">
                    <input type="text" name="contract_number" class="form-control @error('contract_number') is-invalid @enderror" value="{{ $generatedNumber }}" placeholder="Contoh: 001/SPK/LIM/06/2026">
                    <button type="button" class="btn btn-outline-secondary" id="generate-contract-number">Generate</button>
                </div>
                <div class="form-text" style="font-size:.72rem">Nomor otomatis format: XXX/SPK/LIM/BULAN/TAHUN, dimulai dari 234.</div>
                @error('contract_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <div id="employee-supporting-data" class="border rounded p-3 bg-light" data-endpoint-template="{{ route('contract-documents.supporting-data', ['employee' => '__EMPLOYEE_ID__']) }}">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <div class="fw-semibold">Data Pendukung Karyawan</div>
                        <span id="supporting-data-badge" class="badge bg-secondary bg-opacity-10 text-secondary">Belum dipilih</span>
                    </div>
                    <div id="supporting-data-message" class="text-muted" style="font-size:.82rem">
                        Pilih karyawan terlebih dahulu untuk mengambil data pendukung kontrak.
                    </div>
                    <div id="supporting-data-details" class="row g-2 mt-1 d-none" style="font-size:.82rem">
                        <div class="col-md-6"><span class="text-muted">Nama:</span> <span id="supporting-employee-name">-</span></div>
                        <div class="col-md-6"><span class="text-muted">ID Karyawan:</span> <span id="supporting-employee-id">-</span></div>
                        <div class="col-md-6"><span class="text-muted">Perusahaan:</span> <span id="supporting-employee-company">-</span></div>
                        <div class="col-md-6"><span class="text-muted">Jabatan:</span> <span id="supporting-employee-position">-</span></div>
                        <div class="col-md-6"><span class="text-muted">NPWP:</span> <span id="supporting-employee-npwp">-</span></div>
                        <div class="col-md-6"><span class="text-muted">Bank:</span> <span id="supporting-employee-bank">-</span></div>
                    </div>
                    <div class="mt-2">
                        <div class="text-muted mb-1" style="font-size:.78rem">Checklist dokumen pendukung:</div>
                        <ul id="supporting-doc-checklist" class="small ps-3 mb-0 text-muted"></ul>
                    </div>
                    <div id="supporting-missing-alert" class="alert alert-warning py-2 px-3 mt-3 mb-0 d-none" style="font-size:.8rem"></div>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Kontrak</label>
                <input type="date" name="contract_date" class="form-control @error('contract_date') is-invalid @enderror" value="{{ $contractDate }}">
                @error('contract_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Lokasi</label>
                <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $documentModel?->location ?? '') }}">
                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Nama Proyek / Pekerjaan</label>
                <input type="text" name="project_name" class="form-control @error('project_name') is-invalid @enderror" value="{{ old('project_name', $documentModel?->project_name ?? '') }}">
                @error('project_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12"><div class="border-top pt-3 mt-1"><h6 class="mb-0">Pihak & Identitas</h6></div></div>
            <div class="col-12">
                <label class="form-label">Master Pihak Pertama</label>
                <select id="first-party-select" class="form-select">
                    <option value="">-- Pilih dari Master Data (opsional) --</option>
                    @foreach($firstParties as $firstParty)
                        <option value="{{ $firstParty->id }}">{{ $firstParty->name }}</option>
                    @endforeach
                </select>
                <div class="form-text" style="font-size:.72rem">
                    Pilihan ini akan mengisi otomatis data Pihak Pertama. Kelola data di menu Data Master > Pihak Pertama.
                </div>
            </div>
            <div class="col-md-4"><label class="form-label">Pihak Pertama (Nama Penanggung Jawab)</label><input type="text" name="first_party_name" class="form-control" value="{{ old('first_party_name', $documentModel?->first_party_name ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">Jabatan Pihak Pertama</label><input type="text" name="first_party_position" class="form-control" value="{{ old('first_party_position', $documentModel?->first_party_position ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">Perusahaan Pihak Pertama</label><input type="text" name="first_party_company" class="form-control" value="{{ old('first_party_company', $documentModel?->first_party_company ?? '') }}"></div>
            <div class="col-12"><label class="form-label">Alamat Pihak Pertama</label><textarea name="first_party_address" rows="2" class="form-control">{{ old('first_party_address', $documentModel?->first_party_address ?? '') }}</textarea></div>

            {{-- Penandatangan Pihak Pertama (default from master, editable) --}}
            <div class="col-12">
                <div class="alert alert-primary py-2 mb-0 d-flex align-items-center gap-2" style="font-size:.82rem">
                    <i class="bi bi-pen-fill flex-shrink-0"></i>
                    <span><strong>Penandatangan Kontrak</strong> — Diisi otomatis dari Master Pihak Pertama. Ubah jika diperlukan.</span>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">
                    Nama Penandatangan Pihak Pertama
                    <span class="badge bg-primary bg-opacity-10 text-primary ms-1" style="font-size:.68rem">HRD / Direktur</span>
                </label>
                <input type="text" name="penandatangan_p1_name" class="form-control"
                    value="{{ old('penandatangan_p1_name', $documentModel?->penandatangan_p1_name ?? '') }}"
                    placeholder="Nama yang menandatangani atas nama PT LIM">
                <div class="form-text" style="font-size:.72rem">Orang yang menandatangani kontrak atas nama Pihak Pertama.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Jabatan Penandatangan Pihak Pertama</label>
                <input type="text" name="penandatangan_p1_position" class="form-control"
                    value="{{ old('penandatangan_p1_position', $documentModel?->penandatangan_p1_position ?? '') }}"
                    placeholder="cth: HRD Manager, Direktur Utama">
            </div>

            <div class="col-md-4"><label class="form-label">Pihak Kedua</label><input type="text" name="second_party_name" class="form-control" value="{{ old('second_party_name', $documentModel?->second_party_name ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">No. KTP Pihak Kedua</label><input type="text" name="second_party_ktp" class="form-control" value="{{ old('second_party_ktp', $documentModel?->second_party_ktp ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">Durasi</label><input type="text" name="duration_text" class="form-control" value="{{ old('duration_text', $documentModel?->duration_text ?? '') }}" placeholder="contoh: 6 bulan / 14 hari"></div>
            <div class="col-md-6"><label class="form-label">Tanggal Mulai</label><input type="date" name="start_date" class="form-control" value="{{ $startDate }}"></div>
            <div class="col-md-6"><label class="form-label">Tanggal Selesai</label><input type="date" name="end_date" class="form-control" value="{{ $endDate }}"></div>
            <div class="col-12">
                <div class="form-text" style="font-size:.72rem">Durasi terintegrasi dengan tanggal mulai dan selesai. Jika isi durasi + tanggal mulai, tanggal selesai akan dihitung otomatis. Jika ubah tanggal mulai/selesai, durasi akan diperbarui otomatis.</div>
            </div>
            <div class="col-12"><label class="form-label">Alamat Pihak Kedua</label><textarea name="second_party_address" rows="2" class="form-control">{{ old('second_party_address', $documentModel?->second_party_address ?? '') }}</textarea></div>

            <div class="col-12"><div class="border-top pt-3 mt-1"><h6 class="mb-0">Isi Perjanjian</h6></div></div>
            <div class="col-md-6">
                <label class="form-label d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;letter-spacing:.03em">Pasal 1</span>
                    Ruang Lingkup Pekerjaan
                </label>
                <textarea name="scope_of_work" rows="5" class="form-control js-contract-richtext">{{ old('scope_of_work', $documentModel?->scope_of_work ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;letter-spacing:.03em">Pasal 2</span>
                    Hak &amp; Kewajiban Para Pihak
                </label>
                <textarea name="rights_obligations" rows="5" class="form-control js-contract-richtext">{{ old('rights_obligations', $documentModel?->rights_obligations ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;letter-spacing:.03em">Pasal 3</span>
                    Hak Kekayaan Intelektual (HKI)
                </label>
                <textarea name="hki_terms" rows="5" class="form-control js-contract-richtext">{{ old('hki_terms', $documentModel?->hki_terms ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;letter-spacing:.03em">Pasal 4</span>
                    Kerahasiaan / NDA
                </label>
                <textarea name="nda_terms" rows="5" class="form-control js-contract-richtext">{{ old('nda_terms', $documentModel?->nda_terms ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;letter-spacing:.03em">Pasal 5</span>
                    Berakhirnya Perintah Kerja &amp; Sanksi
                </label>
                <textarea name="sanctions_terms" rows="5" class="form-control js-contract-richtext">{{ old('sanctions_terms', $documentModel?->sanctions_terms ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem;letter-spacing:.03em">Pasal 6</span>
                    Penyelesaian Perselisihan
                </label>
                <textarea name="dispute_terms" rows="5" class="form-control js-contract-richtext">{{ old('dispute_terms', $documentModel?->dispute_terms ?? '') }}</textarea>
            </div>

            {{-- ══════════════════════════════════════════════════════════════════ --}}
            {{-- SECTION 1: METODE PEMBAYARAN --}}
            {{-- ══════════════════════════════════════════════════════════════════ --}}
            <div class="col-12"><div class="border-top pt-3 mt-1">
                <h6 class="mb-0"><i class="bi bi-credit-card me-2 text-primary"></i>Metode Pembayaran</h6>
                <div class="text-muted" style="font-size:.75rem">Cara pembayaran dan rekening tujuan untuk transfer gaji/upah.</div>
            </div></div>

            <div class="col-md-6">
                <label class="form-label fw-medium">Metode Pembayaran</label>
                <select name="payment_method" class="form-select">
                    @php $currentMethod = old('payment_method', $documentModel?->payment_method ?? 'Gaji Bulanan'); @endphp
                    <option value="Gaji Bulanan"   {{ $currentMethod === 'Gaji Bulanan' ? 'selected' : '' }}>Gaji Bulanan</option>
                    <option value="Lump Sum"       {{ $currentMethod === 'Lump Sum' ? 'selected' : '' }}>Lump Sum (Sekali Bayar)</option>
                    <option value="Per Proyek"     {{ $currentMethod === 'Per Proyek' ? 'selected' : '' }}>Per Proyek</option>
                    <option value="Harian"         {{ $currentMethod === 'Harian' ? 'selected' : '' }}>Harian</option>
                    <option value="Mingguan"       {{ $currentMethod === 'Mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="Termin"         {{ $currentMethod === 'Termin' ? 'selected' : '' }}>Termin / Bertahap</option>
                </select>
                <div class="form-text" style="font-size:.72rem">Pilih skema pembayaran sesuai kontrak.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-medium">Keterangan Termin / Detail Pembayaran <span class="text-muted fw-normal">(opsional)</span></label>
                <textarea name="payment_terms" rows="2" class="form-control"
                    placeholder="Contoh: Dibayar setiap tanggal 25 atau 3x termin: 40% awal, 40% tengah, 20% akhir">{{ old('payment_terms', $documentModel?->payment_terms ?? '') }}</textarea>
                <div class="form-text" style="font-size:.72rem">Jelaskan jika ada termin, jadwal, atau syarat pembayaran.</div>
            </div>

            <div class="col-12">
                <div class="alert alert-primary py-2 d-flex align-items-center gap-2 mb-0" style="font-size:.82rem">
                    <i class="bi bi-bank flex-shrink-0"></i>
                    <span><strong>Rekening Tujuan</strong> — Diisi sesuai rekening karyawan untuk transfer gaji.</span>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Bank Tujuan</label>
                <input type="text" name="bank_name" class="form-control"
                    value="{{ old('bank_name', $documentModel?->bank_name ?? '') }}"
                    placeholder="Contoh: BCA, Mandiri, BNI">
            </div>
            <div class="col-md-4">
                <label class="form-label">Nomor Rekening</label>
                <input type="text" name="bank_account" class="form-control"
                    value="{{ old('bank_account', $documentModel?->bank_account ?? '') }}"
                    placeholder="1234567890">
            </div>
            <div class="col-md-4">
                <label class="form-label">Atas Nama</label>
                <input type="text" name="bank_account_name" class="form-control"
                    value="{{ old('bank_account_name', $documentModel?->bank_account_name ?? '') }}"
                    placeholder="Nama sesuai rekening">
            </div>

            {{-- ══════════════════════════════════════════════════════════════════ --}}
            {{-- SECTION 2: NOMINAL & KOMPONEN PEMBAYARAN --}}
            {{-- ══════════════════════════════════════════════════════════════════ --}}
            <div class="col-12"><div class="border-top pt-3 mt-1">
                <h6 class="mb-0"><i class="bi bi-cash-stack me-2 text-success"></i>Nominal Pembayaran & Komponen</h6>
                <div class="text-muted" style="font-size:.75rem">Rincian nilai kontrak/gaji pokok dan komponen tunjangan/potongan yang akan otomatis tersinkron ke payroll.</div>
            </div></div>

            <div class="col-md-6">
                <label class="form-label fw-medium">
                    Nilai Kontrak / Gaji Pokok (Rp)
                    <span class="badge bg-success bg-opacity-10 text-success ms-1" style="font-size:.68rem">Payroll</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" min="0" step="1000" name="base_salary" class="form-control"
                        value="{{ old('base_salary', $documentModel?->base_salary ?? '') }}"
                        placeholder="5000000"
                        id="baseSalaryInput">
                </div>
                <div class="form-text" style="font-size:.72rem">
                    Nilai total kontrak (Lump Sum) atau gaji pokok bulanan. Akan tersinkron ke data karyawan.
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-medium">Nilai Kontrak (terbilang) <span class="text-muted fw-normal">(opsional)</span></label>
                <input type="text" name="contract_value_text" class="form-control"
                    value="{{ old('contract_value_text', $documentModel?->contract_value_text ?? '') }}"
                    placeholder="Lima juta rupiah">
                <div class="form-text" style="font-size:.72rem">Digunakan untuk dokumen kontrak formal.</div>
            </div>

            <div class="col-12">
                <label class="form-label fw-medium">
                    Tunjangan &amp; Potongan
                    <span class="badge bg-info bg-opacity-10 text-info ms-1" style="font-size:.68rem">Opsional</span>
                </label>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-2" id="contractSalaryTable" style="font-size:.85rem">
                        <thead class="table-light">
                            <tr>
                                <th style="width:45%">Label Komponen</th>
                                <th style="width:20%">Jenis</th>
                                <th style="width:25%">Jumlah (Rp)</th>
                                <th style="width:10%"></th>
                            </tr>
                        </thead>
                        <tbody id="contractSalaryBody">
                            @php $existingComponents = old('salary_components', $documentModel?->salary_components ?? []); @endphp
                            @forelse($existingComponents as $i => $row)
                                <tr>
                                    <td><input type="text" name="salary_components[{{ $i }}][label]" class="form-control form-control-sm" value="{{ $row['label'] ?? '' }}" placeholder="cth. Tunjangan Jabatan"></td>
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
                <div class="row g-2 mb-2">
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addContractSalaryBtn">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Komponen
                        </button>
                    </div>
                </div>
                <div class="alert alert-secondary py-2 mb-0" style="font-size:.78rem">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Info:</strong> Gaji Pokok + Tunjangan/Potongan di atas akan otomatis tersinkron ke data karyawan
                    dan digunakan saat <strong>Transfer Payroll</strong>.
                </div>
            </div>

            <div class="col-12"><div class="border-top pt-3 mt-1"><h6 class="mb-0">File &amp; Catatan</h6></div></div>
            <div class="col-md-6"><label class="form-label">Upload File Kontrak</label><input type="file" name="contract_file" class="form-control @error('contract_file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx"><div class="form-text" style="font-size:.72rem">Opsional PDF/JPG/PNG/DOC/DOCX — hingga 10 MB</div>@error('contract_file')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label">Catatan</label><textarea name="notes" rows="3" class="form-control">{{ old('notes', $documentModel?->notes ?? '') }}</textarea></div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                @if(!$documentModel)
                    <button
                        type="submit"
                        formaction="{{ route('contract-documents.save-template') }}"
                        formmethod="POST"
                        class="btn btn-outline-primary"
                    >
                        Simpan Jadi Template CMS
                    </button>
                @endif
                <a href="{{ route('contract-documents.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white">
                <span class="card-title mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Preview Dokumen</span>
            </div>
            <div class="card-body p-0">
                {{-- ── Header bergaya slip gaji ── --}}
                <div style="background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 60%,#2563eb 100%);padding:1.25rem 1.5rem;color:#fff;position:relative;overflow:hidden;border-radius:0">
                    <div style="position:absolute;right:-40px;top:-40px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.07)"></div>
                    <div style="position:absolute;right:50px;bottom:-50px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.05)"></div>
                    <div class="d-flex align-items-start justify-content-between gap-3 position-relative" style="z-index:1">
                        <div>
                            <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.14em;opacity:.65">Surat Perjanjian Kerja</div>
                            <div id="prev-company-name" style="font-size:1rem;font-weight:700;line-height:1.25;margin-top:.15rem">
                                {{ old('first_party_company', $documentModel?->first_party_company ?? '[nama perusahaan]') }}
                            </div>
                            <div id="prev-party-one-name" style="font-size:.72rem;opacity:.72;margin-top:.2rem">
                                {{ old('first_party_name', $documentModel?->first_party_name ?? '') }}
                                {{ old('first_party_position', $documentModel?->first_party_position ?? '') ? '· ' . old('first_party_position', $documentModel?->first_party_position ?? '') : '' }}
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div id="prev-contract-number" style="font-size:.85rem;font-weight:800;font-family:monospace;line-height:1.2">
                                {{ old('contract_number', $documentModel?->contract_number ?? '[nomor kontrak]') }}
                            </div>
                            <div id="prev-contract-date" style="font-size:.7rem;opacity:.72;margin-top:.15rem">
                                {{ $contractDate ? \Carbon\Carbon::parse($contractDate)->isoFormat('D MMMM YYYY') : '[tanggal]' }}
                                {{ old('location', $documentModel?->location ?? '') ? '· ' . old('location', $documentModel?->location ?? '') : '' }}
                            </div>
                            <span style="display:inline-flex;align-items:center;gap:.25rem;padding:.2em .6em;border-radius:50rem;font-size:.62rem;font-weight:700;letter-spacing:.05em;margin-top:.4rem;background:rgba(253,224,71,.2);color:#fde68a;border:1px solid rgba(253,224,71,.35)">
                                <i class="bi bi-hourglass-split"></i> Draft
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-3" style="font-size:.82rem;line-height:1.65">
                    {{-- Para pihak --}}
                    <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;display:flex;align-items:center;gap:.4rem;margin-bottom:.6rem;margin-top:.5rem">
                        Para Pihak <span style="flex:1;height:1px;background:#e2e8f0;display:inline-block"></span>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="border rounded p-2 h-100" style="font-size:.78rem">
                                <div class="fw-semibold mb-1" style="color:#1d4ed8;font-size:.72rem">PIHAK PERTAMA</div>
                                <div class="fw-semibold">{{ old('first_party_name', $documentModel?->first_party_name ?? '—') }}</div>
                                <div class="text-muted">{{ old('first_party_position', $documentModel?->first_party_position ?? '') }}</div>
                                <div>{{ old('first_party_company', $documentModel?->first_party_company ?? '—') }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 h-100" style="font-size:.78rem">
                                <div class="fw-semibold mb-1" style="color:#1d4ed8;font-size:.72rem">PIHAK KEDUA</div>
                                <div class="fw-semibold">{{ old('second_party_name', $documentModel?->second_party_name ?? '—') }}</div>
                                @php $ktp = old('second_party_ktp', $documentModel?->second_party_ktp ?? ''); @endphp
                                @if($ktp)<div class="text-muted">KTP: {{ $ktp }}</div>@endif
                            </div>
                        </div>
                    </div>

                    {{-- Info kontrak --}}
                    <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;display:flex;align-items:center;gap:.4rem;margin-bottom:.6rem">
                        Informasi Kontrak <span style="flex:1;height:1px;background:#e2e8f0;display:inline-block"></span>
                    </div>
                    <div class="row g-2 mb-3" style="font-size:.78rem">
                        <div class="col-12">
                            <span class="text-muted">Proyek:</span> <strong>{{ old('project_name', $documentModel?->project_name ?? '—') }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Durasi:</span>
                            <strong>{{ old('duration_text', $documentModel?->duration_text ?? '—') }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Periode:</span>
                            <strong>
                                {{ $startDate ? \Carbon\Carbon::parse($startDate)->isoFormat('D MMM YY') : '—' }}
                                s/d
                                {{ $endDate ? \Carbon\Carbon::parse($endDate)->isoFormat('D MMM YY') : 'Permanen' }}
                            </strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Nilai:</span>
                            @php $cv = old('contract_value', $documentModel?->contract_value ?? ''); @endphp
                            <strong class="text-success">{{ $cv ? 'Rp ' . number_format((float)$cv, 0, ',', '.') : '—' }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Metode:</span>
                            <strong>{{ old('payment_method', $documentModel?->payment_method ?? '—') }}</strong>
                        </div>
                        <div class="col-12">
                            <span class="text-muted">Rekening:</span>
                            {{ old('bank_name', $documentModel?->bank_name ?? '—') }} /
                            {{ old('bank_account', $documentModel?->bank_account ?? '—') }} /
                            {{ old('bank_account_name', $documentModel?->bank_account_name ?? '—') }}
                        </div>
                    </div>

                    {{-- Pasal 1–6 --}}
                    @foreach([
                        ['1','Ruang Lingkup Pekerjaan', old('scope_of_work', $documentModel?->scope_of_work ?? '')],
                        ['2','Hak &amp; Kewajiban', old('rights_obligations', $documentModel?->rights_obligations ?? '')],
                        ['3','HKI', old('hki_terms', $documentModel?->hki_terms ?? '')],
                        ['4','Kerahasiaan / NDA', old('nda_terms', $documentModel?->nda_terms ?? '')],
                        ['5','Sanksi', old('sanctions_terms', $documentModel?->sanctions_terms ?? '')],
                        ['6','Penyelesaian Perselisihan', old('dispute_terms', $documentModel?->dispute_terms ?? '')],
                    ] as [$no, $label, $val])
                    <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;display:flex;align-items:center;gap:.4rem;margin-bottom:.4rem">
                        <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.6rem">Pasal {{ $no }}</span>
                        {!! $label !!}
                        <span style="flex:1;height:1px;background:#e2e8f0;display:inline-block"></span>
                    </div>
                    <div class="mb-3 text-muted" style="font-size:.78rem;line-height:1.6">
                        {{ $val ?: '—' }}
                    </div>
                    @endforeach

                    {{-- Lampiran --}}
                    @php $pt = old('payment_terms', $documentModel?->payment_terms ?? ''); @endphp
                    @if($pt)
                    <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;display:flex;align-items:center;gap:.4rem;margin-bottom:.4rem">
                        <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:.6rem">Lampiran</span>
                        Termin Pembayaran
                        <span style="flex:1;height:1px;background:#e2e8f0;display:inline-block"></span>
                    </div>
                    <div class="mb-3 text-muted" style="font-size:.78rem;line-height:1.6">{{ $pt }}</div>
                    @endif

                    {{-- Catatan --}}
                    @php $notes = old('notes', $documentModel?->notes ?? ''); @endphp
                    @if($notes)
                    <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;display:flex;align-items:center;gap:.4rem;margin-bottom:.4rem">
                        Catatan <span style="flex:1;height:1px;background:#e2e8f0;display:inline-block"></span>
                    </div>
                    <div class="text-muted" style="font-size:.78rem;line-height:1.6">{{ $notes }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="contract-template-json">@json($contractTemplate)</script>
<script type="application/json" id="first-parties-json">@json($firstPartiesJson)</script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
    const btn = document.getElementById('autofill-contract-template');
    const generateNumberBtn = document.getElementById('generate-contract-number');
    const employeeSelect = document.getElementById('contract-employee-select');
    const firstPartySelect = document.getElementById('first-party-select');
    const contractNumberInput = document.querySelector('[name="contract_number"]');
    const startDateInput = document.querySelector('[name="start_date"]');
    const endDateInput = document.querySelector('[name="end_date"]');
    const durationInput = document.querySelector('[name="duration_text"]');
    const form = document.querySelector('form[action*="contract-documents"]');
    const supportingDataPanel = document.getElementById('employee-supporting-data');
    const supportingDataBadge = document.getElementById('supporting-data-badge');
    const supportingDataMessage = document.getElementById('supporting-data-message');
    const supportingDataDetails = document.getElementById('supporting-data-details');
    const supportingDocChecklist = document.getElementById('supporting-doc-checklist');
    const supportingMissingAlert = document.getElementById('supporting-missing-alert');

    function parseYmd(value) {
        if (!value) return null;
        const parts = value.split('-').map(Number);
        if (parts.length !== 3) return null;
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function toYmd(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function plural(value, unit) {
        return `${value} ${unit}`;
    }

    function durationFromDates(start, end) {
        if (!start || !end || end < start) return '';

        let years = end.getFullYear() - start.getFullYear();
        let months = end.getMonth() - start.getMonth();
        let days = end.getDate() - start.getDate();

        if (days < 0) {
            months -= 1;
            const prevMonth = new Date(end.getFullYear(), end.getMonth(), 0).getDate();
            days += prevMonth;
        }

        if (months < 0) {
            years -= 1;
            months += 12;
        }

        const parts = [];
        if (years > 0) parts.push(plural(years, 'tahun'));
        if (months > 0) parts.push(plural(months, 'bulan'));
        if (days > 0) parts.push(plural(days, 'hari'));

        return parts.length ? parts.join(' ') : '0 hari';
    }

    function applyDurationToEndDate() {
        if (!startDateInput || !endDateInput || !durationInput) return;
        const start = parseYmd(startDateInput.value);
        const raw = (durationInput.value || '').trim().toLowerCase();
        if (!start || !raw) return;

        const match = raw.match(/^(\d+)\s*(hari|bulan|tahun)$/i);
        if (!match) return;

        const value = parseInt(match[1], 10);
        const unit = match[2];
        const end = new Date(start);

        if (unit === 'hari') end.setDate(end.getDate() + value);
        if (unit === 'bulan') end.setMonth(end.getMonth() + value);
        if (unit === 'tahun') end.setFullYear(end.getFullYear() + value);

        endDateInput.value = toYmd(end);
    }

    function applyDateDiffToDuration() {
        if (!startDateInput || !endDateInput || !durationInput) return;
        const start = parseYmd(startDateInput.value);
        const end = parseYmd(endDateInput.value);
        const text = durationFromDates(start, end);
        if (text) durationInput.value = text;
    }

    const templateNode = document.getElementById('contract-template-json');
    const template = templateNode ? JSON.parse(templateNode.textContent || '{}') : {};
    const firstPartiesNode = document.getElementById('first-parties-json');
    const firstParties = firstPartiesNode ? JSON.parse(firstPartiesNode.textContent || '[]') : [];

    function toEditorHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>');
    }

    function setFieldValue(name, value) {
        const el = document.querySelector(`[name="${name}"]`);
        if (!el) return;

        const strValue = String(value ?? '');
        el.value = strValue;

        if (window.tinymce && el.classList.contains('js-contract-richtext')) {
            const editor = tinymce.get(el.id);
            if (editor) {
                const hasHtml = /<[^>]+>/.test(strValue);
                editor.setContent(hasHtml ? strValue : toEditorHtml(strValue));
            }
        }

        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function setFieldValueIfEmpty(name, value) {
        const el = document.querySelector(`[name="${name}"]`);
        if (!el) return;
        if ((el.value || '').trim() !== '') return;
        if (value === null || value === undefined || value === '') return;
        setFieldValue(name, value);
    }

    function renderSupportingData(payload) {
        if (!payload || !supportingDataPanel) return;

        const employee = payload.employee || {};
        const documents = Array.isArray(payload.documents) ? payload.documents : [];
        const missingDocLabels = Array.isArray(payload.missing_document_labels) ? payload.missing_document_labels : [];
        const missingDataFields = Array.isArray(payload.missing_data_fields) ? payload.missing_data_fields : [];
        const hasMissing = !!payload.has_missing_supporting_data;

        supportingDataDetails.classList.remove('d-none');
        supportingDataMessage.textContent = payload.info_message || '';
        supportingDataMessage.className = hasMissing ? 'text-warning' : 'text-success';
        supportingDataMessage.style.fontSize = '.82rem';

        supportingDataBadge.textContent = hasMissing ? 'Perlu Dilengkapi' : 'Siap Digunakan';
        supportingDataBadge.className = hasMissing
            ? 'badge bg-warning bg-opacity-10 text-warning'
            : 'badge bg-success bg-opacity-10 text-success';

        document.getElementById('supporting-employee-name').textContent = employee.name || '-';
        document.getElementById('supporting-employee-id').textContent = employee.employee_id || '-';
        document.getElementById('supporting-employee-company').textContent = employee.company_name || '-';
        document.getElementById('supporting-employee-position').textContent = employee.position || '-';
        document.getElementById('supporting-employee-npwp').textContent = employee.npwp || 'Belum tersedia';

        const bankText = (employee.bank_name && employee.bank_account)
            ? `${employee.bank_name} / ${employee.bank_account}`
            : 'Belum tersedia';
        document.getElementById('supporting-employee-bank').textContent = bankText;

        supportingDocChecklist.innerHTML = '';
        documents.forEach(function (doc) {
            const li = document.createElement('li');
            li.className = doc.available ? 'text-success' : 'text-danger';
            li.textContent = doc.available
                ? `${doc.label} tersedia${doc.document_label ? ' (' + doc.document_label + ')' : ''}`
                : `${doc.label} belum tersedia`;
            supportingDocChecklist.appendChild(li);
        });

        if (hasMissing) {
            const parts = [];
            if (missingDocLabels.length) {
                parts.push('Dokumen belum lengkap: ' + missingDocLabels.join(', '));
            }
            if (missingDataFields.length) {
                parts.push('Data karyawan belum lengkap: ' + missingDataFields.join(', '));
            }
            supportingMissingAlert.textContent = parts.join(' | ');
            supportingMissingAlert.classList.remove('d-none');
        } else {
            supportingMissingAlert.classList.add('d-none');
            supportingMissingAlert.textContent = '';
        }

        // Autofill draft fields from selected employee when still empty.
        setFieldValueIfEmpty('second_party_name', employee.name || '');
        setFieldValueIfEmpty('bank_name', employee.bank_name || '');
        setFieldValueIfEmpty('bank_account', employee.bank_account || '');
    }

    async function loadSupportingData(employeeId) {
        if (!supportingDataPanel || !supportingDataBadge || !supportingDataMessage) return;

        if (!employeeId) {
            supportingDataBadge.textContent = 'Belum dipilih';
            supportingDataBadge.className = 'badge bg-secondary bg-opacity-10 text-secondary';
            supportingDataMessage.textContent = 'Pilih karyawan terlebih dahulu untuk mengambil data pendukung kontrak.';
            supportingDataMessage.className = 'text-muted';
            supportingDataDetails.classList.add('d-none');
            supportingDocChecklist.innerHTML = '';
            supportingMissingAlert.classList.add('d-none');
            supportingMissingAlert.textContent = '';
            return;
        }

        const endpointTemplate = supportingDataPanel.dataset.endpointTemplate || '';
        const url = endpointTemplate.replace('__EMPLOYEE_ID__', String(employeeId));

        supportingDataBadge.textContent = 'Memuat...';
        supportingDataBadge.className = 'badge bg-info bg-opacity-10 text-info';
        supportingDataMessage.textContent = 'Mengambil data pendukung karyawan...';
        supportingDataMessage.className = 'text-muted';

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Gagal memuat data pendukung');
            }

            const payload = await response.json();
            renderSupportingData(payload);
        } catch (error) {
            supportingDataBadge.textContent = 'Gagal';
            supportingDataBadge.className = 'badge bg-danger bg-opacity-10 text-danger';
            supportingDataMessage.textContent = 'Gagal mengambil data pendukung karyawan. Silakan coba lagi.';
            supportingDataMessage.className = 'text-danger';
            supportingDataDetails.classList.add('d-none');
            supportingDocChecklist.innerHTML = '';
            supportingMissingAlert.classList.add('d-none');
            supportingMissingAlert.textContent = '';
        }
    }

    function initTinyMce() {
        if (!window.tinymce) return;

        document.querySelectorAll('textarea.js-contract-richtext').forEach((el, index) => {
            if (!el.id) {
                el.id = `contract-richtext-${index + 1}`;
            }
        });

        tinymce.init({
            selector: 'textarea.js-contract-richtext',
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

    initTinyMce();

    // Contract salary components: add row
    (function() {
        var btn  = document.getElementById('addContractSalaryBtn');
        var body = document.getElementById('contractSalaryBody');
        if (!btn || !body) return;
        btn.addEventListener('click', function() {
            var idx = body.querySelectorAll('tr').length;
            var tr  = document.createElement('tr');
            tr.innerHTML =
                '<td><input type="text" name="salary_components['+idx+'][label]" class="form-control form-control-sm" placeholder="cth. Tunjangan Jabatan"></td>'+
                '<td><select name="salary_components['+idx+'][type]" class="form-select form-select-sm">'+
                    '<option value="income">Pendapatan</option><option value="deduction">Potongan</option>'+
                '</select></td>'+
                '<td><input type="number" min="0" step="1000" name="salary_components['+idx+'][amount]" class="form-control form-control-sm" placeholder="0"></td>'+
                '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove()"><i class="bi bi-x-lg"></i></button></td>';
            body.appendChild(tr);
        });
    })();

    if (firstPartySelect) {
        firstPartySelect.addEventListener('change', function () {
            const selectedId = parseInt(firstPartySelect.value || '0', 10);
            if (!selectedId) return;

            const selected = firstParties.find(function (fp) {
                return parseInt(fp.id, 10) === selectedId;
            });
            if (!selected) return;

            setFieldValue('first_party_company', selected.name || '');
            setFieldValue('first_party_name', selected.representative_name || '');
            setFieldValue('first_party_position', selected.representative_position || '');
            setFieldValue('first_party_address', selected.address || '');
            // Also auto-fill penandatangan fields if still empty
            setFieldValueIfEmpty('penandatangan_p1_name', selected.representative_name || '');
            setFieldValueIfEmpty('penandatangan_p1_position', selected.representative_position || '');
        });

        if ((document.querySelector('[name="first_party_company"]')?.value || '').trim() === '' && firstParties.length > 0) {
            const defaultParty = firstParties.find(function (fp) {
                return (fp.name || '').toLowerCase() === 'pt lingkar inovasi muda';
            }) || firstParties[0];

            if (defaultParty) {
                firstPartySelect.value = String(defaultParty.id);
                firstPartySelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }

    if (employeeSelect) {
        employeeSelect.addEventListener('change', function () {
            loadSupportingData(employeeSelect.value);
        });

        if (employeeSelect.value) {
            loadSupportingData(employeeSelect.value);
        }
    }

    if (btn) {
        btn.addEventListener('click', function () {
            Object.entries(template).forEach(([name, value]) => {
                setFieldValue(name, value);
            });

            const today = new Date().toISOString().slice(0, 10);
            const contractDate = document.querySelector('[name="contract_date"]');
            const startDate = document.querySelector('[name="start_date"]');
            const endDate = document.querySelector('[name="end_date"]');

            if (contractDate && !contractDate.value) contractDate.value = today;
            if (startDate && !startDate.value) startDate.value = today;

            if (endDate && !endDate.value) {
                const d = new Date();
                d.setMonth(d.getMonth() + 6);
                endDate.value = d.toISOString().slice(0, 10);
            }

            const paymentMethod = document.querySelector('[name="payment_method"]');
            if (paymentMethod && !paymentMethod.value) {
                paymentMethod.value = 'Lump Sum';
            }

            if (contractNumberInput && !contractNumberInput.value.trim()) {
                const now = new Date();
                const m = String(now.getMonth() + 1).padStart(2, '0');
                const y = String(now.getFullYear());
                const seq = String(now.getSeconds() + 1).padStart(3, '0');
                contractNumberInput.value = `${seq}/SPK/LIM/${m}/${y}`;
            }
        });
    }

    if (generateNumberBtn && contractNumberInput) {
        generateNumberBtn.addEventListener('click', function () {
            const now = new Date();
            const m = String(now.getMonth() + 1).padStart(2, '0');
            const y = String(now.getFullYear());

            let next = 234;
            const current = (contractNumberInput.value || '').trim();
            const regex = new RegExp('^(\\d{3})/SPK/LIM/' + m + '/' + y + '$');
            const matched = current.match(regex);
            if (matched) {
                next = Math.max(234, parseInt(matched[1], 10) + 1);
            }

            const seq = String(next).padStart(3, '0');
            contractNumberInput.value = `${seq}/SPK/LIM/${m}/${y}`;
            contractNumberInput.dispatchEvent(new Event('input', { bubbles: true }));
            contractNumberInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    if (startDateInput && endDateInput && durationInput) {
        startDateInput.addEventListener('change', function () {
            if (durationInput.value.trim()) {
                applyDurationToEndDate();
            }
            applyDateDiffToDuration();
        });

        endDateInput.addEventListener('change', function () {
            applyDateDiffToDuration();
        });

        durationInput.addEventListener('change', function () {
            applyDurationToEndDate();
            applyDateDiffToDuration();
        });
    }

    if (form) {
        form.addEventListener('submit', function () {
            if (window.tinymce) {
                tinymce.triggerSave();
            }
        });
    }
})();
</script>