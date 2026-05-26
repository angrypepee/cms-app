@extends('layouts.app')
@section('title', 'Informasi Penggajian')
@section('page-title', 'Informasi Penggajian')

@section('content')
@php
    $payrollDay = 25;
@endphp

{{-- ── Top cards ── --}}
<div class="row g-3 mb-4">

    {{-- Next Payroll --}}
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100" style="border-left:4px solid #2563eb">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:44px;height:44px;border-radius:.75rem;background:#eff6ff;display:flex;align-items:center;justify-content:center;color:#2563eb;font-size:1.2rem;flex-shrink:0">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Transfer Berikutnya</div>
                        <div class="fw-bold" style="font-size:1.25rem;color:#2563eb">{{ $nextPayroll->isoFormat('D MMM YYYY') }}</div>
                    </div>
                </div>
                @if($daysUntil === 0)
                    <span class="badge bg-danger bg-opacity-10 text-danger fw-semibold" style="font-size:.78rem"><i class="bi bi-alarm me-1"></i>HARI INI</span>
                @elseif($daysUntil === 1)
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold" style="font-size:.78rem"><i class="bi bi-clock me-1"></i>Besok</span>
                @else
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold" style="font-size:.78rem"><i class="bi bi-hourglass-split me-1"></i>{{ $daysUntil }} hari lagi</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Transfer Day --}}
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Tanggal Transfer / Bulan</div>
                <div class="fw-bold" style="font-size:2.5rem;color:#1e293b;line-height:1">{{ $payrollDay }}</div>
                <div class="text-muted" style="font-size:.78rem">Setiap bulan, tanggal <strong>25</strong></div>
            </div>
        </div>
    </div>

    {{-- Active Employees --}}
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Karyawan Aktif</div>
                <div class="fw-bold" style="font-size:2.5rem;color:#1e293b;line-height:1">{{ $allActive }}</div>
                @if($expiredCount > 0)
                    <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:.72rem"><i class="bi bi-exclamation-circle me-1"></i>{{ $expiredCount }} kontrak habis</span>
                @endif
                @if($expiringCount > 0)
                    <span class="badge bg-warning bg-opacity-10 text-warning ms-1" style="font-size:.72rem"><i class="bi bi-clock-history me-1"></i>{{ $expiringCount }} segera berakhir</span>
                @endif
                @if($expiredCount === 0 && $expiringCount === 0)
                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.72rem"><i class="bi bi-check-circle me-1"></i>Semua kontrak aman</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Last Payroll --}}
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Transfer Terakhir</div>
                <div class="fw-bold" style="font-size:1.25rem;color:#64748b;line-height:1.3">{{ $prevPayroll->isoFormat('D MMM YYYY') }}</div>
                <div class="text-muted mt-1" style="font-size:.78rem">
                    @php
                        $publishedLastMonth = \App\Models\PayrollSlip::where('status','published')
                            ->whereYear('payment_date', $prevPayroll->year)
                            ->whereMonth('payment_date', $prevPayroll->month)
                            ->count();
                    @endphp
                    {{ $publishedLastMonth }} slip published bulan ini
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filters ── --}}
<div class="card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" action="{{ route('payroll-info.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#64748b">CARI KARYAWAN</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nama karyawan…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#64748b">PERUSAHAAN</label>
                <select name="company_id" class="form-select form-select-sm">
                    <option value="">Semua Perusahaan</option>
                    @foreach($companies as $id => $name)
                        <option value="{{ $id }}" {{ request('company_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#64748b">STATUS KONTRAK</label>
                <select name="contract_status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="active"   {{ request('contract_status') === 'active'   ? 'selected' : '' }}>Aktif / Permanen</option>
                    <option value="expiring" {{ request('contract_status') === 'expiring' ? 'selected' : '' }}>Segera Berakhir (30 hari)</option>
                    <option value="expired"  {{ request('contract_status') === 'expired'  ? 'selected' : '' }}>Kontrak Habis</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                @if(request()->hasAny(['search','company_id','contract_status']))
                    <a href="{{ route('payroll-info.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Employee Payroll Table ── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="card-title mb-0">
            <i class="bi bi-people-fill me-2 text-primary"></i>Daftar Karyawan &amp; Info Penggajian
            @php
                $monthsId = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
            @endphp
            <span class="text-muted fw-normal ms-2" style="font-size:.78rem">Periode {{ $monthsId[$periodMonth] }} {{ $periodYear }}</span>
        </span>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.72rem">
                <i class="bi bi-check2-circle me-1"></i>{{ $paidCount }} sudah ditransfer
            </span>
            <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:.72rem">
                <i class="bi bi-hourglass-split me-1"></i>{{ $unpaidCount }} belum
            </span>
            @if($unpaidCount > 0)
            <form method="POST" action="{{ route('payroll-info.transfer-all') }}" class="d-inline"
                  onsubmit="return confirm('Transfer payroll dan auto-generate slip untuk {{ $unpaidCount }} karyawan periode {{ $monthsId[$periodMonth] }} {{ $periodYear }}?');">
                @csrf
                <input type="hidden" name="period_month" value="{{ $periodMonth }}">
                <input type="hidden" name="period_year"  value="{{ $periodYear }}">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-cash-stack me-1"></i> Transfer Semua ({{ $unpaidCount }})
                </button>
            </form>
            @endif
            @if(auth()->user()?->isAdmin())
                <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateSignDateModal" title="Admin: Ubah tanggal tanda tangan slip">
                    <i class="bi bi-calendar-event me-1"></i> Ubah Tgl. TTD
                </button>
            @endif
            <a href="{{ route('payroll-info.report') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-receipt-cutoff me-1"></i> Laporan Pembayaran
            </a>
        </div>
    </div>

    @if(auth()->user()?->isAdmin())
    {{-- Admin-only: bulk update signature date on payroll slips --}}
    <div class="modal fade" id="updateSignDateModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('payroll-info.update-sign-date') }}"
              onsubmit="return confirm('Yakin ingin mengubah tanggal tanda tangan secara massal? Tindakan ini menulis ulang timestamp pada slip yang sudah ditandatangani.');">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-event me-2 text-warning"></i>Ubah Tanggal Tanda Tangan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 mb-3" style="font-size:.82rem">
                    <i class="bi bi-shield-exclamation me-1"></i>
                    Fitur khusus admin. Mengubah timestamp tanda tangan hanya untuk slip yang <strong>sudah</strong> ditandatangani — slip yang belum tertandatangani tidak akan tersentuh.
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tanggal &amp; Waktu Tanda Tangan Baru</label>
                    <input type="datetime-local" name="sign_date" class="form-control" required
                           value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold d-block">Terapkan ke</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="targets[]" value="admin" id="tgt_admin" checked>
                        <label class="form-check-label" for="tgt_admin">TTD Admin</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="targets[]" value="employee" id="tgt_emp">
                        <label class="form-check-label" for="tgt_emp">TTD Karyawan</label>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold d-block">Cakupan</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="scope" value="period" id="scope_period" checked>
                        <label class="form-check-label" for="scope_period">
                            Periode aktif saja ({{ $monthsId[$periodMonth] }} {{ $periodYear }})
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="scope" value="all" id="scope_all">
                        <label class="form-check-label text-danger" for="scope_all">
                            Semua periode (seluruh riwayat slip)
                        </label>
                    </div>
                </div>

                <input type="hidden" name="period_month" value="{{ $periodMonth }}">
                <input type="hidden" name="period_year"  value="{{ $periodYear }}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="bi bi-save me-1"></i> Terapkan Perubahan
                </button>
            </div>
        </form>
      </div>
    </div>
    @endif
    @if($employees->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
            Tidak ada karyawan ditemukan.
        </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>Karyawan</th>
                    <th>Perusahaan</th>
                    <th>Gaji Pokok</th>
                    <th>Bank / Rekening</th>
                    <th>Periode Kontrak</th>
                    <th>Status Kontrak</th>
                    <th>Status Transfer</th>
                    <th class="text-end" style="min-width:160px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $i => $emp)
                @php
                    [$cbLabel, $cbColor] = $emp->contractBadge();
                    $isPaid = in_array($emp->id, $paidIds);
                    $hasSalary = $emp->base_salary && $emp->base_salary > 0;
                @endphp
                <tr>
                    <td class="text-muted" style="font-size:.78rem">{{ $employees->firstItem() + $i }}</td>
                    <td>
                        <a href="{{ route('employees.show', $emp) }}" class="text-decoration-none">
                            <div class="fw-semibold" style="font-size:.875rem;color:#1e293b">{{ $emp->name }}</div>
                            <div class="text-muted" style="font-size:.75rem">{{ $emp->employee_id }}{{ $emp->position ? ' · '.$emp->position : '' }}</div>
                        </a>
                    </td>
                    <td class="text-muted" style="font-size:.82rem">{{ $emp->company->name ?? '-' }}</td>
                    <td>
                        @if($hasSalary)
                            <span class="fw-semibold text-success" style="font-size:.875rem">Rp {{ number_format($emp->base_salary, 0, ',', '.') }}</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:.7rem"><i class="bi bi-exclamation-circle me-1"></i>Belum diatur</span>
                        @endif
                    </td>
                    <td style="font-size:.82rem">
                        @if($emp->bank_name)
                            <div class="fw-medium">{{ $emp->bank_name }}</div>
                            <div class="text-muted font-monospace" style="font-size:.75rem">{{ $emp->bank_account ?? '-' }}</div>
                        @else
                            <span class="text-muted">–</span>
                        @endif
                    </td>
                    <td style="font-size:.82rem">
                        @if($emp->contract_start)
                            <div>{{ $emp->contract_start->isoFormat('D MMM YY') }}</div>
                            @if($emp->contract_end)
                                <div class="text-muted">s/d {{ $emp->contract_end->isoFormat('D MMM YY') }}</div>
                            @else
                                <div class="text-muted">s/d <em>Permanen</em></div>
                            @endif
                        @else
                            <span class="text-muted">–</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $cbColor }} bg-opacity-10 text-{{ $cbColor }} badge-pill" style="font-size:.72rem">{{ $cbLabel }}</span>
                        @if($emp->contract_end && $emp->contractStatus() === 'expiring')
                            <div class="text-warning" style="font-size:.7rem;margin-top:.15rem">
                                <i class="bi bi-exclamation-triangle-fill"></i> {{ $emp->contract_end->diffForHumans() }}
                            </div>
                        @elseif($emp->contractStatus() === 'expired')
                            <div class="text-danger" style="font-size:.7rem;margin-top:.15rem">
                                <i class="bi bi-x-circle-fill"></i> Habis {{ $emp->contract_end?->isoFormat('D MMM YYYY') }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($isPaid)
                            @php
                                $paidSlip = \App\Models\PayrollSlip::where('employee_id', $emp->id)
                                    ->where('period_month', $periodMonth)->where('period_year', $periodYear)->first();
                            @endphp
                            <span class="badge bg-success" style="font-size:.72rem"><i class="bi bi-check2-circle me-1"></i>Sudah Ditransfer</span>
                            <div class="text-muted" style="font-size:.7rem;margin-top:.15rem">{{ $monthsId[$periodMonth] }} {{ $periodYear }}</div>
                            @if($paidSlip?->transfer_reference)
                                <div class="text-muted font-monospace" style="font-size:.68rem;margin-top:.15rem" title="Nomor Transaksi">
                                    <i class="bi bi-hash"></i>{{ $paidSlip->transfer_reference }}
                                </div>
                            @endif
                            @if($paidSlip?->transfer_bank)
                                <div class="text-muted" style="font-size:.68rem" title="Bank Pengirim">
                                    <i class="bi bi-bank2"></i> {{ $paidSlip->transfer_bank }}
                                </div>
                            @endif
                            @if($paidSlip?->transfer_proof_path)
                                <a href="{{ asset('storage/' . $paidSlip->transfer_proof_path) }}" target="_blank"
                                   class="text-decoration-none" style="font-size:.68rem" title="Lihat bukti transfer">
                                    <i class="bi bi-paperclip"></i> Bukti
                                </a>
                            @endif
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:.72rem"><i class="bi bi-hourglass-split me-1"></i>Belum Transfer</span>
                            <div class="text-muted" style="font-size:.7rem;margin-top:.15rem">{{ $nextPayroll->isoFormat('D MMM YYYY') }}</div>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($isPaid)
                            @php
                                $slip = \App\Models\PayrollSlip::where('employee_id', $emp->id)
                                    ->where('period_month', $periodMonth)->where('period_year', $periodYear)->first();
                            @endphp
                            @if($slip)
                                <a href="{{ route('payroll-slips.show', $slip) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-receipt"></i> Lihat Slip
                                </a>
                            @endif
                        @elseif(!$hasSalary)
                            <a href="{{ route('employees.edit', $emp) }}" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-pencil"></i> Set Gaji
                            </a>
                        @else
                            <button type="button" class="btn btn-success btn-sm"
                                data-bs-toggle="modal" data-bs-target="#transferModal"
                                data-action="{{ route('payroll-info.transfer', $emp) }}"
                                data-employee-name="{{ $emp->name }}"
                                data-employee-bank="{{ $emp->bank_name }}"
                                data-employee-account="{{ $emp->bank_account }}"
                                data-amount="Rp {{ number_format($emp->base_salary, 0, ',', '.') }}"
                                data-period="{{ $monthsId[$periodMonth] }} {{ $periodYear }}">
                                <i class="bi bi-send-check"></i> Transfer
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
        <div class="px-4 py-3 border-top">{{ $employees->links() }}</div>
    @endif
    @endif
</div>

{{-- ── Transfer Proof Modal (shared, populated via JS) ── --}}
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="transferForm" method="POST" action="#" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="period_month" value="{{ $periodMonth }}">
        <input type="hidden" name="period_year"  value="{{ $periodYear }}">

        <div class="modal-header">
            <h5 class="modal-title">
                <i class="bi bi-send-check me-2 text-success"></i>Transfer &amp; Bukti Pembayaran
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div class="alert alert-light border py-2 mb-3" style="font-size:.82rem">
                <div class="mb-1"><strong>Karyawan:</strong> <span id="tm-employee-name"></span></div>
                <div class="mb-1"><strong>Periode:</strong> <span id="tm-period"></span></div>
                <div class="mb-1"><strong>Jumlah:</strong> <span class="text-success fw-semibold" id="tm-amount"></span></div>
                <div class="text-muted" style="font-size:.78rem">
                    <i class="bi bi-bank2"></i> Rek. tujuan: <span id="tm-employee-bank">-</span>
                    <span id="tm-employee-account-wrap" class="font-monospace ms-1"></span>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">
                    Nomor Transaksi / Referensi <span class="text-danger">*</span>
                </label>
                <input type="text" name="transfer_reference" class="form-control" required maxlength="100"
                       placeholder="cth. TRX-20260525-001 / Ref bank">
                <div class="form-text" style="font-size:.72rem">Nomor referensi dari bukti transfer bank Anda.</div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">
                    Bank Pengirim <span class="text-danger">*</span>
                </label>
                <input type="text" name="transfer_bank" class="form-control" required maxlength="100"
                       placeholder="cth. BCA / Mandiri / BNI">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Bukti Transfer (Dokumen)</label>
                <input type="file" name="transfer_proof" class="form-control"
                       accept=".jpg,.jpeg,.png,.pdf">
                <div class="form-text" style="font-size:.72rem">JPG, PNG, atau PDF — maks. 5 MB.</div>
            </div>

            <div class="mb-2">
                <label class="form-label small fw-semibold">Catatan</label>
                <textarea name="transfer_notes" class="form-control" rows="2" maxlength="1000"
                          placeholder="Catatan tambahan (opsional)"></textarea>
            </div>

            <div class="text-muted" style="font-size:.7rem">
                <i class="bi bi-shield-check me-1"></i>
                Data akan tersimpan sebagai bukti audit. Slip gaji otomatis dibuat &amp; ditandatangani admin.
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success btn-sm">
                <i class="bi bi-send-check me-1"></i> Konfirmasi Transfer
            </button>
        </div>
    </form>
  </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('transferModal');
        if (!modal) return;
        modal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            if (!btn) return;
            var form = document.getElementById('transferForm');
            form.setAttribute('action', btn.getAttribute('data-action') || '#');
            form.reset();

            document.getElementById('tm-employee-name').textContent = btn.getAttribute('data-employee-name') || '-';
            document.getElementById('tm-period').textContent        = btn.getAttribute('data-period') || '-';
            document.getElementById('tm-amount').textContent        = btn.getAttribute('data-amount') || '-';
            document.getElementById('tm-employee-bank').textContent = btn.getAttribute('data-employee-bank') || '-';
            var acct = btn.getAttribute('data-employee-account');
            document.getElementById('tm-employee-account-wrap').textContent = acct ? ('· ' + acct) : '';
        });
    })();
</script>
@endsection
