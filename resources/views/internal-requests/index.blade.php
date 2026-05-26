@extends('layouts.app')

@section('page-title', 'Permohonan Karyawan')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Permohonan Karyawan</h4>
        <p class="text-muted small mb-0">Kelola permohonan, pengaduan, dan surat keterangan</p>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input name="search" type="text" class="form-control form-control-sm" placeholder="Cari nama/subjek..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}" @selected(request('type')==$t)>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending"  @selected(request('status')=='pending')>Menunggu</option>
                    <option value="diproses" @selected(request('status')=='diproses')>Diproses</option>
                    <option value="selesai"  @selected(request('status')=='selesai')>Selesai</option>
                    <option value="ditolak"  @selected(request('status')=='ditolak')>Ditolak</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Cari</button>
                <a href="{{ route('internal-requests.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Karyawan</th>
                        <th>Tipe</th>
                        <th>Subjek</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $req->employee->name }}</div>
                                <div class="text-muted small">{{ $req->employee->company->name ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $req->typeBadgeClass() }} badge-pill">{{ $req->typeLabel() }}</span>
                            </td>
                            <td class="small" style="max-width:220px">
                                <div class="text-truncate" title="{{ $req->subject }}">{{ $req->subject }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $req->statusBadgeClass() }} badge-pill">{{ $req->statusLabel() }}</span>
                            </td>
                            <td class="small text-muted">{{ $req->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('internal-requests.show', $req) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Tidak ada permohonan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($requests->hasPages())
        <div class="card-footer">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
