@php
    $rupiah = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
    $co = $invoice->company;
    $cl = $invoice->client;
    $bank = $invoice->bankAccount;
@endphp
<!doctype html>
<html><head>
<meta charset="utf-8">
<title>{{ $invoice->invoice_number }}</title>
<style>
    @page { margin: 28px 32px; }
    * { font-family: DejaVu Sans, sans-serif; }
    body { color:#222; font-size: 11px; }
    h1,h2,h3,h4 { margin: 0; }
    .row { width:100%; }
    .row:after { content:""; display:table; clear:both; }
    .col { float:left; }
    .right { text-align:right; }
    .muted { color:#777; }
    .title { font-size: 28px; letter-spacing: 4px; color: #1d4ed8; font-weight: bold; }
    .brand { font-size: 16px; font-weight: bold; color:#111; }
    table.items { width:100%; border-collapse: collapse; margin-top: 8px; }
    table.items th { background:#1d4ed8; color:#fff; padding:8px 6px; text-align:left; font-size:11px; }
    table.items td { padding:7px 6px; border-bottom:1px solid #eee; vertical-align: top; }
    table.items tr.alt td { background:#fafafa; }
    .totals { width: 45%; float:right; margin-top:6px; }
    .totals td { padding:5px 6px; }
    .totals .label { color:#555; }
    .totals .grand { background:#1d4ed8; color:#fff; font-weight:bold; font-size: 13px; }
    .badge { display:inline-block; padding:3px 9px; border-radius:3px; color:#fff; font-size:10px; font-weight:bold; text-transform:uppercase; }
    .b-paid { background:#16a34a; }
    .b-partial { background:#d97706; }
    .b-overdue { background:#dc2626; }
    .b-sent { background:#2563eb; }
    .b-draft { background:#6b7280; }
    .b-cancelled { background:#111; }
    .box { border:1px solid #e5e7eb; padding:10px 12px; border-radius:4px; }
    .stamp-paid {
        position: absolute; top: 180px; right: 60px; transform: rotate(-18deg);
        border: 4px solid #16a34a; color:#16a34a; padding: 4px 18px; font-size: 32px;
        font-weight: bold; letter-spacing: 4px; opacity: .8;
    }
</style>
</head><body>

@if($invoice->status === 'paid')
    <div class="stamp-paid">LUNAS</div>
@endif

<div class="row">
    <div class="col" style="width:60%">
        <div class="brand">{{ $co->name ?? 'Perusahaan' }}</div>
        @if($co?->address)<div class="muted">{{ $co->address }}</div>@endif
        @if($co?->phone)<div class="muted">Telp: {{ $co->phone }}</div>@endif
        @if($co?->email)<div class="muted">Email: {{ $co->email }}</div>@endif
    </div>
    <div class="col right" style="width:40%">
        <div class="title">INVOICE</div>
        <div style="margin-top:4px"><strong>{{ $invoice->invoice_number }}</strong></div>
        @php $sb = $invoice->statusBadge(); @endphp
        <div style="margin-top:4px">
            <span class="badge b-{{ $invoice->status }}">{{ $sb[0] }}</span>
        </div>
    </div>
</div>

<hr style="border:none; border-top:2px solid #1d4ed8; margin:12px 0;">

<div class="row">
    <div class="col" style="width:55%">
        <div class="muted" style="font-size:10px; text-transform:uppercase;">Ditagihkan kepada</div>
        <div class="box" style="margin-top:4px">
            <strong>{{ $cl->name ?? '—' }}</strong><br>
            @if($cl?->pic_name)PIC: {{ $cl->pic_name }}<br>@endif
            @if($cl?->address)<span class="muted">{{ $cl->address }}</span><br>@endif
            @if($cl?->phone)<span class="muted">Telp: {{ $cl->phone }}</span><br>@endif
            @if($cl?->email)<span class="muted">Email: {{ $cl->email }}</span>@endif
        </div>
    </div>
    <div class="col right" style="width:45%">
        <table style="float:right; font-size:11px;">
            <tr><td class="muted right" style="padding-right:8px">Tanggal Terbit:</td><td><strong>{{ $invoice->issue_date?->format('d M Y') }}</strong></td></tr>
            <tr><td class="muted right" style="padding-right:8px">Jatuh Tempo:</td><td><strong>{{ $invoice->due_date?->format('d M Y') ?: '—' }}</strong></td></tr>
            @if($invoice->project)<tr><td class="muted right" style="padding-right:8px">Project:</td><td>{{ $invoice->project->name }}</td></tr>@endif
            @if($invoice->quotation)<tr><td class="muted right" style="padding-right:8px">Ref. Quotation:</td><td>{{ $invoice->quotation->quotation_number }}</td></tr>@endif
        </table>
    </div>
</div>

@if($invoice->subject)
    <div style="margin-top:14px"><strong>Perihal:</strong> {{ $invoice->subject }}</div>
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
        @foreach($invoice->items as $i => $it)
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
    <tr><td class="label">Subtotal</td><td class="right">{{ $rupiah($invoice->subtotal) }}</td></tr>
    @if((float)$invoice->discount > 0)
        <tr><td class="label">Diskon</td><td class="right">- {{ $rupiah($invoice->discount) }}</td></tr>
    @endif
    @if((float)$invoice->tax_percent > 0)
        <tr><td class="label">Pajak ({{ rtrim(rtrim(number_format($invoice->tax_percent,2,',','.'),'0'),',') }}%)</td><td class="right">{{ $rupiah($invoice->tax_amount) }}</td></tr>
    @endif
    <tr class="grand"><td>TOTAL</td><td class="right">{{ $rupiah($invoice->total) }}</td></tr>
    @if((float)$invoice->paid_amount > 0)
        <tr><td class="label">Dibayar</td><td class="right" style="color:#16a34a">- {{ $rupiah($invoice->paid_amount) }}</td></tr>
        <tr><td class="label"><strong>Sisa</strong></td><td class="right"><strong>{{ $rupiah($invoice->balance) }}</strong></td></tr>
    @endif
</table>

<div style="clear:both"></div>

@if($bank)
<div style="margin-top:18px">
    <div class="muted" style="font-size:10px; text-transform:uppercase;">Pembayaran ditujukan ke</div>
    <div class="box" style="margin-top:4px; background:#f8fafc">
        <strong>{{ $bank->bank_name }}</strong> {{ $bank->branch ? '— '.$bank->branch : '' }}<br>
        No. Rekening: <strong>{{ $bank->account_number }}</strong><br>
        Atas Nama: <strong>{{ $bank->account_name }}</strong>
        @if($bank->swift_code)<br>SWIFT: {{ $bank->swift_code }}@endif
    </div>
</div>
@endif

@if($invoice->payments->count())
<div style="margin-top:14px">
    <strong>Riwayat Pembayaran</strong>
    <table class="items" style="margin-top:4px">
        <thead><tr><th>Tanggal</th><th>Metode</th><th>Referensi</th><th class="right">Jumlah</th></tr></thead>
        <tbody>
            @foreach($invoice->payments as $p)
                <tr><td>{{ $p->payment_date?->format('d M Y') }}</td><td>{{ $p->method ?: '—' }}</td><td>{{ $p->reference ?: '—' }}</td><td class="right">{{ $rupiah($p->amount) }}</td></tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if($invoice->notes)
    <div style="margin-top:14px"><strong>Catatan:</strong><br>{!! nl2br(e($invoice->notes)) !!}</div>
@endif

@if($invoice->terms)
    <div style="margin-top:10px"><strong>Syarat & Ketentuan:</strong><br><span class="muted">{!! nl2br(e($invoice->terms)) !!}</span></div>
@endif

<div style="margin-top:30px; text-align:center; color:#aaa; font-size:10px;">
    Dokumen ini dihasilkan otomatis dari sistem — {{ now()->format('d M Y H:i') }}
</div>

</body></html>
