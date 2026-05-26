@extends('layouts.app')

@section('page-title', 'Cuti & Izin Saya')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Cuti & Izin Saya</h4>
        <p class="text-muted small mb-0">Riwayat dan status permohonan cuti Anda</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajukanCutiModal">
        <i class="bi bi-plus-lg me-1"></i> Ajukan Cuti
    </button>
</div>

{{-- Quota cards --}}
<div class="row g-3 mb-4">
    @foreach($leaveTypes as $lt)
        @php
            $used = $usageByType[$lt->id] ?? 0;
            $max  = $lt->max_days_per_year;
        @endphp
        <div class="col-md-3 col-sm-6">
            <div class="card h-100">
                <div class="card-body text-center py-3">
                    <div class="fw-bold small" style="color:{{ $lt->color }}">{{ $lt->name }}</div>
                    @if($max > 0)
                        <div class="stat-value my-1">{{ $max - $used }}</div>
                        <div class="stat-label">sisa dari {{ $max }} hari</div>
                        <div class="progress mt-2" style="height:4px">
                            <div class="progress-bar" role="progressbar"
                                 style="width:{{ min(100, round($used/$max*100)) }}%;background:{{ $lt->color }}"></div>
                        </div>
                    @else
                        <div class="stat-value my-1">{{ $used }}</div>
                        <div class="stat-label">hari digunakan (tak terbatas)</div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Year filter --}}
<div class="d-flex align-items-center gap-2 mb-3">
    <label class="text-muted small">Tahun:</label>
    @for($y = now()->year; $y >= now()->year - 2; $y--)
        <a href="{{ route('my.leaves', ['year' => $y]) }}"
           class="btn btn-sm {{ $y == $year ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $y }}</a>
    @endfor
</div>

{{-- List --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tipe</th>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Catatan Admin</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $lr)
                        <tr>
                            <td>
                                <span class="badge badge-pill" style="background-color:{{ $lr->leaveType->color ?? '#2563eb' }}">
                                    {{ $lr->leaveType->name }}
                                </span>
                            </td>
                            <td class="small">
                                {{ $lr->start_date->format('d M Y') }}
                                @if(!$lr->start_date->equalTo($lr->end_date))
                                    &ndash; {{ $lr->end_date->format('d M Y') }}
                                @endif
                            </td>
                            <td class="text-center fw-semibold">{{ $lr->days_count }}</td>
                            <td class="small" style="max-width:180px">
                                <div class="text-truncate" title="{{ $lr->reason }}">{{ $lr->reason }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $lr->statusBadgeClass() }} badge-pill">{{ $lr->statusLabel() }}</span>
                            </td>
                            <td class="small text-muted fst-italic">{{ $lr->admin_notes ?? '-' }}</td>
                            <td>
                                @if($lr->status === 'pending')
                                    <form method="POST" action="{{ route('my.leaves.destroy', $lr) }}"
                                          onsubmit="return confirm('Batalkan permohonan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Batalkan">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>
                                Belum ada permohonan cuti tahun {{ $year }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Ajukan Cuti Modal --}}
<div class="modal fade" id="ajukanCutiModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('my.leaves.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajukan Cuti / Izin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Tipe Cuti <span class="text-danger">*</span></label>
                        <select name="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                            <option value="">-- Pilih tipe --</option>
                            @foreach($leaveTypes as $lt)
                                <option value="{{ $lt->id }}" @selected(old('leave_type_id')==$lt->id)>
                                    {{ $lt->name }} @if($lt->max_days_per_year > 0)(maks. {{ $lt->max_days_per_year }} hari)@endif
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-2 mb-1">
                        <div class="col-6">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input name="start_date" id="leave_start_date" type="text" autocomplete="off"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   placeholder="Pilih tanggal" value="{{ old('start_date') }}" required readonly>
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tanggal Akhir <span class="text-danger">*</span></label>
                            <input name="end_date" id="leave_end_date" type="text" autocomplete="off"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   placeholder="Pilih tanggal" value="{{ old('end_date') }}" required readonly>
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div id="leave_day_count" class="alert alert-info py-1 px-2 small mb-3" style="display:none">
                        <i class="bi bi-info-circle me-1"></i>
                        <span id="leave_day_count_text"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror"
                            rows="3" required minlength="10" placeholder="Jelaskan alasan cuti Anda...">{{ old('reason') }}</textarea>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Ajukan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
.flatpickr-day.flatpickr-disabled { background: #f1f5f9 !important; color: #cbd5e1 !important; text-decoration: line-through; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script>
const HOLIDAYS = @json($holidays);

function isDisabled(dateObj) {
    const dow = dateObj.getDay(); // 0=Sun, 6=Sat
    if (dow === 0 || dow === 6) return true;
    const str = dateObj.toISOString().slice(0, 10);
    return HOLIDAYS.includes(str);
}

function calcWorkingDays(startStr, endStr) {
    if (!startStr || !endStr) return 0;
    let start = new Date(startStr), end = new Date(endStr), count = 0;
    let cur = new Date(start);
    while (cur <= end) {
        if (!isDisabled(cur)) count++;
        cur.setDate(cur.getDate() + 1);
    }
    return count;
}

function updateDayCount() {
    const start = document.getElementById('leave_start_date').value;
    const end   = document.getElementById('leave_end_date').value;
    const box   = document.getElementById('leave_day_count');
    const txt   = document.getElementById('leave_day_count_text');
    if (start && end && end >= start) {
        const n = calcWorkingDays(start, end);
        box.style.display = '';
        if (n < 1) {
            box.className = 'alert alert-warning py-1 px-2 small mb-3';
            txt.textContent = 'Tidak ada hari kerja dalam rentang ini (semua akhir pekan/hari libur).';
        } else {
            box.className = 'alert alert-info py-1 px-2 small mb-3';
            txt.textContent = n === 1
                ? '1 hari kerja.'
                : n + ' hari kerja (Senin–Jumat, tidak termasuk hari libur).';
        }
    } else {
        box.style.display = 'none';
    }
}

const today = new Date().toISOString().slice(0, 10);

const fpStart = flatpickr('#leave_start_date', {
    locale: 'id',
    dateFormat: 'Y-m-d',
    minDate: today,
    disableMobile: true,
    disable: [isDisabled],
    onChange(sel, str) {
        fpEnd.set('minDate', str);
        // Auto-fill end date with start date when end is empty or is now before start
        if (!fpEnd.selectedDates[0] || fpEnd.selectedDates[0] < sel[0]) {
            fpEnd.setDate(str, true); // true = trigger onChange so day count updates
        } else {
            updateDayCount();
        }
    }
});

const fpEnd = flatpickr('#leave_end_date', {
    locale: 'id',
    dateFormat: 'Y-m-d',
    minDate: today,
    disableMobile: true,
    disable: [isDisabled],
    onChange() { updateDayCount(); }
});

// Re-open modal + trigger count if old() values exist (after validation error)
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('ajukanCutiModal')).show();
    updateDayCount();
});
@endif
</script>
@endpush
