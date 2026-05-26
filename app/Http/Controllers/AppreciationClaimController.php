<?php

namespace App\Http\Controllers;

use App\Models\AppreciationBudget;
use App\Models\AppreciationClaim;
use App\Models\AppreciationClaimDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppreciationClaimController extends Controller
{
    public function create(AppreciationBudget $appreciation)
    {
        $appreciation->load('employee', 'company');
        return view('appreciation.claims.create', compact('appreciation'));
    }

    public function store(Request $request, AppreciationBudget $appreciation)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'amount'      => 'required|numeric|min:1',
            'documents'   => 'nullable|array',
            'documents.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx',
            'doc_labels'  => 'nullable|array',
            'doc_labels.*'=> 'nullable|string|max:255',
        ]);

        // Check remaining budget
        $remaining = $appreciation->remainingAmount();
        if ($validated['amount'] > $remaining) {
            return back()->withInput()->withErrors([
                'amount' => 'Jumlah melebihi sisa anggaran (Rp ' . number_format($remaining, 0, ',', '.') . ').',
            ]);
        }

        $claim = $appreciation->claims()->create([
            'submitted_by' => auth()->id(),
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'amount'       => $validated['amount'],
            'status'       => 'pending',
        ]);

        // Attach documents
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $idx => $file) {
                $label = $request->input("doc_labels.$idx") ?: $file->getClientOriginalName();
                $path  = $file->store('appreciation-claims/' . $claim->id, 'private');

                $claim->documents()->create([
                    'uploaded_by'   => auth()->id(),
                    'label'         => $label,
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getMimeType(),
                    'file_size'     => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('appreciation.show', $appreciation)
            ->with('success', 'Permohonan "' . $claim->title . '" berhasil diajukan.');
    }

    public function show(AppreciationBudget $appreciation, AppreciationClaim $claim)
    {
        abort_if($claim->appreciation_budget_id !== $appreciation->id, 404);
        $claim->load('submitter', 'reviewer', 'documents.uploader', 'budget.employee', 'budget.company');
        return view('appreciation.claims.show', compact('appreciation', 'claim'));
    }

    public function approve(Request $request, AppreciationBudget $appreciation, AppreciationClaim $claim)
    {
        abort_if($claim->appreciation_budget_id !== $appreciation->id, 404);
        abort_unless($claim->isPending(), 422);

        $request->validate([
            'transfer_proof' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,webp',
            'payment_date'   => 'nullable|date',
        ]);

        $updateData = [
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'payment_date' => $request->input('payment_date') ?: null,
        ];

        if ($request->hasFile('transfer_proof')) {
            // Remove old proof if replacing
            if ($claim->transfer_proof_path) {
                Storage::disk('private')->delete($claim->transfer_proof_path);
            }
            $updateData['transfer_proof_path'] = $request->file('transfer_proof')
                ->store('transfer-proofs/' . $claim->id, 'private');
        }

        $claim->update($updateData);

        return back()->with('success', 'Permohonan disetujui.');
    }

    public function showTransferProof(Request $request, AppreciationBudget $appreciation, AppreciationClaim $claim)
    {
        abort_if($claim->appreciation_budget_id !== $appreciation->id, 404);
        abort_unless($claim->hasTransferProof(), 404);

        $absolutePath = Storage::disk('private')->path($claim->transfer_proof_path);
        abort_unless(file_exists($absolutePath), 404);

        $ext      = pathinfo($claim->transfer_proof_path, PATHINFO_EXTENSION);
        $mimeMap  = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                     'png' => 'image/png', 'webp' => 'image/webp'];
        $mime     = $mimeMap[strtolower($ext)] ?? 'application/octet-stream';
        $filename = 'bukti-transfer-' . $claim->id . '.' . $ext;

        if ($request->boolean('download')) {
            return response()->download($absolutePath, $filename);
        }
        return response()->file($absolutePath, ['Content-Type' => $mime]);
    }

    public function reject(Request $request, AppreciationBudget $appreciation, AppreciationClaim $claim)
    {
        abort_if($claim->appreciation_budget_id !== $appreciation->id, 404);
        abort_unless($claim->isPending(), 422);

        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $claim->update([
            'status'           => 'rejected',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Permohonan ditolak.');
    }

    public function addDocument(Request $request, AppreciationBudget $appreciation, AppreciationClaim $claim)
    {
        abort_if($claim->appreciation_budget_id !== $appreciation->id, 404);

        $request->validate([
            'label' => 'required|string|max:255',
            'file'  => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx',
        ]);

        $file = $request->file('file');
        $path = $file->store('appreciation-claims/' . $claim->id, 'private');

        $claim->documents()->create([
            'uploaded_by'   => auth()->id(),
            'label'         => $request->label,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
        ]);

        return back()->with('success', 'Bukti dokumen berhasil ditambahkan.');
    }

    public function showDocument(AppreciationBudget $appreciation, AppreciationClaim $claim,
                                 AppreciationClaimDocument $document, Request $request)
    {
        abort_if($document->appreciation_claim_id !== $claim->id, 404);
        abort_if($claim->appreciation_budget_id   !== $appreciation->id, 404);

        $absolutePath = Storage::disk('private')->path($document->file_path);
        abort_unless(file_exists($absolutePath), 404);

        $headers       = ['Content-Type' => $document->mime_type ?? 'application/octet-stream'];
        $forceDownload = $request->boolean('download');

        if (!$forceDownload && $document->isViewable()) {
            return response()->file($absolutePath, $headers);
        }

        return response()->download($absolutePath, $document->original_name, $headers);
    }

    public function deleteDocument(AppreciationBudget $appreciation, AppreciationClaim $claim,
                                   AppreciationClaimDocument $document)
    {
        abort_if($document->appreciation_claim_id !== $claim->id, 404);
        abort_if($claim->appreciation_budget_id   !== $appreciation->id, 404);

        Storage::disk('private')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function destroy(AppreciationBudget $appreciation, AppreciationClaim $claim)
    {
        abort_if($claim->appreciation_budget_id !== $appreciation->id, 404);
        abort_unless($claim->isPending(), 422);

        foreach ($claim->documents as $doc) {
            Storage::disk('private')->delete($doc->file_path);
        }
        $claim->delete();

        return redirect()->route('appreciation.show', $appreciation)->with('success', 'Permohonan berhasil dihapus.');
    }
}
