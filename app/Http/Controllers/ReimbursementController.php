<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Reimbursement;
use App\Models\ReimbursementDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReimbursementController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Reimbursement::with(['employee', 'approver', 'submitter'])
            ->orderByDesc('created_at');

        // Non-admin staff only see reimbursements they are tagged as approver
        if (!$user->isAdmin() && $user->role?->value !== 'hr') {
            $query->where('approver_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $reimbursements = $query->paginate(20)->withQueryString();
        $employees      = Employee::orderBy('name')->get(['id', 'name', 'employee_id']);

        return view('reimbursements.index', compact('reimbursements', 'employees'));
    }

    public function show(Reimbursement $reimbursement)
    {
        $user = auth()->user();
        // Only admin/HR or the tagged approver can view
        if (!$user->isAdmin() && $user->role?->value !== 'hr') {
            abort_unless($reimbursement->approver_id === $user->id, 403);
        }

        $reimbursement->load('employee.company', 'submitter', 'approver', 'reviewer', 'documents.uploader');

        return view('reimbursements.show', compact('reimbursement'));
    }

    public function approve(Request $request, Reimbursement $reimbursement)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role?->value !== 'hr') {
            abort_unless($reimbursement->approver_id === $user->id, 403);
        }
        abort_unless($reimbursement->isPending(), 422, 'Hanya permohonan pending yang dapat disetujui.');

        $validated = $request->validate([
            'transfer_proof' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,webp',
            'payment_date'   => 'nullable|date',
        ]);

        $data = [
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'payment_date' => $validated['payment_date'] ?? null,
        ];

        if ($request->hasFile('transfer_proof')) {
            if ($reimbursement->transfer_proof_path) {
                Storage::disk('private')->delete($reimbursement->transfer_proof_path);
            }
            $data['transfer_proof_path'] = $request->file('transfer_proof')
                ->store('reimbursement-proofs/' . $reimbursement->id, 'private');
        }

        $reimbursement->update($data);

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', 'Permohonan reimbursement disetujui.');
    }

    public function reject(Request $request, Reimbursement $reimbursement)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role?->value !== 'hr') {
            abort_unless($reimbursement->approver_id === $user->id, 403);
        }
        abort_unless($reimbursement->isPending(), 422, 'Hanya permohonan pending yang dapat ditolak.');

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        $reimbursement->update([
            'status'           => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', 'Permohonan reimbursement ditolak.');
    }

    public function destroy(Reimbursement $reimbursement)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $reimbursement->documents()->each(function ($doc) {
            Storage::disk('private')->delete($doc->file_path);
            $doc->delete();
        });
        if ($reimbursement->transfer_proof_path) {
            Storage::disk('private')->delete($reimbursement->transfer_proof_path);
        }
        $reimbursement->delete();

        return redirect()->route('reimbursements.index')
            ->with('success', 'Reimbursement berhasil dihapus.');
    }

    public function showTransferProof(Request $request, Reimbursement $reimbursement)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role?->value !== 'hr') {
            abort_unless($reimbursement->approver_id === $user->id, 403);
        }
        abort_unless($reimbursement->hasTransferProof(), 404);

        $absolutePath = Storage::disk('private')->path($reimbursement->transfer_proof_path);
        abort_unless(file_exists($absolutePath), 404);

        $ext     = pathinfo($reimbursement->transfer_proof_path, PATHINFO_EXTENSION);
        $mimeMap = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png', 'webp' => 'image/webp'];
        $mime    = $mimeMap[strtolower($ext)] ?? 'application/octet-stream';

        if ($request->boolean('download')) {
            return response()->download($absolutePath, 'bukti-transfer-reimb-' . $reimbursement->id . '.' . $ext);
        }
        return response()->file($absolutePath, ['Content-Type' => $mime]);
    }

    public function showDocument(Request $request, Reimbursement $reimbursement, ReimbursementDocument $document)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role?->value !== 'hr') {
            abort_unless($reimbursement->approver_id === $user->id, 403);
        }
        abort_if($document->reimbursement_id !== $reimbursement->id, 404);

        $absolutePath = Storage::disk('private')->path($document->file_path);
        abort_unless(file_exists($absolutePath), 404);

        $headers = ['Content-Type' => $document->mime_type ?? 'application/octet-stream'];

        if ($request->boolean('download') || !$document->isViewable()) {
            return response()->download($absolutePath, $document->original_name, $headers);
        }
        return response()->file($absolutePath, $headers);
    }
}
