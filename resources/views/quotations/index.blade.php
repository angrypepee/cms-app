@extends('layouts.app')
@section('title','Quotation')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1" style="font-size:1.15rem;font-weight:700"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Quotation</h4>
        <div class="text-muted" style="font-size:.82rem">Penawaran harga untuk klien B2B.</div>
    </div>
    <a href="{{ route('quotations.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Quotation Baru
    </a>
</div>

@if(session('success'))<div class="alert alert-success py-2 small">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2 small">{{ session('error') }}</div>@endif

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="search" name="q" class="form-control form-control-sm" placeholder="Cari nomor / subjek / klien..." value="{{ $q }}"></div>
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">-- Semua Status --</option>
            @foreach(['draft'=>'Draft','sent'=>'Terkirim','accepted'=>'Diterima','rejected'=>'Ditolak','expired'=>'Kadaluarsa','converted'=>'Konversi → Invoice'] as $k=>$v)
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
                <th>No. Quotation</th><th>Tanggal</th><th>Klien</th><th>Subjek</th><th>Berlaku s/d</th><th class="text-end">Total</th><th>Status</th><th></th>
            </tr></thead>
            <tbody>
                @forelse($quotations as $q)
                @php [$lbl,$clr] = $q->statusBadge(); @endphp
                <tr>
                    <td class="font-monospace" style="font-size:.78rem"><a href="{{ route('quotations.show',$q) }}" class="text-decoration-none fw-semibold">{{ $q->quotation_number }}</a></td>
                    <td style="font-size:.78rem">{{ $q->issue_date->isoFormat('D MMM YY') }}</td>
                    <td style="font-size:.82rem">{{ $q->client->name }}</td>
                    <td style="font-size:.82rem">{{ \Illuminate\Support\Str::limit($q->subject, 40) }}</td>
                    <td style="font-size:.78rem">{{ $q->valid_until?->isoFormat('D MMM YY') ?? '-' }}</td>
                    <td class="text-end font-monospace" style="font-size:.82rem">Rp {{ number_format($q->total,0,',','.') }}</td>
                    <td><span class="badge bg-{{ $clr }} bg-opacity-10 text-{{ $clr }}" style="font-size:.7rem">{{ $lbl }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('quotations.show',$q) }}" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox d-block fs-3 opacity-25 mb-1"></i>Belum ada quotation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($quotations->hasPages())<div class="px-3 py-2 border-top">{{ $quotations->links() }}</div>@endif
</div>
@endsection
