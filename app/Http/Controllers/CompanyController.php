<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount('employees')->latest()->paginate(12);
        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'tagline'         => 'nullable|string|max:255',
            'address'         => 'nullable|string|max:500',
            'phone'           => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'npwp'            => 'nullable|string|max:50',
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,svg|max:1024',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time'   => 'nullable|date_format:H:i|after:work_start_time',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        Company::create($validated);
        return redirect()->route('companies.index')->with('success', 'Perusahaan berhasil ditambahkan.');
    }

    public function show(Company $company)
    {
        $company->load('employees');
        return view('companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'tagline'         => 'nullable|string|max:255',
            'address'         => 'nullable|string|max:500',
            'phone'           => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'npwp'            => 'nullable|string|max:50',
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,svg|max:1024',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time'   => 'nullable|date_format:H:i|after:work_start_time',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $company->update($validated);
        return redirect()->route('companies.index')->with('success', 'Data perusahaan berhasil diperbarui.');
    }

    public function destroy(Company $company)
    {
        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }
        $company->delete();
        return redirect()->route('companies.index')->with('success', 'Perusahaan berhasil dihapus.');
    }
}
