<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectWorkHistory;
use App\Services\RepoStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectPlanController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim((string) $request->get('q', ''));
        $status = $request->get('status');

        $projects = Project::with(['client', 'company', 'employees.company'])
            ->when($q !== '', fn($query) => $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")->orWhere('code', 'like', "%$q%");
            }))
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderByRaw("FIELD(status,'active','planning','on_hold','completed','cancelled')")
            ->orderBy('start_date')
            ->get();

        $statuses = ['planning', 'active', 'on_hold', 'completed', 'cancelled'];

        return view('project-plan.index', compact('projects', 'q', 'status', 'statuses'));
    }

    public function show(Project $project)
    {
        $project->load([
            'client', 'company', 'employees.company', 'quotations', 'invoices',
            'links', 'files',
            'workHistories.employee', 'workHistories.logger',
        ]);

        $employees = Employee::with('company')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Fetch repo contributor stats for GitHub/GitLab links
        $repoStats = [];
        // Fetch commits grouped by author per repo link
        $repoCommits = [];

        foreach ($project->links->whereIn('type', ['github', 'gitlab']) as $link) {
            $result = RepoStatsService::contributors($link->url);
            $contributors = $result['contributors'] ?? [];
            $matched = !empty($contributors)
                ? RepoStatsService::matchContributorsToEmployees($contributors, $project->employees)
                : [];
            $repoStats[] = [
                'link'         => $link,
                'contributors' => $contributors,
                'matched'      => $matched,
                'error'        => $result['error'] ?? null,
                'parsed'       => $result['parsed'] ?? null,
            ];

            // Fetch commits per author (admin-only detail)
            $commitsResult = RepoStatsService::allCommitsByAuthor($link->url, 200);
            $repoCommits[] = [
                'link'     => $link,
                'byAuthor' => $commitsResult['byAuthor'] ?? [],
                'total'    => $commitsResult['total'] ?? 0,
                'error'    => $commitsResult['error'] ?? null,
                'parsed'   => $commitsResult['parsed'] ?? null,
                'matched'  => RepoStatsService::matchContributorsToEmployees(
                    collect($commitsResult['byAuthor'] ?? [])->keys()->map(fn($name) => ['login' => $name, 'type' => $link->type, 'contributions' => 0])->toArray(),
                    $project->employees
                ),
            ];
        }

        return view('project-plan.show', compact('project', 'employees', 'repoStats', 'repoCommits'));
    }

    public function addMember(Request $request, Project $project)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'role'        => 'nullable|string|max:100',
            'notes'       => 'nullable|string|max:500',
            'joined_at'   => 'nullable|date',
        ]);

        if (empty($validated['joined_at']) && $project->start_date) {
            $validated['joined_at'] = $project->start_date->toDateString();
        }

        if (empty($validated['role'])) {
            $employee = Employee::findOrFail($validated['employee_id']);
            $validated['role'] = $employee->position ?? null;
        }

        $project->members()->updateOrCreate(
            ['employee_id' => $validated['employee_id']],
            [
                'role'      => $validated['role'] ?? null,
                'notes'     => $validated['notes'] ?? null,
                'joined_at' => $validated['joined_at'] ?? null,
            ]
        );

        // Log initial status
        ProjectWorkHistory::create([
            'project_id'  => $project->id,
            'employee_id' => $validated['employee_id'],
            'logged_by'   => Auth::id(),
            'from_status' => null,
            'to_status'   => 'not_started',
            'note'        => 'Ditambahkan ke tim project.',
        ]);

        return back()->with('success', 'Anggota tim berhasil ditambahkan.');
    }

    public function removeMember(Project $project, Employee $employee)
    {
        $project->members()->where('employee_id', $employee->id)->delete();

        ProjectWorkHistory::create([
            'project_id'  => $project->id,
            'employee_id' => $employee->id,
            'logged_by'   => Auth::id(),
            'from_status' => null,
            'to_status'   => 'not_started',
            'note'        => 'Dihapus dari tim project.',
        ]);

        return back()->with('success', 'Anggota tim berhasil dihapus dari project.');
    }

    public function updateMember(Request $request, Project $project, Employee $employee)
    {
        $validated = $request->validate([
            'role'      => 'nullable|string|max:100',
            'notes'     => 'nullable|string|max:500',
            'joined_at' => 'nullable|date',
        ]);

        $project->members()->where('employee_id', $employee->id)->update($validated);

        return back()->with('success', 'Data anggota tim diperbarui.');
    }
}
