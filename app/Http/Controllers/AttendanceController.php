<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /** POST /my/attendance/check-in */
    public function checkIn(Request $request)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        $today = now()->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            ['check_in' => null, 'check_out' => null]
        );

        if ($attendance->check_in) {
            return back()->with('info', 'Anda sudah absen masuk hari ini.');
        }

        $attendance->update(['check_in' => now()->toTimeString()]);

        return back()->with('success', 'Absen masuk berhasil! ' . now()->format('H:i'));
    }

    /** POST /my/attendance/check-out */
    public function checkOut(Request $request)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        $today = now()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return back()->with('error', 'Anda belum absen masuk hari ini.');
        }

        if ($attendance->check_out) {
            return back()->with('info', 'Anda sudah absen keluar hari ini.');
        }

        $attendance->update(['check_out' => now()->toTimeString()]);

        return back()->with('success', 'Absen keluar berhasil! ' . now()->format('H:i'));
    }

    /** GET /attendance/today.json — admin/HR only, returns today's list as JSON */
    public function todayJson()
    {
        $today = now()->toDateString();

        $rows = Attendance::with(['employee.company'])
            ->where('date', $today)
            ->orderBy('check_in')
            ->get()
            ->map(fn($a) => [
                'id'            => $a->id,
                'employee_name' => $a->employee->name,
                'company'       => $a->employee->company->name ?? '-',
                'work_start'    => $a->employee->company?->work_start_time
                                   ? substr($a->employee->company->work_start_time, 0, 5) : null,
                'work_end'      => $a->employee->company?->work_end_time
                                   ? substr($a->employee->company->work_end_time, 0, 5) : null,
                'check_in'      => $a->check_in  ? substr($a->check_in,  0, 5) : null,
                'check_out'     => $a->check_out ? substr($a->check_out, 0, 5) : null,
                'duration'      => $a->durationLabel(),
                'is_active'     => $a->isActive(),
                'late_minutes'  => ($a->check_in && $a->employee->company?->work_start_time)
                    ? max(0, \Carbon\Carbon::parse($a->check_in)->diffInMinutes(
                        \Carbon\Carbon::parse($a->employee->company->work_start_time),
                        false
                      ) * -1)
                    : 0,
            ]);

        $totalActive = Employee::where('is_active', true)->count();

        return response()->json([
            'date'         => now()->isoFormat('dddd, D MMM YYYY'),
            'present'      => $rows->count(),
            'total_active' => $totalActive,
            'last_updated' => now()->format('H:i:s'),
            'rows'         => $rows,
        ]);
    }
}
