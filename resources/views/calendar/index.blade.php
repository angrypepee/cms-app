@extends('layouts.app')

@section('page-title', 'Kalender & Hari Libur')

@push('styles')
<style>
    #calendar { max-width: 100%; }
    .fc-event { cursor: pointer; font-size: 0.78rem; }
    .legend-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Kalender & Hari Libur</h4>
        <p class="text-muted small mb-0">Kelola hari libur nasional & perusahaan</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Hari Libur
    </button>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    <option value="national" @selected(request('type')=='national')>Nasional</option>
                    <option value="company"  @selected(request('type')=='company')>Perusahaan</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-2 align-items-center ms-2 flex-wrap">
                <span class="legend-dot" style="background:#dc2626"></span><small class="text-muted me-2">Libur Nasional</small>
                <span class="legend-dot" style="background:#2563eb"></span><small class="text-muted me-2">Libur Perusahaan</small>
                <span class="legend-dot" style="background:#16a34a"></span><small class="text-muted">Karyawan Cuti</small>
            </div>
        </form>
    </div>
</div>

{{-- On-leave summary --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0"><i class="bi bi-person-dash me-2 text-success"></i>Sedang Cuti Hari Ini</h6>
                <span class="badge bg-success-subtle text-success">{{ $onLeaveToday->count() }}</span>
            </div>
            <div class="card-body p-0" style="max-height:220px;overflow-y:auto">
                @forelse($onLeaveToday as $l)
                    <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center fw-bold" style="width:38px;height:38px;font-size:.85rem">
                            {{ strtoupper(mb_substr($l->employee?->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $l->employee?->name ?? '—' }}</div>
                            <div class="text-muted" style="font-size:.72rem">
                                {{ $l->leaveType?->name ?? 'Cuti' }} · {{ $l->employee?->company?->name ?? '' }}
                            </div>
                        </div>
                        <div class="text-end small text-muted" style="font-size:.7rem">
                            {{ $l->start_date->isoFormat('D MMM') }} – {{ $l->end_date->isoFormat('D MMM') }}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4 small">
                        <i class="bi bi-people d-block mb-1" style="font-size:1.3rem;opacity:.4"></i>
                        Tidak ada karyawan yang cuti hari ini
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0"><i class="bi bi-calendar-week me-2 text-primary"></i>Cuti 7 Hari ke Depan</h6>
                <span class="badge bg-primary-subtle text-primary">{{ $upcomingLeave->count() }}</span>
            </div>
            <div class="card-body p-0" style="max-height:220px;overflow-y:auto">
                @forelse($upcomingLeave as $l)
                    <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                        <div class="text-center" style="min-width:42px">
                            <div class="fw-bold text-dark" style="font-size:1.05rem;line-height:1">{{ $l->start_date->format('d') }}</div>
                            <div class="text-muted text-uppercase" style="font-size:.65rem">{{ $l->start_date->isoFormat('MMM') }}</div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $l->employee?->name ?? '—' }}</div>
                            <div class="text-muted" style="font-size:.72rem">
                                {{ $l->leaveType?->name ?? 'Cuti' }} · {{ $l->days_count }} hari
                            </div>
                        </div>
                        <span class="badge bg-light text-dark" style="font-size:.65rem">
                            s/d {{ $l->end_date->isoFormat('D MMM') }}
                        </span>
                    </div>
                @empty
                    <div class="text-center text-muted py-4 small">
                        <i class="bi bi-calendar-x d-block mb-1" style="font-size:1.3rem;opacity:.4"></i>
                        Tidak ada cuti dijadwalkan minggu depan
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- FullCalendar --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    {{-- List --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">Daftar Hari Libur {{ $year }}</h6>
            </div>
            <div class="card-body p-0" style="max-height:550px;overflow-y:auto;">
                @forelse($holidays as $h)
                    <div class="d-flex align-items-start gap-3 p-3 border-bottom" id="holiday-row-{{ $h->id }}">
                        <div class="text-center" style="min-width:44px">
                            <div class="fw-bold text-dark" style="font-size:1.2rem;">{{ $h->date->format('d') }}</div>
                            <div class="text-muted small text-uppercase">{{ $h->date->format('M') }}</div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $h->name }}</div>
                            <span class="badge {{ $h->typeBadgeClass() }} badge-pill mt-1">{{ $h->typeLabel() }}</span>
                            @if($h->company)
                                <div class="text-muted small">{{ $h->company->name }}</div>
                            @endif
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary" onclick="editHoliday({{ $h->id }}, '{{ $h->name }}', '{{ $h->date->format('Y-m-d') }}', '{{ $h->type }}', {{ $h->company_id ?? 'null' }}, '{{ $h->description }}')" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('calendar.holidays.destroy', $h) }}" onsubmit="return confirm('Hapus hari libur ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                        Belum ada hari libur
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addHolidayModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('calendar.holidays.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Hari Libur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Hari Libur <span class="text-danger">*</span></label>
                        <input name="name" class="form-control" required maxlength="200" placeholder="Cth: Hari Raya Idul Fitri">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input name="date" type="date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" id="add-type-select" onchange="toggleCompanySelect('add-company-select', this.value)">
                            <option value="national">Nasional</option>
                            <option value="company">Perusahaan</option>
                        </select>
                    </div>
                    <div class="mb-3" id="add-company-select" style="display:none">
                        <label class="form-label">Perusahaan</label>
                        <select name="company_id" class="form-select">
                            <option value="">-- Pilih Perusahaan --</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editHolidayModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editHolidayForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Hari Libur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Hari Libur <span class="text-danger">*</span></label>
                        <input name="name" id="edit-name" class="form-control" required maxlength="200">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input name="date" id="edit-date" type="date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe <span class="text-danger">*</span></label>
                        <select name="type" id="edit-type" class="form-select" onchange="toggleCompanySelect('edit-company-select', this.value)">
                            <option value="national">Nasional</option>
                            <option value="company">Perusahaan</option>
                        </select>
                    </div>
                    <div class="mb-3" id="edit-company-select" style="display:none">
                        <label class="form-label">Perusahaan</label>
                        <select name="company_id" id="edit-company-id" class="form-select">
                            <option value="">-- Pilih Perusahaan --</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="description" id="edit-description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/id.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var events = @json($events);
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: '{{ $year }}-01-01',
        locale: 'id',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth,listYear' },
        events: events,
        height: 600,
        dayMaxEventRows: 3,
        eventDidMount: function(info) {
            var p = info.event.extendedProps || {};
            var lines = [];
            if (p.kind === 'leave') {
                lines.push('Karyawan: ' + (p.employee || '-'));
                if (p.company)    lines.push('Perusahaan: ' + p.company);
                if (p.leave_type) lines.push('Jenis: ' + p.leave_type);
                if (p.description) lines.push('Alasan: ' + p.description);
            } else {
                lines.push((p.type === 'national' ? 'Libur Nasional' : 'Libur Perusahaan'));
                if (p.description) lines.push(p.description);
            }
            info.el.setAttribute('title', info.event.title + '\n' + lines.join('\n'));
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            var p = info.event.extendedProps || {};
            if (p.kind === 'leave') {
                alert(info.event.title + '\n' +
                    'Perusahaan: ' + (p.company || '-') + '\n' +
                    (p.description ? 'Alasan: ' + p.description : ''));
            }
        }
    });
    calendar.render();
});

function toggleCompanySelect(id, val) {
    document.getElementById(id).style.display = val === 'company' ? '' : 'none';
}

function editHoliday(id, name, date, type, companyId, description) {
    const base = '{{ url("calendar/holidays") }}';
    document.getElementById('editHolidayForm').action = base + '/' + id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-date').value = date;
    document.getElementById('edit-type').value = type;
    document.getElementById('edit-description').value = description || '';
    toggleCompanySelect('edit-company-select', type);
    const sel = document.getElementById('edit-company-id');
    if (sel && companyId) sel.value = companyId;
    new bootstrap.Modal(document.getElementById('editHolidayModal')).show();
}
</script>
@endpush
