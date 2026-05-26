<?php

namespace App\Http\Controllers;

use App\Models\InternalRequest;
use App\Notifications\InternalRequestRespondedNotification;
use Illuminate\Http\Request;

class InternalRequestController extends Controller
{
    private function authorizeStaff(): void
    {
        abort_unless(auth()->check() && auth()->user()->isStaff(), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeStaff();

        $query = InternalRequest::with(['employee.company', 'responder'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'like', '%' . $request->search . '%')
                  ->orWhereHas('employee', fn($e) => $e->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $requests = $query->paginate(20)->withQueryString();
        $types    = ['permohonan', 'pendanaan', 'surat_keterangan', 'pengaduan', 'lainnya'];

        return view('internal-requests.index', compact('requests', 'types'));
    }

    public function show(InternalRequest $internalRequest)
    {
        $this->authorizeStaff();
        $internalRequest->load(['employee.company', 'responder']);
        return view('internal-requests.show', compact('internalRequest'));
    }

    public function respond(Request $request, InternalRequest $internalRequest)
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'admin_response' => 'required|string|max:2000',
            'status'         => 'required|in:diproses,selesai,ditolak',
        ]);

        $internalRequest->update([
            'admin_response' => $validated['admin_response'],
            'status'         => $validated['status'],
            'responded_by'   => auth()->id(),
            'responded_at'   => now(),
        ]);

        // Notify employee
        $internalRequest->load('employee.user');
        optional($internalRequest->employee->user)->notify(new InternalRequestRespondedNotification($internalRequest));

        return redirect()->route('internal-requests.show', $internalRequest)
            ->with('success', 'Balasan berhasil dikirim.');
    }
}
