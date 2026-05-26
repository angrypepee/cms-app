@extends('layouts.app')

@section('page-title', 'Kalender Saya')

@push('styles')
<style>
    .fc-event { cursor: default; font-size: 0.78rem; }
    .legend-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
</style>
@endpush

@section('content')
<div class="mb-4">
    <h4 class="mb-0 fw-bold">Kalender Saya</h4>
    <p class="text-muted small mb-0">Hari libur, cuti, dan jadwal {{ $employee->company->name ?? '' }}</p>
</div>

<div class="d-flex gap-3 mb-3 flex-wrap align-items-center">
    <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#dc2626"></span><small>Libur Nasional</small></div>
    <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#7c3aed"></span><small>Libur Perusahaan</small></div>
    <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#15803d"></span><small>Cuti Disetujui</small></div>
    <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#d97706"></span><small>Cuti Menunggu</small></div>
    <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#9ca3af"></span><small>Cuti Ditolak</small></div>
    <div class="ms-auto">
        <a href="{{ route('my.leaves') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-calendar-plus me-1"></i> Ajukan Cuti
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/id.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: '{{ $year }}-{{ str_pad($month, 2, "0", STR_PAD_LEFT) }}-01',
        locale: 'id',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
        events: @json($events),
        height: 600,
        eventClick: function(info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        }
    });
    calendar.render();
});
</script>
@endpush
