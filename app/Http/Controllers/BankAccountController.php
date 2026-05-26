<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    public function index()
    {
        $accounts  = BankAccount::with('company')->orderBy('sort_order')->orderBy('bank_name')->get();
        $companies = Company::orderBy('name')->get();
        return view('bank-accounts.index', compact('accounts','companies'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        DB::transaction(function () use ($data) {
            if (!empty($data['is_default'])) {
                BankAccount::where('company_id', $data['company_id'] ?? null)->update(['is_default' => false]);
            }
            BankAccount::create($data);
        });
        return back()->with('success', 'Rekening bank ditambahkan.');
    }

    public function update(Request $request, BankAccount $bank_account)
    {
        $data = $this->validateData($request);
        DB::transaction(function () use ($data, $bank_account) {
            if (!empty($data['is_default'])) {
                BankAccount::where('company_id', $data['company_id'] ?? null)
                    ->where('id', '!=', $bank_account->id)
                    ->update(['is_default' => false]);
            }
            $bank_account->update($data);
        });
        return back()->with('success', 'Rekening bank diperbarui.');
    }

    public function destroy(BankAccount $bank_account)
    {
        $bank_account->delete();
        return back()->with('success', 'Rekening bank dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'company_id'     => 'nullable|exists:companies,id',
            'bank_name'      => 'required|string|max:100',
            'account_name'   => 'required|string|max:150',
            'account_number' => 'required|string|max:50',
            'branch'         => 'nullable|string|max:100',
            'swift_code'     => 'nullable|string|max:30',
            'is_default'     => 'nullable|boolean',
            'is_active'      => 'nullable|boolean',
            'sort_order'     => 'nullable|integer|min:0',
        ]);
    }
}
