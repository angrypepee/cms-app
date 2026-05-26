@extends('layouts.app')
@section('title', 'Dana Apresiasi Saya')
@section('page-title', 'Dana Apresiasi Saya')

@section('content')
@if($budgets->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-stars fs-1 d-block mb-2 opacity-25"></i>
            <p class="mb-0">Belum ada anggaran apresiasi yang ditetapkan untuk Anda.</p>
            <p class="mb-0" style="font-size:.85rem">Hubungi HR/Admin untuk informasi lebih lanjut.</p>
        </div>
    </div>
@else
    @foreach($budgets as $budget)
    @php
        $used      = $budget->usedAmount();
        $remaining = $budget->remainingAmount();
        $pct       = $budget->usagePercentage();
        $pendingClaims = $budget->claims->where('status', 'pending');
    @endphp
    <div class="card mb-4">
        {{-- Budget header --}}
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <span class="card-title mb-0"><i class="bi bi-stars me-2 text-warning"></i>Dana Apresiasi {{ $budget->year }}</span>
                @if($budget->notes)
                    <div class="text-muted mt-1" style="font-size:.8rem">{{ $budget->notes }}</div>
                @endif
            </div>
            <button class="btn btn-primary btn-sm claim-trigger"
                data-budget-id="{{ $budget->id }}"
                data-budget-year="{{ $budget->year }}"
                data-remaining="{{ $remaining }}"
                data-action="{{ route('my.appreciation.claims.store', $budget) }}"
                {{ $remaining <= 0 ? 'disabled' : '' }}>
                <i class="bi bi-plus-lg me-1"></i>Ajukan Permohonan
            </button>
        </div>

        {{-- Budget quota summary --}}
        <div class="card-body border-bottom">
            <div class="row g-3 align-items-center">
                <div class="col-sm-4 text-center">
                    <div class="text-muted mb-1" style="font-size:.78rem">Total Anggaran</div>
                    <div class="fw-bold text-primary">Rp {{ number_format($budget->total_amount, 0, ',', '.') }}</div>
                </div>
                <div class="col-sm-4 text-center">
                    <div class="text-muted mb-1" style="font-size:.78rem">Sudah Digunakan</div>
                    <div class="fw-bold text-secondary">Rp {{ number_format($used, 0, ',', '.') }}</div>
                </div>
                <div class="col-sm-4 text-center">
                    <div class="text-muted mb-1" style="font-size:.78rem">Sisa Anggaran</div>
                    <div class="fw-bold {{ $remaining > 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($remaining, 0, ',', '.') }}
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.75rem">
                        <span class="text-muted">Pemakaian</span>
                        <span class="fw-medium">{{ $pct }}%</span>
                    </div>
                    <div class="progress" style="height:8px">
                        <div class="progress-bar {{ $pct >= 90 ? 'bg-danger' : ($pct >= 60 ? 'bg-warning' : 'bg-success') }}"
                             style="width:{{ $pct }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Claims list --}}
        @if($budget->claims->isEmpty())
            <div class="card-body text-center py-4 text-muted" style="font-size:.88rem">
                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>Belum ada permohonan.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Nominal</th>
                            <th>Tgl. Pengajuan</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($budget->claims as $claim)
                        <tr>
                            <td>
                                <div class="fw-medium" style="font-size:.88rem">{{ $claim->title }}</div>
                                @if($claim->description)
                                    <div class="text-muted text-truncate" style="font-size:.76rem;max-width:280px">{{ $claim->description }}</div>
                                @endif
                            </td>
                            <td class="fw-semibold text-success">Rp {{ number_format($claim->amount, 0, ',', '.') }}</td>
                            <td class="text-muted" style="font-size:.82rem">{{ $claim->created_at->format('d M Y') }}</td>
                            <td>
                                <span class="badge badge-pill {{ $claim->statusBadgeClass() }}">{{ $claim->statusLabel() }}</span>
                                @if($claim->isRejected() && $claim->rejection_reason)
                                    <i class="bi bi-info-circle text-danger ms-1" style="cursor:pointer"
                                       data-bs-toggle="tooltip" title="{{ $claim->rejection_reason }}"></i>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('my.appreciation.claims.show', [$budget, $claim]) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($claim->isPending())
                                        <form method="POST" action="{{ route('my.appreciation.claims.destroy', [$budget, $claim]) }}"
                                              onsubmit="return confirm('Batalkan permohonan ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @endforeach
@endif

{{-- ── Single shared claim modal ── --}}
<div class="modal fade" id="claimModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-plus me-2 text-primary"></i>
                    Ajukan Permohonan — <span id="modalBudgetYear"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="claimForm" method="POST" action="" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="_budget_id" id="modalBudgetId" value="{{ old('_budget_id') }}">
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3" style="font-size:.84rem">
                        <i class="bi bi-info-circle me-1"></i>
                        Sisa anggaran: <strong id="modalRemainingText">—</strong>
                    </div>
                    <div class="alert alert-danger py-2 mb-2 d-none" id="amountOverAlert" role="alert" style="font-size:.84rem">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Nominal melebihi sisa anggaran. Maksimal: <strong id="amountOverMax"></strong>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">Judul Permohonan <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="modalTitle"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title') }}"
                                placeholder="cth. Pembelian peralatan, Biaya pelatihan..."
                                autocomplete="off" required>
                            @error('title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Deskripsi</label>
                            <textarea name="description" id="modalDescription" rows="3"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Penjelasan keperluan...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nominal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount" id="modalAmount"
                                    class="form-control @error('amount') is-invalid @enderror"
                                    value="{{ old('amount') }}"
                                    min="1" step="1000"
                                    placeholder="Masukkan nominal"
                                    autocomplete="off" required>
                                @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text mt-1" style="font-size:.76rem">
                                Maks: <span id="modalMaxAmountText">—</span>
                            </div>
                        </div>
                        <div class="col-12 border-top pt-3">
                            <label class="form-label fw-medium">Dokumen Pendukung</label>
                            <div id="docList">
                                <div class="row g-2 mb-2 doc-row">
                                    <div class="col-md-5">
                                        <input type="text" name="doc_labels[]" class="form-control form-control-sm" placeholder="Nama dokumen">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="file" name="documents[]" class="form-control form-control-sm"
                                            accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-doc"><i class="bi bi-x"></i></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addDocBtn" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-plus me-1"></i>Tambah Dokumen
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="claimSubmitBtn" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Ajukan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Tooltips ────────────────────────────────────────────────
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
        new bootstrap.Tooltip(el);
    });

    // ── Shared claim modal ──────────────────────────────────────
    const claimModalEl  = document.getElementById('claimModal');
    const claimForm     = document.getElementById('claimForm');
    const modalAmount   = document.getElementById('modalAmount');
    const overAlert     = document.getElementById('amountOverAlert');
    const overMaxSpan   = document.getElementById('amountOverMax');
    let currentRemaining = 0;

    function fmtRp(val) {
        return 'Rp ' + parseInt(val).toLocaleString('id-ID');
    }

    function openModal(btn, resetFields) {
        const remaining = parseInt(btn.dataset.remaining) || 0;
        currentRemaining = remaining;

        document.getElementById('modalBudgetYear').textContent  = btn.dataset.budgetYear;
        document.getElementById('modalBudgetId').value          = btn.dataset.budgetId;
        document.getElementById('modalRemainingText').textContent = fmtRp(remaining);
        document.getElementById('modalMaxAmountText').textContent = fmtRp(remaining);
        overMaxSpan.textContent = fmtRp(remaining);
        claimForm.action = btn.dataset.action;

        if (resetFields) {
            document.getElementById('modalTitle').value       = '';
            document.getElementById('modalDescription').value = '';
            modalAmount.value = '';
            modalAmount.classList.remove('is-invalid');
            overAlert.classList.add('d-none');
            // Reset doc list
            const docList = document.getElementById('docList');
            docList.querySelectorAll('.doc-row:not(:first-child)').forEach(r => r.remove());
            docList.querySelectorAll('input').forEach(i => { i.value = ''; });
        }

        bootstrap.Modal.getOrCreateInstance(claimModalEl).show();
    }

    // Trigger buttons
    document.querySelectorAll('.claim-trigger').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openModal(btn, true);
        });
    });

    // Real-time amount over-budget check
    modalAmount.addEventListener('input', function() {
        const val = parseFloat(this.value) || 0;
        if (val > currentRemaining && currentRemaining > 0) {
            overAlert.classList.remove('d-none');
            this.classList.add('is-invalid');
            document.getElementById('claimSubmitBtn').disabled = true;
        } else {
            overAlert.classList.add('d-none');
            this.classList.remove('is-invalid');
            document.getElementById('claimSubmitBtn').disabled = false;
        }
    });

    // ── Doc row management ──────────────────────────────────────
    document.getElementById('addDocBtn').addEventListener('click', function() {
        const docList = document.getElementById('docList');
        const row = docList.querySelector('.doc-row').cloneNode(true);
        row.querySelectorAll('input').forEach(function(i) { i.value = ''; });
        docList.appendChild(row);
    });
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-doc')) {
            const docList = e.target.closest('.doc-row').parentElement;
            if (docList.querySelectorAll('.doc-row').length > 1) {
                e.target.closest('.doc-row').remove();
            }
        }
    });

    // ── Auto-reopen modal after validation error ─────────────────
    @if($errors->any() && old('_budget_id'))
    (function() {
        var errorBudgetId = '{{ old('_budget_id') }}';
        var btn = document.querySelector('.claim-trigger[data-budget-id="' + errorBudgetId + '"]');
        if (btn) {
            // Fields already have old() values from Blade — don't reset
            openModal(btn, false);
        }
    })();
    @endif
});
</script>
@endpush
@endsection
