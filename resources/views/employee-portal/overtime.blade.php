@extends('layouts.app')
@section('title', 'Pengajuan Lembur Saya')
@section('page-title', 'Pengajuan Lembur')

@section('content')
<div class="row g-4">

    {{-- Submit Form --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-plus-circle me-2 text-warning"></i>Ajukan Lembur</span>
            </div>
            <div class="card-body px-4 py-4">
                @if(session('success'))
                    <div class="alert alert-success alert-sm py-2 mb-3" style="font-size:.84rem">{{ session('success') }}</div>
                @endif
                <form method="POST" action="{{ route('my.overtime.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium">Tanggal Lembur</label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                            value="{{ old('date', now()->format('Y-m-d')) }}" min="{{ now()->format('Y-m-d') }}" required>
                        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Jam Mulai Lembur</label>
                        <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror"
                            value="{{ old('start_time') }}" required>
                        @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Jam Selesai Lembur</label>
                        <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror"
                            value="{{ old('end_time') }}" required>
                        @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Alasan / Keterangan</label>
                        <textarea name="reason" rows="3" class="form-control @error('reason') is-invalid @enderror"
                            placeholder="Jelaskan keperluan lembur…" required>{{ old('reason') }}</textarea>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-warning w-100 fw-semibold">
                        <i class="bi bi-send me-1"></i>Kirim Pengajuan
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- History --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-clock-history me-2 text-muted"></i>Riwayat Pengajuan</span>
            </div>
            @if($overtimeRequests->isEmpty())
                <div class="text-center py-5" style="color:#94a3b8;font-size:.85rem">
                    <i class="bi bi-clock d-block mb-2" style="font-size:2rem;opacity:.3"></i>
                    Belum ada pengajuan lembur.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Waktu Lembur</th>
                                <th>Durasi</th>
                                <th>Alasan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overtimeRequests as $ot)
                            <tr>
                                <td style="font-size:.875rem">{{ $ot->date->isoFormat('D MMM YYYY') }}</td>
                                <td class="fw-semibold" style="font-size:.875rem;color:#d97706">
                                    {{ substr($ot->start_time, 0, 5) }} &ndash; {{ substr($ot->end_time, 0, 5) }}
                                </td>
                                <td style="font-size:.82rem;color:#64748b">{{ $ot->durationLabel() }}</td>
                                <td class="text-muted" style="font-size:.82rem;max-width:180px">
                                    {{ Str::limit($ot->reason, 50) }}
                                </td>
                                <td>
                                    <span class="badge badge-pill {{ $ot->statusBadgeClass() }}">{{ $ot->statusLabel() }}</span>
                                    @if($ot->admin_notes)
                                        <div class="text-muted mt-1" style="font-size:.72rem">{{ Str::limit($ot->admin_notes, 40) }}</div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($overtimeRequests->hasPages())
                    <div class="px-4 py-3 border-top">{{ $overtimeRequests->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
