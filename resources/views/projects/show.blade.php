@extends('layouts.app')
@section('title', $project->code)

@section('content')
@php [$lbl,$clr] = $project->statusBadge(); @endphp

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('projects.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Daftar Project</a>
        <h4 class="mb-0 mt-1" style="font-size:1.2rem;font-weight:700">
            <i class="bi bi-kanban me-2 text-primary"></i>
            <span class="font-monospace">{{ $project->code }}</span>
            <span class="badge bg-{{ $clr }} bg-opacity-10 text-{{ $clr }} ms-2" style="font-size:.72rem">{{ $lbl }}</span>
        </h4>
        <div class="text-muted" style="font-size:.85rem">{{ $project->name }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('quotations.create', ['project_id'=>$project->id,'client_id'=>$project->client_id]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-plus"></i> Quotation</a>
        <a href="{{ route('invoices.create', ['project_id'=>$project->id,'client_id'=>$project->client_id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-receipt"></i> Invoice</a>
        <a href="{{ route('projects.edit',$project) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success py-2 small">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2 small">{{ session('error') }}</div>@endif

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <div class="card h-100"><div class="card-body">
            <h6 class="text-muted text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.06em">Detail Project</h6>
            <dl class="row mb-0 small">
                <dt class="col-sm-3 text-muted fw-normal">Klien</dt>
                <dd class="col-sm-9"><a href="{{ route('clients.show',$project->client) }}">{{ $project->client->name }}</a></dd>
                <dt class="col-sm-3 text-muted fw-normal">Penerbit</dt>
                <dd class="col-sm-9">{{ $project->company->name ?? '-' }}</dd>
                <dt class="col-sm-3 text-muted fw-normal">Periode</dt>
                <dd class="col-sm-9">{{ $project->start_date?->isoFormat('D MMM YYYY') ?? '-' }} → {{ $project->end_date?->isoFormat('D MMM YYYY') ?? '-' }}</dd>
                <dt class="col-sm-3 text-muted fw-normal">Budget</dt>
                <dd class="col-sm-9 font-monospace">Rp {{ number_format($project->budget,0,',','.') }}</dd>
                @if($project->description)
                <dt class="col-sm-3 text-muted fw-normal">Deskripsi</dt>
                <dd class="col-sm-9" style="white-space:pre-line">{{ $project->description }}</dd>
                @endif
                @if($project->notes)
                <dt class="col-sm-3 text-muted fw-normal">Catatan</dt>
                <dd class="col-sm-9 text-muted" style="white-space:pre-line">{{ $project->notes }}</dd>
                @endif
            </dl>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <h6 class="text-muted text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.06em">Ringkasan Finansial</h6>
            @php
                $totalQuoted = $project->quotations->sum('total');
                $totalInvoiced = $project->invoices->sum('total');
                $totalPaid = $project->invoices->sum('paid_amount');
            @endphp
            <div class="d-flex justify-content-between small py-1"><span class="text-muted">Total Quotation</span><strong class="font-monospace">Rp {{ number_format($totalQuoted,0,',','.') }}</strong></div>
            <div class="d-flex justify-content-between small py-1"><span class="text-muted">Total Invoice</span><strong class="font-monospace">Rp {{ number_format($totalInvoiced,0,',','.') }}</strong></div>
            <div class="d-flex justify-content-between small py-1 border-top mt-1 pt-2"><span class="text-success">Dibayar</span><strong class="font-monospace text-success">Rp {{ number_format($totalPaid,0,',','.') }}</strong></div>
            <div class="d-flex justify-content-between small py-1"><span class="text-warning">Outstanding</span><strong class="font-monospace text-warning">Rp {{ number_format($totalInvoiced - $totalPaid,0,',','.') }}</strong></div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <h6 class="text-muted text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.06em">Quotation</h6>
            @forelse($project->quotations as $q)
                @php [$ql,$qc] = $q->statusBadge(); @endphp
                <div class="d-flex justify-content-between border-bottom py-2 small">
                    <a href="{{ route('quotations.show',$q) }}" class="font-monospace text-decoration-none">{{ $q->quotation_number }}</a>
                    <div class="d-flex align-items-center gap-2">
                        <span class="font-monospace">Rp {{ number_format($q->total,0,',','.') }}</span>
                        <span class="badge bg-{{ $qc }} bg-opacity-10 text-{{ $qc }}" style="font-size:.68rem">{{ $ql }}</span>
                    </div>
                </div>
            @empty<div class="text-muted small">Belum ada quotation.</div>@endforelse
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <h6 class="text-muted text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.06em">Invoice</h6>
            @forelse($project->invoices as $i)
                @php [$il,$ic] = $i->statusBadge(); @endphp
                <div class="d-flex justify-content-between border-bottom py-2 small">
                    <a href="{{ route('invoices.show',$i) }}" class="font-monospace text-decoration-none">{{ $i->invoice_number }}</a>
                    <div class="d-flex align-items-center gap-2">
                        <span class="font-monospace">Rp {{ number_format($i->total,0,',','.') }}</span>
                        <span class="badge bg-{{ $ic }} bg-opacity-10 text-{{ $ic }}" style="font-size:.68rem">{{ $il }}</span>
                    </div>
                </div>
            @empty<div class="text-muted small">Belum ada invoice.</div>@endforelse
        </div></div>
    </div>
</div>
@endsection
