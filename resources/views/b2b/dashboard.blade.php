@extends('layouts.app')
@section('title','Dashboard B2B')

@section('content')
@php
    $rupiah = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-1"><i class="bi bi-speedometer2 text-primary"></i> Dashboard B2B</h3>
        <p class="text-muted mb-0 small">Ringkasan bisnis: revenue, piutang, pipeline, dan aging</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('quotations.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Quotation Baru
        </a>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Invoice Baru
        </a>
    </div>
</div>

{{-- ── Top KPI cards ── --}}
<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-uppercase small text-muted fw-semibold">Revenue YTD</div>
                <div class="h4 mb-0 fw-bold text-success">{{ $rupiah($summary['revenue_ytd']) }}</div>
                <div class="small text-muted mt-1">Bulan ini: {{ $rupiah($summary['revenue_mtd']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-uppercase small text-muted fw-semibold">Piutang Aktif</div>
                <div class="h4 mb-0 fw-bold text-primary">{{ $rupiah($summary['outstanding']) }}</div>
                <div class="small text-muted mt-1">{{ $summary['invoices_open'] }} invoice terbuka</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 {{ $summary['overdue_count']>0?'border-danger':'' }}" style="border-left-width: 4px !important;">
            <div class="card-body">
                <div class="text-uppercase small text-muted fw-semibold">Jatuh Tempo</div>
                <div class="h4 mb-0 fw-bold text-danger">{{ $rupiah($summary['overdue_amount']) }}</div>
                <div class="small text-muted mt-1">{{ $summary['overdue_count'] }} invoice overdue</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-uppercase small text-muted fw-semibold">Pipeline Quotation</div>
                <div class="h4 mb-0 fw-bold text-info">{{ $rupiah($summary['quotation_pipeline']) }}</div>
                <div class="small text-muted mt-1">{{ $summary['quotations_open'] }} quotation draft/sent</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-2 col-4"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
        <div class="small text-muted">Klien Aktif</div><div class="h5 mb-0">{{ $summary['clients_active'] }}</div>
    </div></div></div>
    <div class="col-md-2 col-4"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
        <div class="small text-muted">Project Aktif</div><div class="h5 mb-0">{{ $summary['projects_active'] }}</div>
    </div></div></div>
    <div class="col-md-2 col-4"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
        <div class="small text-muted">Quotation Terbuka</div><div class="h5 mb-0">{{ $summary['quotations_open'] }}</div>
    </div></div></div>
    <div class="col-md-2 col-4"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
        <div class="small text-muted">Invoice Terbuka</div><div class="h5 mb-0">{{ $summary['invoices_open'] }}</div>
    </div></div></div>
    <div class="col-md-4 col-12"><a href="{{ route('invoices.index', ['status'=>'overdue']) }}" class="text-decoration-none"><div class="card border-danger shadow-sm"><div class="card-body py-2 text-center text-danger">
        <i class="bi bi-exclamation-triangle"></i> <strong>{{ $summary['overdue_count'] }}</strong> invoice jatuh tempo perlu tindak lanjut
    </div></div></a></div>
</div>

{{-- ── Charts & Aging ── --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><strong><i class="bi bi-graph-up"></i> Revenue 6 Bulan Terakhir</strong></div>
            <div class="card-body">
                <canvas id="revenueChart" height="110"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><strong><i class="bi bi-clock-history"></i> Aging Piutang</strong></div>
            <div class="card-body">
                @php
                    $agingTotal = max(1, array_sum($aging));
                    $agingDef = [
                        ['current','Belum Jatuh Tempo','success'],
                        ['1_30','1–30 Hari','warning'],
                        ['31_60','31–60 Hari','orange'],
                        ['61_90','61–90 Hari','danger'],
                        ['over_90','> 90 Hari','dark'],
                    ];
                @endphp
                @foreach($agingDef as [$key,$label,$color])
                    @php $val = $aging[$key]; $pct = round(($val/$agingTotal)*100,1); @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small">
                            <span>{{ $label }}</span>
                            <strong>{{ $rupiah($val) }}</strong>
                        </div>
                        <div class="progress" style="height:8px">
                            <div class="progress-bar bg-{{ $color==='orange'?'warning':$color }}" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── Top clients + recent ── --}}
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><strong><i class="bi bi-trophy"></i> Top 5 Klien</strong></div>
            <div class="list-group list-group-flush">
                @forelse($topClients as $i => $row)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-light text-dark me-2">{{ $i+1 }}</span>
                            <a href="{{ $row->client ? route('clients.show', $row->client) : '#' }}">{{ $row->client->name ?? '—' }}</a>
                            <div class="small text-muted ms-4">{{ $row->inv_count }} invoice</div>
                        </div>
                        <strong class="text-success">{{ $rupiah($row->revenue) }}</strong>
                    </div>
                @empty
                    <div class="list-group-item text-muted text-center py-4">Belum ada data</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between">
                <strong><i class="bi bi-file-earmark-spreadsheet"></i> Invoice Terbaru</strong>
                <a href="{{ route('invoices.index') }}" class="small">Lihat semua</a>
            </div>
            <div class="list-group list-group-flush" style="max-height:340px;overflow-y:auto">
                @forelse($recentInvoices as $inv)
                    @php [$lbl,$col] = $inv->statusBadge(); @endphp
                    <a href="{{ route('invoices.show',$inv) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">{{ $inv->invoice_number }}</span>
                            <span class="badge bg-{{ $col }}">{{ $lbl }}</span>
                        </div>
                        <div class="small text-muted d-flex justify-content-between">
                            <span>{{ $inv->client->name ?? '—' }}</span>
                            <strong class="text-dark">{{ $rupiah($inv->total) }}</strong>
                        </div>
                    </a>
                @empty
                    <div class="list-group-item text-muted text-center py-4">Belum ada invoice</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><strong><i class="bi bi-cash-coin"></i> Pembayaran Terbaru</strong></div>
            <div class="list-group list-group-flush" style="max-height:340px;overflow-y:auto">
                @forelse($recentPayments as $p)
                    <a href="{{ route('invoices.show',$p->invoice) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">{{ $p->invoice->invoice_number ?? '—' }}</span>
                            <strong class="text-success">+{{ $rupiah($p->amount) }}</strong>
                        </div>
                        <div class="small text-muted d-flex justify-content-between">
                            <span>{{ $p->invoice->client->name ?? '—' }}</span>
                            <span>{{ $p->payment_date?->format('d M Y') }}</span>
                        </div>
                    </a>
                @empty
                    <div class="list-group-item text-muted text-center py-4">Belum ada pembayaran</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
    const chartData = @json($chart);
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(r => r.label),
            datasets: [
                { label: 'Tertagih',  data: chartData.map(r => r.invoiced), backgroundColor: 'rgba(37,99,235,.7)' },
                { label: 'Diterima',  data: chartData.map(r => r.paid),     backgroundColor: 'rgba(34,197,94,.85)' },
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: c => c.dataset.label + ': Rp ' + c.parsed.y.toLocaleString('id-ID') } }
            },
            scales: { y: { ticks: { callback: v => 'Rp ' + Number(v).toLocaleString('id-ID') } } }
        }
    });
})();
</script>
@endsection
