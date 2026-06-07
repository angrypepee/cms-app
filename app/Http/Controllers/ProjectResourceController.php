<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectResourceController extends Controller
{
    // ── Links ────────────────────────────────────────────────

    public function storeLink(Request $request, Project $project)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:150',
            'url'   => 'required|url|max:500',
            'type'  => 'required|string|in:' . implode(',', array_keys(ProjectLink::typeOptions())),
        ]);

        $project->links()->create($validated);

        return back()->with('success', 'Tautan "' . $validated['label'] . '" berhasil ditambahkan.');
    }

    public function destroyLink(Project $project, ProjectLink $link)
    {
        abort_if($link->project_id !== $project->id, 404);
        $link->delete();

        return back()->with('success', 'Tautan berhasil dihapus.');
    }

    // ── Files ────────────────────────────────────────────────

    public function storeFile(Request $request, Project $project)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'file'  => 'required|file|max:20480|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xlsx,xls,zip,rar,pptx,ppt',
        ]);

        $file = $request->file('file');
        $path = $file->store('project-files/' . $project->id, 'private');

        $project->files()->create([
            'uploaded_by'   => auth()->id(),
            'label'         => $request->label,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
        ]);

        return back()->with('success', 'File "' . $request->label . '" berhasil diunggah.');
    }

    public function showFile(Request $request, Project $project, ProjectFile $file)
    {
        abort_if($file->project_id !== $project->id, 404);

        $absolutePath = Storage::disk('private')->path($file->file_path);
        abort_unless(file_exists($absolutePath), 404);

        $headers = ['Content-Type' => $file->mime_type ?? 'application/octet-stream'];

        if (!$request->boolean('download') && $file->isViewable()) {
            return response()->file($absolutePath, $headers);
        }

        return response()->download($absolutePath, $file->original_name, $headers);
    }

    public function destroyFile(Project $project, ProjectFile $file)
    {
        abort_if($file->project_id !== $project->id, 404);

        Storage::disk('private')->delete($file->file_path);
        $file->delete();

        return back()->with('success', 'File berhasil dihapus.');
    }
}
