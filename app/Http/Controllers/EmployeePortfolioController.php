<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeePortfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeePortfolioController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $entryType = $request->input('entry_type', 'file');

        $request->validate([
            'label' => 'required|string|max:255',
            'file'  => $entryType === 'file' ? 'required|file|max:20480|mimes:pdf,jpg,jpeg,png,webp,doc,docx,zip' : 'nullable',
            'url'   => $entryType === 'link' ? 'required|url|max:500' : 'nullable|url|max:500',
        ]);

        $data = [
            'uploaded_by' => auth()->id(),
            'label'       => $request->label,
        ];

        if ($entryType === 'link') {
            $data['url'] = $request->url;
        } else {
            $file = $request->file('file');
            $path = $file->store('employee-portfolios/' . $employee->id, 'private');
            $data['file_path']    = $path;
            $data['original_name'] = $file->getClientOriginalName();
            $data['mime_type']    = $file->getMimeType();
            $data['file_size']    = $file->getSize();
        }

        $employee->portfolios()->create($data);

        return back()->with('success', 'Portfolio "' . $request->label . '" berhasil ditambahkan.');
    }

    public function show(Request $request, Employee $employee, EmployeePortfolio $portfolio)
    {
        abort_if($portfolio->employee_id !== $employee->id, 404);

        $absolutePath = Storage::disk('private')->path($portfolio->file_path);
        abort_unless(file_exists($absolutePath), 404);

        $headers = ['Content-Type' => $portfolio->mime_type ?? 'application/octet-stream'];

        if (!$request->boolean('download') && $portfolio->isViewable()) {
            return response()->file($absolutePath, $headers);
        }

        return response()->download($absolutePath, $portfolio->original_name, $headers);
    }

    public function destroy(Employee $employee, EmployeePortfolio $portfolio)
    {
        abort_if($portfolio->employee_id !== $employee->id, 404);

        Storage::disk('private')->delete($portfolio->file_path);
        $portfolio->delete();

        return back()->with('success', 'Portfolio berhasil dihapus.');
    }
}
