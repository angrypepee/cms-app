@extends('layouts.app')
@section('title','Projects')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1" style="font-size:1.15rem;font-weight:700"><i class="bi bi-kanban me-2 text-primary"></i>Project</h4>
        <div class="text-muted" style="font-size:.82rem">Daftar project bisnis B2B.</div>
    </div>
    <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Project Baru
    </a>
</div>

@if(session('success'))<div class="alert alert-success py-2" style="font-size:.85rem">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2" style="font-size:.85rem">{{ session('error') }}</div>@endif

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4"><input type="search" name="q" class="form-control form-control-sm" placeholder="Cari kode / nama..." value="{{ $q }}"></div>
    <div class="col-md-3">
        <select name="client_id" class="form-select form-select-sm">
            <option value="">-- Semua Klien --</option>
            @foreach($clients as $cl)<option value="{{ $cl->id }}" @selected($clientId == $cl->id)>{{ $cl->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">-- Semua Status --</option>
            @foreach(['planning'=>'Planning','active'=>'Aktif','on_hold'=>'On Hold','completed'=>'Selesai','cancelled'=>'Dibatalkan'] as $k=>$v)
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
                <th>Kode</th><th>Nama Project</th><th>Klien</th><th>Periode</th><th class="text-end">Budget</th><th>Status</th><th class="text-end">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($projects as $p)
                @php [$lbl,$clr] = $p->statusBadge(); @endphp
                <tr>
                    <td class="font-monospace" style="font-size:.78rem"><a href="{{ route('projects.show',$p) }}" class="text-decoration-none fw-semibold">{{ $p->code }}</a></td>
                    <td style="font-size:.85rem">{{ $p->name }}</td>
                    <td style="font-size:.82rem"><a href="{{ route('clients.show',$p->client) }}" class="text-decoration-none text-muted">{{ $p->client->name }}</a></td>
                    <td style="font-size:.76rem">
                        {{ $p->start_date?->isoFormat('D MMM YY') ?? '-' }} → {{ $p->end_date?->isoFormat('D MMM YY') ?? '-' }}
                    </td>
                    <td class="text-end font-monospace" style="font-size:.82rem">Rp {{ number_format($p->budget,0,',','.') }}</td>
                    <td><span class="badge bg-{{ $clr }} bg-opacity-10 text-{{ $clr }}" style="font-size:.7rem">{{ $lbl }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('projects.show',$p) }}" class="btn btn-sm btn-outline-secondary btn-icon" title="Detail"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('projects.edit',$p) }}" class="btn btn-sm btn-outline-primary btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-inbox d-block fs-3 opacity-25 mb-1"></i>Belum ada project.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($projects->hasPages())<div class="px-3 py-2 border-top">{{ $projects->links() }}</div>@endif
</div>
@endsection
