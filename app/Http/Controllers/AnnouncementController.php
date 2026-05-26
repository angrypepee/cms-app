<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Company;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    private function authorizeStaff(): void
    {
        abort_unless(auth()->check() && auth()->user()->isStaff(), 403);
    }

    public function index()
    {
        $this->authorizeStaff();
        $announcements = Announcement::with(['author', 'company'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        $this->authorizeStaff();
        $companies = Company::orderBy('name')->get();
        return view('announcements.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'title'        => 'required|string|max:200',
            'content'      => 'required|string',
            'company_id'   => 'nullable|exists:companies,id',
            'is_pinned'    => 'boolean',
            'published_at' => 'nullable|date',
            'expires_at'   => 'nullable|date|after:published_at',
        ]);

        $validated['author_id']  = auth()->id();
        $validated['is_pinned']  = $request->boolean('is_pinned');
        $validated['published_at'] = $validated['published_at'] ?? now();

        Announcement::create($validated);

        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function show(Announcement $announcement)
    {
        $this->authorizeStaff();
        $announcement->load(['author', 'company']);
        return view('announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        $this->authorizeStaff();
        $companies = Company::orderBy('name')->get();
        return view('announcements.edit', compact('announcement', 'companies'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'title'        => 'required|string|max:200',
            'content'      => 'required|string',
            'company_id'   => 'nullable|exists:companies,id',
            'is_pinned'    => 'boolean',
            'published_at' => 'nullable|date',
            'expires_at'   => 'nullable|date',
        ]);

        $validated['is_pinned'] = $request->boolean('is_pinned');
        $announcement->update($validated);

        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorizeStaff();
        $announcement->delete();
        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman dihapus.');
    }
}
