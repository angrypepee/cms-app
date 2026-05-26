<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Company;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $query = Holiday::with('company')->whereYear('date', $year);
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        $holidays = $query->orderBy('date')->get();
        $companies = Company::orderBy('name')->get();
        $years = range(now()->year - 1, now()->year + 2);

        // Approved leave requests overlapping the selected year
        $yearStart = "$year-01-01";
        $yearEnd   = "$year-12-31";
        $leaves = LeaveRequest::with(['employee:id,name,employee_id,company_id', 'employee.company:id,name', 'leaveType:id,name'])
            ->where('status', 'approved')
            ->where('start_date', '<=', $yearEnd)
            ->where('end_date',   '>=', $yearStart)
            ->orderBy('start_date')
            ->get();

        // Holidays as events
        $holidayEvents = $holidays->map(fn($h) => [
            'id'    => 'h-' . $h->id,
            'title' => $h->name,
            'start' => $h->date->toDateString(),
            'color' => $h->type === 'national' ? '#dc2626' : '#2563eb',
            'extendedProps' => ['kind' => 'holiday', 'type' => $h->type, 'description' => $h->description],
        ]);

        // Leaves as events (FullCalendar end is exclusive — add 1 day)
        $leaveEvents = $leaves->map(function ($l) {
            $empName = $l->employee?->name ?? 'Karyawan';
            $type    = $l->leaveType?->name ?? 'Cuti';
            return [
                'id'    => 'l-' . $l->id,
                'title' => $empName . ' — ' . $type,
                'start' => $l->start_date->toDateString(),
                'end'   => $l->end_date->copy()->addDay()->toDateString(),
                'color' => '#16a34a',
                'extendedProps' => [
                    'kind'        => 'leave',
                    'employee'    => $empName,
                    'company'     => $l->employee?->company?->name,
                    'leave_type'  => $type,
                    'description' => $l->reason,
                ],
            ];
        });

        $events = $holidayEvents->concat($leaveEvents)->values();

        // Today & upcoming (next 7 days) on leave — for sidebar
        $today    = now()->toDateString();
        $weekAhead = now()->addDays(7)->toDateString();
        $onLeaveToday = $leaves->filter(fn($l) =>
            $l->start_date->toDateString() <= $today && $l->end_date->toDateString() >= $today
        )->values();
        $upcomingLeave = $leaves->filter(fn($l) =>
            $l->start_date->toDateString() > $today && $l->start_date->toDateString() <= $weekAhead
        )->values();

        return view('calendar.index', compact(
            'holidays', 'companies', 'years', 'year', 'events',
            'onLeaveToday', 'upcomingLeave'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'date'        => 'required|date',
            'type'        => 'required|in:national,company',
            'description' => 'nullable|string|max:500',
            'company_id'  => 'nullable|exists:companies,id',
            'is_active'   => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        Holiday::create($validated);

        return redirect()->route('calendar.index')
            ->with('success', "Hari libur \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'date'        => 'required|date',
            'type'        => 'required|in:national,company',
            'description' => 'nullable|string|max:500',
            'company_id'  => 'nullable|exists:companies,id',
            'is_active'   => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $holiday->update($validated);

        return redirect()->route('calendar.index')
            ->with('success', "Hari libur \"{$holiday->name}\" berhasil diperbarui.");
    }

    public function destroy(Holiday $holiday)
    {
        $name = $holiday->name;
        $holiday->delete();
        return redirect()->route('calendar.index')
            ->with('success', "Hari libur \"{$name}\" berhasil dihapus.");
    }
}
