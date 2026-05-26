@php
    $rupiah = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
    [$lbl,$col] = $invoice->statusBadge();
@endphp
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Invoice {{ $invoice->invoice_number }}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background: #f3f4f6; }
    .doc { max-width: 880px; margin: 30px auto; background:#fff; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.1); padding: 32px; }
    .brand-bar { border-bottom:3px solid #1d4ed8; padding-bottom:14px; margin-bottom:18px; }
    .label { font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; }
    .totals td { padding:6px 8px; }
    .totals .grand { background:#1d4ed8; color:#fff; font-weight:bold; font-size:1.05rem; }
    .actions { position: sticky; top: 0; background:#1d4ed8; color:#fff; padding: 10px 20px; }
    .actions a, .actions button { color:#fff; }
</style>
</head>
<body>

<div class="actions d-flex justify-content-between align-items-center">
    <div><i class="bi bi-shield-check"></i> Tautan resmi dari {{ $invoice->company->name ?? '' }}</div>
    <div>
        <a href="{{ route('public.invoice.pdf', $invoice->share_token) }}" class="btn btn-light btn-sm">
            <i class="bi bi-download"></i> Download PDF
        </a>
        <button onclick="window.print()" class="btn btn-outline-light btn-sm d-none d-md-inline-block">
            <i class="bi bi-printer"></i> Cetak
        </button>
    </div>
</div>

<div class="doc">

    <div class="d-flex justify-content-between align-items-start brand-bar">
        <div>
            <h4 class="mb-1">{{ $invoice->company->name ?? 'Perusahaan' }}</h4>
            <div class="text-muted small">
                {{ $invoice->company->address ?? '' }}<br>
                {{ $invoice->company->phone ? 'Telp: '.$invoice->company->phone : '' }}
                {{ $invoice->company->email ? '· '.$invoice->company->email : '' }}
            </div>
        </div>
        <div class="text-end">
            <div class="h3 mb-1 text-primary"><strong>INVOICE</strong></div>
            <div><code>{{ $invoice->invoice_number }}</code></div>
            <span class="badge bg-{{ $col }} mt-1">{{ $lbl }}</span>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="label">Ditagihkan kepada</div>
            <div class="mt-1">
                <strong>{{ $invoice->client->name ?? '—' }}</strong><br>
                @if($invoice->client?->pic_name)<span class="text-muted">PIC: {{ $invoice->client->pic_name }}</span><br>@endif
                <span class="text-muted small">{{ $invoice->client->address ?? '' }}</span>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <div><span class="label">Tanggal Terbit:</span> <strong>{{ $invoice->issue_date?->format('d M Y') }}</strong></div>
            <div><span class="label">Jatuh Tempo:</span> <strong>{{ $invoice->due_date?->format('d M Y') ?: '—' }}</strong></div>
            @if($invoice->project)<div><span class="label">Project:</span> {{ $invoice->project->name }}</div>@endif
        </div>
    </div>

    @if($invoice->subject)
        <p><strong>Perihal:</strong> {{ $invoice->subject }}</p>
    @endif

    <div class="table-responsive">
    <table class="table table-sm">
        <thead class="table-primary">
            <tr><th>#</th><th>Deskripsi</th><th class="text-end" style="width:80px">Qty</th><th style="width:80px">Unit</th><th class="text-end" style="width:130px">Harga</th><th class="text-end" style="width:140px">Jumlah</th></tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $it)
                <tr>
                    <td>{{ $i+1 }}</td><td>{{ $it->description }}</td>
                    <td class="text-end">{{ rtrim(rtrim(number_format($it->quantity,2,',','.'),'0'),',') }}</td>
                    <td>{{ $it->unit }}</td>
                    <td class="text-end">{{ $rupiah($it->unit_price) }}</td>
                    <td class="text-end">{{ $rupiah($it->amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    <div class="row">
        <div class="col-md-6">
            @if($invoice->bankAccount)
                <div class="border rounded p-3 bg-light">
                    <div class="label mb-2">Pembayaran ditujukan ke</div>
                    <strong>{{ $invoice->bankAccount->bank_name }}</strong>
                    {{ $invoice->bankAccount->branch ? '— '.$invoice->bankAccount->branch : '' }}<br>
                    No. Rekening: <strong>{{ $invoice->bankAccount->account_number }}</strong><br>
                    a.n. <strong>{{ $invoice->bankAccount->account_name }}</strong>
                </div>
            @endif
        </div>
        <div class="col-md-6">
            <table class="totals w-100">
                <tr><td>Subtotal</td><td class="text-end">{{ $rupiah($invoice->subtotal) }}</td></tr>
                @if((float)$invoice->discount > 0)<tr><td>Diskon</td><td class="text-end">- {{ $rupiah($invoice->discount) }}</td></tr>@endif
                @if((float)$invoice->tax_percent > 0)<tr><td>Pajak ({{ $invoice->tax_percent }}%)</td><td class="text-end">{{ $rupiah($invoice->tax_amount) }}</td></tr>@endif
                <tr class="grand"><td>TOTAL</td><td class="text-end">{{ $rupiah($invoice->total) }}</td></tr>
                @if((float)$invoice->paid_amount > 0)
                    <tr><td>Dibayar</td><td class="text-end text-success">- {{ $rupiah($invoice->paid_amount) }}</td></tr>
                    <tr><td><strong>Sisa</strong></td><td class="text-end"><strong>{{ $rupiah($invoice->balance) }}</strong></td></tr>
                @endif
            </table>
        </div>
    </div>

    @if($invoice->notes)<div class="mt-3"><strong>Catatan:</strong><br>{!! nl2br(e($invoice->notes)) !!}</div>@endif
    @if($invoice->terms)<div class="mt-2 text-muted small"><strong>Syarat & Ketentuan:</strong><br>{!! nl2br(e($invoice->terms)) !!}</div>@endif

    <hr class="my-4">
    <div class="text-center text-muted small">
        Terima kasih atas kepercayaan Anda. Untuk pertanyaan, hubungi {{ $invoice->company->email ?? '' }}
    </div>
</div>

</body></html>
