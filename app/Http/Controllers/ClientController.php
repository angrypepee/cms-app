<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $clients = Client::with('company')
            ->when($q !== '', fn($qq) => $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                  ->orWhere('contact_person', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%");
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $companies = Company::orderBy('name')->get();
        return view('clients.index', compact('clients', 'companies', 'q'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Client::create($data);
        return back()->with('success', 'Klien berhasil ditambahkan.');
    }

    public function show(Client $client)
    {
        $client->load('company');
        $projects   = $client->projects()->latest()->limit(10)->get();
        $quotations = $client->quotations()->latest()->limit(10)->get();
        $invoices   = $client->invoices()->latest()->limit(10)->get();
        return view('clients.show', compact('client', 'projects', 'quotations', 'invoices'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $this->validateData($request);
        $client->update($data);
        return back()->with('success', 'Klien diperbarui.');
    }

    public function destroy(Client $client)
    {
        if ($client->projects()->exists() || $client->quotations()->exists() || $client->invoices()->exists()) {
            return back()->with('error', 'Klien tidak dapat dihapus karena memiliki project / quotation / invoice terkait.');
        }
        $client->delete();
        return back()->with('success', 'Klien dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'company_id'     => 'nullable|exists:companies,id',
            'name'           => 'required|string|max:200',
            'contact_person' => 'nullable|string|max:150',
            'email'          => 'nullable|email|max:150',
            'phone'          => 'nullable|string|max:50',
            'npwp'           => 'nullable|string|max:50',
            'address'        => 'nullable|string|max:1000',
            'notes'          => 'nullable|string|max:1000',
            'is_active'      => 'nullable|boolean',
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
