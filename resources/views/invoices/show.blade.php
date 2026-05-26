@extends('layouts.app')
@section('title', $invoice->invoice_number)

@section('content')
@php [$lbl,$clr] = $invoice->statusBadge(); $balance = $invoice->balance; @endphp

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 d-print-none">
    <div>
        <a href="{{ route('invoices.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Daftar Invoice</a>
        <h4 class="mb-0 mt-1" style="font-size:1.2rem;font-weight:700">
            <span class="font-monospace">{{ $invoice->invoice_number }}</span>
            <span class="badge bg-{{ $clr }} bg-opacity-10 text-{{ $clr }} ms-2" style="font-size:.72rem">{{ $lbl }}</span>
        </h4>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('invoices.pdf',$invoice) }}" target="_blank" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <button type="button" class="btn btn-sm btn-outline-info" onclick="copyShare()" title="Salin link untuk klien"><i class="bi bi-link-45deg"></i> Salin Link Share</button>
        @if(!$invoice->sent_at && $invoice->status === 'draft')
            <form method="POST" action="{{ route('invoices.send',$invoice) }}" class="d-inline">@csrf
                <button class="btn btn-sm btn-primary"><i class="bi bi-send"></i> Tandai Terkirim</button>
            </form>
        @endif
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i> Print</button>
        @if($invoice->status !== 'paid')
            <a href="{{ route('invoices.edit',$invoice) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-arrow-repeat"></i> Status
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach(['draft'=>'Draft','sent'=>'Terkirim','cancelled'=>'Dibatalkan'] as $k=>$v)
                    <li>
                        <form method="POST" action="{{ route('invoices.status',$invoice) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $k }}">
                            <button class="dropdown-item small" type="submit">{{ $v }}</button>
                        </form>
                    </li>
                    @endforeach
                </ul>
            </div>
            @if(!in_array($invoice->status, ['cancelled']) && $balance > 0)
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="bi bi-cash"></i> Catat Pembayaran</button>
            @endif
            @if(!in_array($invoice->status, ['paid','partial']))
            <form method="POST" action="{{ route('invoices.destroy',$invoice) }}" onsubmit="return confirm('Hapus invoice {{ $invoice->invoice_number }}?');">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
            @endif
        @endif
    </div>
</div>

@if(session('success'))<div class="alert alert-success py-2 small">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2 small">{{ session('error') }}</div>@endif

<div class="d-flex flex-wrap gap-2 mb-3 small d-print-none">
    @if($invoice->sent_at)
        <span class="badge bg-primary-subtle text-primary border"><i class="bi bi-send-check"></i> Terkirim: {{ $invoice->sent_at->isoFormat('D MMM YYYY HH:mm') }}</span>
    @endif
    @if($invoice->viewed_at)
        <span class="badge bg-success-subtle text-success border"><i class="bi bi-eye"></i> Dilihat klien: {{ $invoice->viewed_at->isoFormat('D MMM YYYY HH:mm') }}</span>
    @else
        <span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-eye-slash"></i> Belum dilihat klien</span>
    @endif
    @if($invoice->bankAccount)
        <span class="badge bg-light text-dark border"><i class="bi bi-bank"></i> {{ $invoice->bankAccount->bank_name }} {{ $invoice->bankAccount->account_number }}</span>
    @endif
</div>

@if($invoice->quotation)
<div class="alert alert-light border py-2 small d-print-none">
    <i class="bi bi-info-circle me-1 text-muted"></i> Dibuat dari quotation
    <a href="{{ route('quotations.show',$invoice->quotation) }}" class="font-monospace fw-semibold">{{ $invoice->quotation->quotation_number }}</a>
</div>
@endif

