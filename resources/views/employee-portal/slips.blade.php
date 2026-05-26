@extends('layouts.app')
@section('title', 'Slip Gaji Saya')
@section('page-title', 'Slip Gaji Saya')

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Semua Slip Gaji</span>
    </div>
    @if($slips->isEmpty())
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 opacity-25"></i>Belum ada slip gaji.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>No. Slip</th>
                        <th>Periode</th>
                        <th>Gaji Pokok</th>
                        <th>Take Home Pay</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($slips as $slip)
                    <tr>
                        <td><span class="font-monospace text-muted" style="font-size:.78rem">{{ $slip->slip_number }}</span></td>
                        <td>{{ $slip->period_label }}</td>
                        <td>Rp {{ number_format($slip->basic_salary ?? 0, 0, ',', '.') }}</td>
                        <td class="fw-semibold text-success">Rp {{ number_format($slip->take_home_pay, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge badge-pill {{ $slip->status === 'published' ? 'badge-published' : 'badge-draft' }}">
                                {{ $slip->status === 'published' ? 'Published' : 'Draft' }}
                            </span>
                            @if($slip->isEmployeeSigned())
                                <span class="badge bg-success bg-opacity-10 text-success ms-1" style="font-size:.68rem" title="Anda sudah tanda tangan {{ $slip->employee_signed_at->format('d M Y H:i') }}">
                                    <i class="bi bi-pen-fill"></i> Ditandatangani
                                </span>
                            @elseif($slip->status === 'published')
                                <span class="badge bg-warning bg-opacity-10 text-warning ms-1" style="font-size:.68rem">
                                    <i class="bi bi-pen"></i> Perlu TTD
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($slip->status === 'published')
                                <a href="{{ route('my.slips.show', $slip) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>Lihat
                                </a>
                            @else
                                <span class="text-muted" style="font-size:.8rem">Belum diterbitkan</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($slips->hasPages())
            <div class="card-footer">{{ $slips->links() }}</div>
        @endif
    @endif
</div>
@endsection
