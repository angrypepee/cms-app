<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentController extends Controller
{
    /**
     * Store a newly uploaded document for an employee (file or link).
     */
    public function store(Request $request, Employee $employee)
    {
        $entryType = $request->input('entry_type', 'file'); // 'file' or 'link'

        $request->validate([
            'document_type' => 'required|string|in:' . implode(',', array_keys(EmployeeDocument::typeOptions())),
            'label'         => 'required|string|max:255',
            'file'          => $entryType === 'file' ? 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx' : 'nullable',
            'url'           => $entryType === 'link' ? 'required|url|max:500' : 'nullable|url|max:500',
        ]);

        $data = [
            'uploaded_by'   => auth()->id(),
            'document_type' => $request->document_type,
            'label'         => $request->label,
        ];

        if ($entryType === 'link') {
            $data['url'] = $request->url;
        } else {
            $file = $request->file('file');
            $data['file_path']    = $file->store('employee-documents/' . $employee->id, 'private');
            $data['original_name'] = $file->getClientOriginalName();
            $data['mime_type']    = $file->getMimeType();
            $data['file_size']    = $file->getSize();
        }

        $employee->documents()->create($data);

        return back()->with('success', 'Dokumen "' . $request->label . '" berhasil ditambahkan.');
    }

    /**
     * View or download a document.
     * Viewable types (PDF, images) are served inline; others are downloaded.
     * Pass ?download=1 to force attachment download regardless of type.
     */
    public function show(Request $request, Employee $employee, EmployeeDocument $document)
    {
        abort_if($document->employee_id !== $employee->id, 404);

        $absolutePath = Storage::disk('private')->path($document->file_path);
        abort_unless(file_exists($absolutePath), 404);

        $forceDownload = $request->boolean('download');
        $headers = ['Content-Type' => $document->mime_type ?? 'application/octet-stream'];

        if (!$forceDownload && $document->isViewable()) {
            return response()->file($absolutePath, $headers);
        }

        return response()->download($absolutePath, $document->original_name, $headers);
    }

    /**
     * Delete a document.
     */
    public function destroy(Employee $employee, EmployeeDocument $document)
    {
        abort_if($document->employee_id !== $employee->id, 404);

        Storage::disk('private')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
