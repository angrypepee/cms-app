@php
    $rupiah = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
@endphp
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Quotation {{ $quotation->quotation_number }}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background:#f3f4f6; }
    .doc { max-width:880px; margin:30px auto; background:#fff; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.1); padding:32px; }
    .brand-bar { border-bottom:3px solid #7c3aed; padding-bottom:14px; margin-bottom:18px; }
    .label { font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; }
    .totals td { padding:6px 8px; }
    .totals .grand { background:#7c3aed; color:#fff; font-weight:bold; font-size:1.05rem; }
    .actions { position:sticky; top:0; background:#7c3aed; color:#fff; padding:10px 20px; }
    .actions a, .actions button { color:#fff; }
</style>
</head>
<body>

<div class="actions d-flex justify-content-between align-items-center">
    <div><i class="bi bi-shield-check"></i> Penawaran resmi dari {{ $quotation->company->name ?? '' }}</div>
    <div>
        <a href="{{ route('public.quotation.pdf', $quotation->share_token) }}" class="btn btn-light btn-sm">
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
            <h4 class="mb-1">{{ $quotation->company->name ?? 'Perusahaan' }}</h4>
            <div class="text-muted small">
                {{ $quotation->company->address ?? '' }}<br>
                {{ $quotation->company->phone ? 'Telp: '.$quotation->company->phone : '' }}
                {{ $quotation->company->email ? '· '.$quotation->company->email : '' }}
            </div>
        </div>
        <div class="text-end">
            <div class="h3 mb-1" style="color:#7c3aed"><strong>QUOTATION</strong></div>
            <div><code>{{ $quotation->quotation_number }}</code></div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="label">Ditujukan kepada</div>
            <strong>{{ $quotation->client->name ?? '—' }}</strong><br>
            @if($quotation->client?->pic_name)<span class="text-muted">PIC: {{ $quotation->client->pic_name }}</span><br>@endif
            <span class="text-muted small">{{ $quotation->client->address ?? '' }}</span>
        </div>
        <div class="col-md-6 text-md-end">
            <div><span class="label">Tanggal:</span> <strong>{{ $quotation->issue_date?->format('d M Y') }}</strong></div>
            <div><span class="label">Berlaku Sampai:</span> <strong>{{ $quotation->valid_until?->format('d M Y') ?: '—' }}</strong></div>
        </div>
    </div>

    @if($quotation->subject)<p><strong>Perihal:</strong> {{ $quotation->subject }}</p>@endif

    <div class="table-responsive">
    <table class="table table-sm">
        <thead style="background:#ede9fe">
            <tr><th>#</th><th>Deskripsi</th><th class="text-end" style="width:80px">Qty</th><th style="width:80px">Unit</th><th class="text-end" style="width:130px">Harga</th><th class="text-end" style="width:140px">Jumlah</th></tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $i => $it)
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
        <div class="col-md-6"></div>
        <div class="col-md-6">
            <table class="totals w-100">
                <tr><td>Subtotal</td><td class="text-end">{{ $rupiah($quotation->subtotal) }}</td></tr>
                @if((float)$quotation->discount > 0)<tr><td>Diskon</td><td class="text-end">- {{ $rupiah($quotation->discount) }}</td></tr>@endif
                @if((float)$quotation->tax_percent > 0)<tr><td>Pajak ({{ $quotation->tax_percent }}%)</td><td class="text-end">{{ $rupiah($quotation->tax_amount) }}</td></tr>@endif
                <tr class="grand"><td>TOTAL</td><td class="text-end">{{ $rupiah($quotation->total) }}</td></tr>
            </table>
        </div>
    </div>

    @if($quotation->notes)<div class="mt-3"><strong>Catatan:</strong><br>{!! nl2br(e($quotation->notes)) !!}</div>@endif
    @if($quotation->terms)<div class="mt-2 text-muted small"><strong>Syarat & Ketentuan:</strong><br>{!! nl2br(e($quotation->terms)) !!}</div>@endif
</div>

</body></html>
