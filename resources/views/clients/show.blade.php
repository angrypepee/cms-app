@extends('layouts.app')
@section('title', $client->name)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('clients.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Daftar Klien</a>
        <h4 class="mb-0 mt-1" style="font-size:1.2rem;font-weight:700"><i class="bi bi-briefcase me-2 text-primary"></i>{{ $client->name }}</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('quotations.create', ['client_id'=>$client->id]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-plus"></i> Buat Quotation</a>
        <a href="{{ route('invoices.create', ['client_id'=>$client->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-receipt"></i> Buat Invoice</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <h6 class="mb-3 text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.06em">Informasi Klien</h6>
            <div class="mb-2"><strong>{{ $client->name }}</strong></div>
            @if($client->contact_person)<div class="small mb-1"><i class="bi bi-person me-1 text-muted"></i>{{ $client->contact_person }}</div>@endif
            @if($client->email)<div class="small mb-1"><i class="bi bi-envelope me-1 text-muted"></i>{{ $client->email }}</div>@endif
            @if($client->phone)<div class="small mb-1"><i class="bi bi-telephone me-1 text-muted"></i>{{ $client->phone }}</div>@endif
            @if($client->npwp)<div class="small mb-1 font-monospace"><i class="bi bi-credit-card-2-front me-1 text-muted"></i>{{ $client->npwp }}</div>@endif
            @if($client->address)<div class="small mt-2 text-muted">{{ $client->address }}</div>@endif
            @if($client->company)<hr class="my-2"><div class="small text-muted">Issued by: <strong>{{ $client->company->name }}</strong></div>@endif
        </div></div>
    </div>
    <div class="col-md-8">
        <div class="card h-100"><div class="card-body">
            <h6 class="mb-3 text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.06em">Project Terbaru</h6>
            @forelse($projects as $p)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <div>
                        <a href="{{ route('projects.show',$p) }}" class="text-decoration-none fw-semibold small">{{ $p->code }}</a>
                        <div class="text-muted" style="font-size:.78rem">{{ $p->name }}</div>
                    </div>
                    @php [$lbl,$clr] = $p->statusBadge(); @endphp
                    <span class="badge bg-{{ $clr }} bg-opacity-10 text-{{ $clr }}" style="font-size:.7rem;height:fit-content">{{ $lbl }}</span>
                </div>
            @empty
                <div class="text-muted small">Belum ada project.</div>
            @endforelse
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <h6 class="mb-3 text-muted text-uppercase d-flex justify-content-between" style="font-size:.72rem;letter-spacing:.06em">
                Quotation Terbaru
                <a href="{{ route('quotations.index', ['q'=>$client->name]) }}" class="text-decoration-none small text-primary text-lowercase" style="letter-spacing:0">lihat semua</a>
            </h6>
            @forelse($quotations as $qt)
                @php [$lbl,$clr] = $qt->statusBadge(); @endphp
                <div class="d-flex justify-content-between border-bottom py-2">
                    <div>
                        <a href="{{ route('quotations.show',$qt) }}" class="text-decoration-none fw-semibold small font-monospace">{{ $qt->quotation_number }}</a>
                        <div class="text-muted" style="font-size:.75rem">{{ $qt->issue_date->isoFormat('D MMM YYYY') }} · Rp {{ number_format($qt->total,0,',','.') }}</div>
                    </div>
                    <span class="badge bg-{{ $clr }} bg-opacity-10 text-{{ $clr }}" style="font-size:.7rem;height:fit-content">{{ $lbl }}</span>
                </div>
            @empty
                <div class="text-muted small">Belum ada quotation.</div>
            @endforelse
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <h6 class="mb-3 text-muted text-uppercase d-flex justify-content-between" style="font-size:.72rem;letter-spacing:.06em">
                Invoice Terbaru
                <a href="{{ route('invoices.index', ['q'=>$client->name]) }}" class="text-decoration-none small text-primary text-lowercase" style="letter-spacing:0">lihat semua</a>
            </h6>
            @forelse($invoices as $iv)
                @php [$lbl,$clr] = $iv->statusBadge(); @endphp
                <div class="d-flex justify-content-between border-bottom py-2">
                    <div>
                        <a href="{{ route('invoices.show',$iv) }}" class="text-decoration-none fw-semibold small font-monospace">{{ $iv->invoice_number }}</a>
                        <div class="text-muted" style="font-size:.75rem">{{ $iv->issue_date->isoFormat('D MMM YYYY') }} · Rp {{ number_format($iv->total,0,',','.') }}</div>
                    </div>
                    <span class="badge bg-{{ $clr }} bg-opacity-10 text-{{ $clr }}" style="font-size:.7rem;height:fit-content">{{ $lbl }}</span>
                </div>
            @empty
                <div class="text-muted small">Belum ada invoice.</div>
            @endforelse
        </div></div>
    </div>
</div>
@endsection
