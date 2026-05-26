<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $q       = trim((string) $request->get('q', ''));
        $status  = $request->get('status');
        $clientId = $request->get('client_id');

        $projects = Project::with(['client','company'])
            ->when($q !== '', fn($qq) => $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")->orWhere('code', 'like', "%$q%");
            }))
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($clientId, fn($qq) => $qq->where('client_id', $clientId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $clients   = Client::where('is_active', true)->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        return view('projects.index', compact('projects','clients','companies','q','status','clientId'));
    }

    public function create()
    {
        $clients   = Client::where('is_active', true)->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        return view('projects.create', compact('clients','companies'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['code'] = Project::generateCode();
        $project = Project::create($data);
        return redirect()->route('projects.show', $project)->with('success', "Project {$project->code} dibuat.");
    }

    public function show(Project $project)
    {
        $project->load(['client','company','quotations','invoices']);
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $clients   = Client::where('is_active', true)->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        return view('projects.edit', compact('project','clients','companies'));
    }

    public function update(Request $request, Project $project)
    {
        $project->update($this->validateData($request));
        return redirect()->route('projects.show', $project)->with('success', 'Project diperbarui.');
    }

    public function destroy(Project $project)
    {
        if ($project->invoices()->exists()) {
            return back()->with('error', 'Project tidak dapat dihapus karena memiliki invoice.');
        }
        $project->quotations()->update(['project_id' => null]);
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'company_id'  => 'nullable|exists:companies,id',
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'budget'      => 'nullable|numeric|min:0',
            'status'      => 'required|in:planning,active,on_hold,completed,cancelled',
            'notes'       => 'nullable|string|max:2000',
        ]);
    }
}
