@extends('layouts.app')
@section('title', $quotation->quotation_number)

@section('content')
@php [$lbl,$clr] = $quotation->statusBadge(); @endphp

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 d-print-none">
    <div>
        <a href="{{ route('quotations.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Daftar Quotation</a>
        <h4 class="mb-0 mt-1" style="font-size:1.2rem;font-weight:700">
            <span class="font-monospace">{{ $quotation->quotation_number }}</span>
            <span class="badge bg-{{ $clr }} bg-opacity-10 text-{{ $clr }} ms-2" style="font-size:.72rem">{{ $lbl }}</span>
        </h4>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('quotations.pdf',$quotation) }}" target="_blank" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <button type="button" class="btn btn-sm btn-outline-info" onclick="copyQuotShare()"><i class="bi bi-link-45deg"></i> Salin Link Share</button>
        @if(!$quotation->sent_at && $quotation->status === 'draft')
            <form method="POST" action="{{ route('quotations.send',$quotation) }}" class="d-inline">@csrf
                <button class="btn btn-sm btn-primary"><i class="bi bi-send"></i> Tandai Terkirim</button>
            </form>
        @endif
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i> Print</button>
        @if($quotation->status !== 'converted')
            <a href="{{ route('quotations.edit',$quotation) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>

            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-arrow-repeat"></i> Ubah Status
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach(['draft'=>'Draft','sent'=>'Terkirim','accepted'=>'Diterima','rejected'=>'Ditolak','expired'=>'Kadaluarsa'] as $k=>$v)
                    <li>
                        <form method="POST" action="{{ route('quotations.status',$quotation) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $k }}">
                            <button class="dropdown-item small {{ $quotation->status===$k ? 'fw-bold' : '' }}" type="submit">
                                @if($quotation->status===$k)<i class="bi bi-check2 me-1"></i>@endif {{ $v }}
                            </button>
                        </form>
                    </li>
                    @endforeach
                </ul>
            </div>

            @if(in_array($quotation->status, ['accepted','sent','draft']))
            <form method="POST" action="{{ route('quotations.convert',$quotation) }}" onsubmit="return confirm('Konversi quotation ini menjadi invoice? Quotation akan dikunci.');">
                @csrf
                <button class="btn btn-sm btn-success"><i class="bi bi-arrow-right-circle"></i> Konversi ke Invoice</button>
            </form>
            @endif

            <form method="POST" action="{{ route('quotations.destroy',$quotation) }}" onsubmit="return confirm('Hapus quotation {{ $quotation->quotation_number }}?');">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
        @endif
    </div>
</div>

@if(session('success'))<div class="alert alert-success py-2 small">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2 small">{{ session('error') }}</div>@endif

<div class="d-flex flex-wrap gap-2 mb-3 small d-print-none">
    @if($quotation->sent_at)
        <span class="badge bg-primary-subtle text-primary border"><i class="bi bi-send-check"></i> Terkirim: {{ $quotation->sent_at->isoFormat('D MMM YYYY HH:mm') }}</span>
    @endif
    @if($quotation->viewed_at)
        <span class="badge bg-success-subtle text-success border"><i class="bi bi-eye"></i> Dilihat klien: {{ $quotation->viewed_at->isoFormat('D MMM YYYY HH:mm') }}</span>
    @else
        <span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-eye-slash"></i> Belum dilihat klien</span>
    @endif
</div>

<script>
function copyQuotShare(){
    const url = @json($quotation->publicUrl());
    navigator.clipboard.writeText(url).then(()=>alert('Link share disalin:\n'+url),()=>prompt('Salin link:',url));
}
</script>

@if($quotation->invoices->count())
<div class="alert alert-info py-2 small">
    <i class="bi bi-info-circle me-1"></i> Quotation ini sudah dikonversi ke invoice:
    @foreach($quotation->invoices as $inv)
        <a href="{{ route('invoices.show',$inv) }}" class="font-monospace fw-semibold">{{ $inv->invoice_number }}</a>@if(!$loop->last), @endif
    @endforeach
</div>
@endif

