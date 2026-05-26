@php
    $rupiah = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
    $co = $quotation->company;
    $cl = $quotation->client;
@endphp
<!doctype html>
<html><head>
<meta charset="utf-8">
<title>{{ $quotation->quotation_number }}</title>
<style>
    @page { margin: 28px 32px; }
    * { font-family: DejaVu Sans, sans-serif; }
    body { color:#222; font-size: 11px; }
    .row { width:100%; } .row:after { content:""; display:table; clear:both; }
    .col { float:left; }
    .right { text-align:right; }
    .muted { color:#777; }
    .title { font-size: 28px; letter-spacing: 4px; color: #7c3aed; font-weight: bold; }
    .brand { font-size: 16px; font-weight: bold; color:#111; }
    table.items { width:100%; border-collapse: collapse; margin-top: 8px; }
    table.items th { background:#7c3aed; color:#fff; padding:8px 6px; text-align:left; font-size:11px; }
    table.items td { padding:7px 6px; border-bottom:1px solid #eee; vertical-align: top; }
    table.items tr.alt td { background:#fafafa; }
    .totals { width: 45%; float:right; margin-top:6px; }
    .totals td { padding:5px 6px; }
    .totals .label { color:#555; }
    .totals .grand { background:#7c3aed; color:#fff; font-weight:bold; font-size: 13px; }
    .box { border:1px solid #e5e7eb; padding:10px 12px; border-radius:4px; }
    .badge { display:inline-block; padding:3px 9px; border-radius:3px; color:#fff; font-size:10px; font-weight:bold; text-transform:uppercase; }
</style>
</head><body>

<div class="row">
    <div class="col" style="width:60%">
        <div class="brand">{{ $co->name ?? 'Perusahaan' }}</div>
        @if($co?->address)<div class="muted">{{ $co->address }}</div>@endif
        @if($co?->phone)<div class="muted">Telp: {{ $co->phone }}</div>@endif
        @if($co?->email)<div class="muted">Email: {{ $co->email }}</div>@endif
    </div>
    <div class="col right" style="width:40%">
        <div class="title">QUOTATION</div>
        <div style="margin-top:4px"><strong>{{ $quotation->quotation_number }}</strong></div>
    </div>
</div>

<hr style="border:none; border-top:2px solid #7c3aed; margin:12px 0;">

<div class="row">
    <div class="col" style="width:55%">
        <div class="muted" style="font-size:10px; text-transform:uppercase;">Ditujukan kepada</div>
        <div class="box" style="margin-top:4px">
            <strong>{{ $cl->name ?? '—' }}</strong><br>
            @if($cl?->pic_name)PIC: {{ $cl->pic_name }}<br>@endif
            @if($cl?->address)<span class="muted">{{ $cl->address }}</span><br>@endif
            @if($cl?->email)<span class="muted">{{ $cl->email }}</span>@endif
        </div>
    </div>
    <div class="col right" style="width:45%">
        <table style="float:right; font-size:11px;">
            <tr><td class="muted right" style="padding-right:8px">Tanggal:</td><td><strong>{{ $quotation->issue_date?->format('d M Y') }}</strong></td></tr>
            <tr><td class="muted right" style="padding-right:8px">Berlaku Sampai:</td><td><strong>{{ $quotation->valid_until?->format('d M Y') ?: '—' }}</strong></td></tr>
            @if($quotation->project)<tr><td class="muted right" style="padding-right:8px">Project:</td><td>{{ $quotation->project->name }}</td></tr>@endif
        </table>
    </div>
</div>

@if($quotation->subject)
    <div style="margin-top:14px"><strong>Perihal:</strong> {{ $quotation->subject }}</div>
@endif

<table class="items">
    <thead>
        <tr>
            <th style="width:34px">#</th>
            <th>Deskripsi</th>
            <th style="width:50px" class="right">Qty</th>
            <th style="width:50px">Unit</th>
            <th style="width:90px" class="right">Harga</th>
            <th style="width:100px" class="right">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @foreach($quotation->items as $i => $it)
            <tr class="{{ $i%2 ? 'alt' : '' }}">
                <td>{{ $i+1 }}</td>
                <td>{{ $it->description }}</td>
                <td class="right">{{ rtrim(rtrim(number_format($it->quantity,2,',','.'),'0'),',') }}</td>
                <td>{{ $it->unit }}</td>
                <td class="right">{{ $rupiah($it->unit_price) }}</td>
                <td class="right">{{ $rupiah($it->amount) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr><td class="label">Subtotal</td><td class="right">{{ $rupiah($quotation->subtotal) }}</td></tr>
    @if((float)$quotation->discount > 0)
        <tr><td class="label">Diskon</td><td class="right">- {{ $rupiah($quotation->discount) }}</td></tr>
    @endif
    @if((float)$quotation->tax_percent > 0)
        <tr><td class="label">Pajak ({{ rtrim(rtrim(number_format($quotation->tax_percent,2,',','.'),'0'),',') }}%)</td><td class="right">{{ $rupiah($quotation->tax_amount) }}</td></tr>
    @endif
    <tr class="grand"><td>TOTAL</td><td class="right">{{ $rupiah($quotation->total) }}</td></tr>
</table>

<div style="clear:both"></div>

@if($quotation->notes)
    <div style="margin-top:14px"><strong>Catatan:</strong><br>{!! nl2br(e($quotation->notes)) !!}</div>
@endif

@if($quotation->terms)
    <div style="margin-top:10px"><strong>Syarat & Ketentuan:</strong><br><span class="muted">{!! nl2br(e($quotation->terms)) !!}</span></div>
@endif

<div style="margin-top:40px" class="row">
    <div class="col" style="width:50%"></div>
    <div class="col" style="width:50%; text-align:center">
        Hormat kami,<br><br><br><br>
        <strong style="border-top:1px solid #999; padding-top:4px">{{ $co->name ?? '' }}</strong>
    </div>
</div>

<div style="margin-top:20px; text-align:center; color:#aaa; font-size:10px;">
    Dokumen ini dihasilkan otomatis — {{ now()->format('d M Y H:i') }}
</div>

</body></html>