<div class="row g-3 mb-3 d-print-none">
    <div class="col-md-4">
        <div class="card"><div class="card-body py-2">
            <div class="text-muted text-uppercase" style="font-size:.7rem">Total Invoice</div>
            <div class="font-monospace fw-bold" style="font-size:1.1rem">Rp {{ number_format($invoice->total,0,',','.') }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-success"><div class="card-body py-2">
            <div class="text-success text-uppercase" style="font-size:.7rem">Sudah Dibayar</div>
            <div class="font-monospace fw-bold text-success" style="font-size:1.1rem">Rp {{ number_format($invoice->paid_amount,0,',','.') }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-warning"><div class="card-body py-2">
            <div class="text-warning text-uppercase" style="font-size:.7rem">Sisa</div>
            <div class="font-monospace fw-bold text-warning" style="font-size:1.1rem">Rp {{ number_format($balance,0,',','.') }}</div>
        </div></div>
    </div>
</div>

<div class="card"><div class="card-body p-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-7">
            @if($invoice->company)
                @if($invoice->company->logo)<img src="{{ asset('storage/'.$invoice->company->logo) }}" style="max-height:60px" class="mb-2"><br>@endif
                <strong style="font-size:1.05rem">{{ $invoice->company->name }}</strong><br>
                <span class="small text-muted">{{ $invoice->company->address ?? '' }}</span>
            @endif
        </div>
        <div class="col-5 text-end">
            <div class="text-uppercase text-muted" style="letter-spacing:.1em;font-size:.85rem">Invoice / Tagihan</div>
            <div class="font-monospace fw-bold" style="font-size:1.3rem">{{ $invoice->invoice_number }}</div>
            <div class="small">Tanggal: <strong>{{ $invoice->issue_date->isoFormat('D MMMM YYYY') }}</strong></div>
            @if($invoice->due_date)<div class="small">Jatuh tempo: <strong class="{{ $invoice->status==='overdue' ? 'text-danger' : '' }}">{{ $invoice->due_date->isoFormat('D MMMM YYYY') }}</strong></div>@endif
        </div>
    </div>

    {{-- Client / Project --}}
    <div class="row mb-4">
        <div class="col-7">
            <div class="text-uppercase text-muted small mb-1">Ditagihkan Kepada</div>
            <strong>{{ $invoice->client->name }}</strong><br>
            @if($invoice->client->contact_person)<span class="small">UP: {{ $invoice->client->contact_person }}</span><br>@endif
            @if($invoice->client->address)<span class="small text-muted">{{ $invoice->client->address }}</span><br>@endif
            @if($invoice->client->npwp)<span class="small text-muted font-monospace">NPWP: {{ $invoice->client->npwp }}</span>@endif
        </div>
        <div class="col-5">
            @if($invoice->project)
                <div class="text-uppercase text-muted small mb-1">Project</div>
                <strong class="font-monospace">{{ $invoice->project->code }}</strong> — {{ $invoice->project->name }}
            @endif
            @if($invoice->subject)
                <div class="text-uppercase text-muted small mb-1 mt-2">Perihal</div>
                <strong>{{ $invoice->subject }}</strong>
            @endif
        </div>
    </div>

    {{-- Items --}}
    <div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="table-light" style="font-size:.82rem">
            <tr><th style="width:36px">#</th><th>Deskripsi</th><th class="text-end" style="width:80px">Qty</th><th style="width:70px">Unit</th><th class="text-end" style="width:130px">Harga</th><th class="text-end" style="width:140px">Jumlah</th></tr>
        </thead>
        <tbody style="font-size:.85rem">
            @foreach($invoice->items as $it)
            <tr>
                <td class="text-muted small">{{ $loop->iteration }}</td>
                <td>{{ $it->description }}</td>
                <td class="text-end font-monospace">{{ rtrim(rtrim(number_format($it->quantity,2,',','.'),'0'),',') }}</td>
                <td class="small">{{ $it->unit }}</td>
                <td class="text-end font-monospace">Rp {{ number_format($it->unit_price,0,',','.') }}</td>
                <td class="text-end font-monospace">Rp {{ number_format($it->amount,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot style="font-size:.9rem">
            <tr><td colspan="5" class="text-end text-muted">Subtotal</td><td class="text-end font-monospace">Rp {{ number_format($invoice->subtotal,0,',','.') }}</td></tr>
            @if($invoice->discount > 0)
            <tr><td colspan="5" class="text-end text-muted">Diskon</td><td class="text-end font-monospace">- Rp {{ number_format($invoice->discount,0,',','.') }}</td></tr>
            @endif
            @if($invoice->tax_percent > 0)
            <tr><td colspan="5" class="text-end text-muted">PPN {{ rtrim(rtrim(number_format($invoice->tax_percent,2),'0'),'.') }}%</td><td class="text-end font-monospace">Rp {{ number_format($invoice->tax_amount,0,',','.') }}</td></tr>
            @endif
            <tr><td colspan="5" class="text-end fw-bold">TOTAL</td><td class="text-end font-monospace fw-bold" style="font-size:1.1rem">Rp {{ number_format($invoice->total,0,',','.') }}</td></tr>
            @if($invoice->paid_amount > 0)
            <tr><td colspan="5" class="text-end text-success">Dibayar</td><td class="text-end font-monospace text-success">Rp {{ number_format($invoice->paid_amount,0,',','.') }}</td></tr>
            <tr><td colspan="5" class="text-end fw-bold text-warning">Sisa Tagihan</td><td class="text-end font-monospace fw-bold text-warning" style="font-size:1.1rem">Rp {{ number_format($balance,0,',','.') }}</td></tr>
            @endif
        </tfoot>
    </table>
    </div>

    @if($invoice->notes)
    <div class="mt-3"><strong class="small text-muted">Catatan:</strong><div class="small" style="white-space:pre-line">{{ $invoice->notes }}</div></div>
    @endif
    @if($invoice->terms)
    <div class="mt-3"><strong class="small text-muted">Syarat Pembayaran:</strong><div class="small text-muted" style="white-space:pre-line">{{ $invoice->terms }}</div></div>
    @endif

</div></div>

{{-- ── Payment history ── --}}
<div class="card mt-3 d-print-none">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-cash-stack text-success"></i> Riwayat Pembayaran</strong>
        @if(!in_array($invoice->status,['cancelled']) && $balance > 0)
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="bi bi-plus-circle"></i> Catat Pembayaran</button>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Tanggal</th><th>Metode</th><th>Referensi</th><th>Bank</th><th>Catatan</th><th>Dicatat oleh</th><th class="text-end">Jumlah</th><th></th></tr></thead>
            <tbody>
                @forelse($invoice->payments as $p)
                <tr>
                    <td>{{ $p->payment_date?->isoFormat('D MMM YYYY') }}</td>
                    <td>{{ $p->method ?: '—' }}</td>
                    <td><code>{{ $p->reference ?: '—' }}</code></td>
                    <td>{{ $p->bankAccount?->bank_name ?? '—' }}</td>
                    <td class="small text-muted">{{ $p->notes ?: '—' }}</td>
                    <td class="small">{{ $p->recorder?->name ?? '—' }}</td>
                    <td class="text-end font-monospace fw-bold text-success">Rp {{ number_format($p->amount,0,',','.') }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('invoices.payment.delete',[$invoice,$p]) }}" onsubmit="return confirm('Hapus pembayaran ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-3">Belum ada pembayaran tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade d-print-none" id="paymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="POST" action="{{ route('invoices.payment',$invoice) }}">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-cash me-1"></i> Catat Pembayaran</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info py-2 small mb-3">
                Sisa tagihan: <strong class="font-monospace">Rp {{ number_format($balance,0,',','.') }}</strong>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Jumlah Bayar (Rp) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" max="{{ $balance }}" name="amount" class="form-control" required value="{{ $balance }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tanggal Bayar <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="form-control" required value="{{ now()->toDateString() }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Metode</label>
                    <select name="method" class="form-select">
                        <option value="">— Pilih —</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="Tunai">Tunai</option>
                        <option value="Cek/Giro">Cek / Giro</option>
                        <option value="E-Wallet">E-Wallet</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">No. Referensi</label>
                    <input type="text" name="reference" class="form-control" maxlength="100" placeholder="No. transaksi / bukti">
                </div>
                @if(isset($banks) && $banks->count())
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Diterima ke Rekening</label>
                    <select name="bank_account_id" class="form-select">
                        <option value="">— Pilih —</option>
                        @foreach($banks as $b)
                            <option value="{{ $b->id }}" {{ $invoice->bank_account_id == $b->id ? 'selected' : '' }}>{{ $b->bank_name }} — {{ $b->account_number }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Catatan</label>
                    <textarea name="notes" rows="2" maxlength="500" class="form-control"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
            <button class="btn btn-success btn-sm"><i class="bi bi-check2-circle me-1"></i> Catat Pembayaran</button>
        </div>
    </form>
  </div>
</div>

<script>
function copyShare(){
    const url = @json($invoice->publicUrl());
    navigator.clipboard.writeText(url).then(() => {
        alert('Link share disalin:\n' + url);
    }, () => { prompt('Salin link:', url); });
}
</script>

<style media="print">
    .sidebar, .topbar, .d-print-none, header, nav { display:none !important; }
    body, .main-content, .container-fluid { background:white !important; margin:0 !important; padding:0 !important; }
    .card { box-shadow:none !important; border:none !important; }
</style>
@endsection