<div class="card"><div class="card-body p-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-7">
            @if($quotation->company)
                @if($quotation->company->logo)<img src="{{ asset('storage/'.$quotation->company->logo) }}" style="max-height:60px" class="mb-2"><br>@endif
                <strong style="font-size:1.05rem">{{ $quotation->company->name }}</strong><br>
                <span class="small text-muted">{{ $quotation->company->address ?? '' }}</span>
            @endif
        </div>
        <div class="col-5 text-end">
            <div class="text-uppercase text-muted" style="letter-spacing:.1em;font-size:.85rem">Quotation / Penawaran</div>
            <div class="font-monospace fw-bold" style="font-size:1.3rem">{{ $quotation->quotation_number }}</div>
            <div class="small">Tanggal: <strong>{{ $quotation->issue_date->isoFormat('D MMMM YYYY') }}</strong></div>
            @if($quotation->valid_until)<div class="small">Berlaku s/d: <strong>{{ $quotation->valid_until->isoFormat('D MMMM YYYY') }}</strong></div>@endif
        </div>
    </div>

    {{-- Client / Project --}}
    <div class="row mb-4">
        <div class="col-7">
            <div class="text-uppercase text-muted small mb-1">Kepada</div>
            <strong>{{ $quotation->client->name }}</strong><br>
            @if($quotation->client->contact_person)<span class="small">UP: {{ $quotation->client->contact_person }}</span><br>@endif
            @if($quotation->client->address)<span class="small text-muted">{{ $quotation->client->address }}</span><br>@endif
            @if($quotation->client->email)<span class="small text-muted">{{ $quotation->client->email }}</span>@endif
        </div>
        <div class="col-5">
            @if($quotation->project)
                <div class="text-uppercase text-muted small mb-1">Project</div>
                <strong class="font-monospace">{{ $quotation->project->code }}</strong> — {{ $quotation->project->name }}
            @endif
            @if($quotation->subject)
                <div class="text-uppercase text-muted small mb-1 mt-2">Perihal</div>
                <strong>{{ $quotation->subject }}</strong>
            @endif
        </div>
    </div>

    {{-- Items table --}}
    <div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="table-light" style="font-size:.82rem">
            <tr><th style="width:36px">#</th><th>Deskripsi</th><th class="text-end" style="width:80px">Qty</th><th style="width:70px">Unit</th><th class="text-end" style="width:130px">Harga</th><th class="text-end" style="width:140px">Jumlah</th></tr>
        </thead>
        <tbody style="font-size:.85rem">
            @foreach($quotation->items as $it)
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
            <tr><td colspan="5" class="text-end text-muted">Subtotal</td><td class="text-end font-monospace">Rp {{ number_format($quotation->subtotal,0,',','.') }}</td></tr>
            @if($quotation->discount > 0)
            <tr><td colspan="5" class="text-end text-muted">Diskon</td><td class="text-end font-monospace">- Rp {{ number_format($quotation->discount,0,',','.') }}</td></tr>
            @endif
            @if($quotation->tax_percent > 0)
            <tr><td colspan="5" class="text-end text-muted">PPN {{ rtrim(rtrim(number_format($quotation->tax_percent,2),'0'),'.') }}%</td><td class="text-end font-monospace">Rp {{ number_format($quotation->tax_amount,0,',','.') }}</td></tr>
            @endif
            <tr><td colspan="5" class="text-end fw-bold">TOTAL</td><td class="text-end font-monospace fw-bold text-success" style="font-size:1.1rem">Rp {{ number_format($quotation->total,0,',','.') }}</td></tr>
        </tfoot>
    </table>
    </div>

    @if($quotation->notes)
    <div class="mt-3"><strong class="small text-muted">Catatan:</strong><div class="small" style="white-space:pre-line">{{ $quotation->notes }}</div></div>
    @endif
    @if($quotation->terms)
    <div class="mt-3"><strong class="small text-muted">Syarat &amp; Ketentuan:</strong><div class="small text-muted" style="white-space:pre-line">{{ $quotation->terms }}</div></div>
    @endif

    <div class="row mt-5">
        <div class="col-6"></div>
        <div class="col-6 text-center">
            <div class="small text-muted mb-5">Hormat Kami,</div>
            <div style="border-top:1px solid #94a3b8; padding-top:6px; max-width:240px; margin:0 auto">
                <strong class="small">{{ $quotation->creator->name ?? '-' }}</strong>
            </div>
        </div>
    </div>
</div></div>

<style media="print">
    .sidebar, .topbar, .d-print-none, header, nav { display:none !important; }
    body, .main-content, .container-fluid { background:white !important; margin:0 !important; padding:0 !important; }
    .card { box-shadow:none !important; border:none !important; }
</style>
@endsection
