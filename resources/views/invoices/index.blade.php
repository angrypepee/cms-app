@extends('layouts.app')
@section('title','Invoice')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1" style="font-size:1.15rem;font-weight:700"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>Invoice</h4>
        <div class="text-muted" style="font-size:.82rem">Tagihan ke klien B2B.</div>
    </div>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Invoice Baru
    </a>
</div>

@if(session('success'))<div class="alert alert-success py-2 small">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2 small">{{ session('error') }}</div>@endif

<div class="row g-2 mb-3">
    <div class="col-md-4">
        <div class="card border-warning"><div class="card-body py-2">
            <div class="text-uppercase text-muted" style="font-size:.7rem;letter-spacing:.06em">Outstanding</div>
            <div class="font-monospace fw-bold text-warning" style="font-size:1.15rem">Rp {{ number_format($summary['total_outstanding'],0,',','.') }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-success"><div class="card-body py-2">
            <div class="text-uppercase text-muted" style="font-size:.7rem;letter-spacing:.06em">Total Dibayar</div>
            <div class="font-monospace fw-bold text-success" style="font-size:1.15rem">Rp {{ number_format($summary['total_paid'],0,',','.') }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-danger"><div class="card-body py-2">
            <div class="text-uppercase text-muted" style="font-size:.7rem;letter-spacing:.06em">Jatuh Tempo (Overdue)</div>
            <div class="fw-bold text-danger" style="font-size:1.15rem">{{ $summary['count_overdue'] }} invoice</div>
        </div></div>
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="search" name="q" class="form-control form-control-sm" placeholder="Cari nomor / subjek / klien..." value="{{ $q }}"></div>
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">-- Semua Status --</option>
            @foreach(['draft'=>'Draft','sent'=>'Terkirim','partial'=>'Sebagian','paid'=>'Lunas','overdue'=>'Overdue','cancelled'=>'Dibatalkan'] as $k=>$v)
                <option value="{{ $k }}" @selected($status===$k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-funnel"></i> Filter</button></div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead style="font-size:.8rem"><tr>
                <th>No. Invoice</th><th>Tanggal</th><th>Jatuh Tempo</th><th>Klien</th><th class="text-end">Total</th><th class="text-end">Dibayar</th><th>Status</th><th></th>
            </tr></thead>
            <tbody>
                @forelse($invoices as $iv)
                @php [$lbl,$clr] = $iv->statusBadge(); @endphp
                <tr>
                    <td class="font-monospace" style="font-size:.78rem"><a href="{{ route('invoices.show',$iv) }}" class="text-decoration-none fw-semibold">{{ $iv->invoice_number }}</a></td>
                    <td style="font-size:.78rem">{{ $iv->issue_date->isoFormat('D MMM YY') }}</td>
                    <td style="font-size:.78rem {{ $iv->status==='overdue' ? 'text-danger fw-semibold' : '' }}">{{ $iv->due_date?->isoFormat('D MMM YY') ?? '-' }}</td>
                    <td style="font-size:.82rem">{{ $iv->client->name }}</td>
                    <td class="text-end font-monospace" style="font-size:.82rem">Rp {{ number_format($iv->total,0,',','.') }}</td>
                    <td class="text-end font-monospace text-success" style="font-size:.82rem">Rp {{ number_format($iv->paid_amount,0,',','.') }}</td>
                    <td><span class="badge bg-{{ $clr }} bg-opacity-10 text-{{ $clr }}" style="font-size:.7rem">{{ $lbl }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('invoices.show',$iv) }}" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox d-block fs-3 opacity-25 mb-1"></i>Belum ada invoice.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())<div class="px-3 py-2 border-top">{{ $invoices->links() }}</div>@endif
</div>
@endsection
