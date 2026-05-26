<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $q      = trim((string) $request->get('q',''));

        $quotations = Quotation::with(['client','project'])
            ->when($status, fn($qq) => $qq->where('status',$status))
            ->when($q !== '', fn($qq) => $qq->where(function($w) use ($q) {
                $w->where('quotation_number','like',"%$q%")
                  ->orWhere('subject','like',"%$q%")
                  ->orWhereHas('client', fn($c) => $c->where('name','like',"%$q%"));
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('quotations.index', compact('quotations','status','q'));
    }

    public function create(Request $request)
    {
        $clients   = Client::where('is_active', true)->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        $projects  = Project::with('client')->orderBy('name')->get();
        $selectedClient  = $request->get('client_id');
        $selectedProject = $request->get('project_id');
        return view('quotations.create', compact('clients','companies','projects','selectedClient','selectedProject'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        return DB::transaction(function () use ($data) {
            $quotation = Quotation::create([
                'quotation_number' => Quotation::generateNumber(),
                'client_id'        => $data['client_id'],
                'project_id'       => $data['project_id'] ?? null,
                'company_id'       => $data['company_id'] ?? null,
                'issue_date'       => $data['issue_date'],
                'valid_until'      => $data['valid_until'] ?? null,
                'subject'          => $data['subject'] ?? null,
                'discount'         => $data['discount'] ?? 0,
                'tax_percent'      => $data['tax_percent'] ?? 0,
                'status'           => $data['status'] ?? 'draft',
                'notes'            => $data['notes'] ?? null,
                'terms'            => $data['terms'] ?? null,
                'created_by'       => auth()->id(),
            ]);

            $this->saveItems($quotation, $data['items']);
            $quotation->recalculate();

            return redirect()->route('quotations.show', $quotation)
                ->with('success', "Quotation {$quotation->quotation_number} dibuat.");
        });
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['client','project','company','items','creator','invoices']);
        return view('quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->with('error', 'Quotation yang sudah dikonversi tidak dapat diedit.');
        }
        $quotation->load('items');
        $clients   = Client::where('is_active', true)->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        $projects  = Project::with('client')->orderBy('name')->get();
        return view('quotations.edit', compact('quotation','clients','companies','projects'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($data, $quotation) {
            $quotation->update([
                'client_id'   => $data['client_id'],
                'project_id'  => $data['project_id'] ?? null,
                'company_id'  => $data['company_id'] ?? null,
                'issue_date'  => $data['issue_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'subject'     => $data['subject'] ?? null,
                'discount'    => $data['discount'] ?? 0,
                'tax_percent' => $data['tax_percent'] ?? 0,
                'status'      => $data['status'] ?? $quotation->status,
                'notes'       => $data['notes'] ?? null,
                'terms'       => $data['terms'] ?? null,
            ]);

            $quotation->items()->delete();
            $this->saveItems($quotation, $data['items']);
            $quotation->recalculate();
        });

        return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation diperbarui.');
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $request->validate(['status' => 'required|in:draft,sent,accepted,rejected,expired']);
        $quotation->update(['status' => $request->status]);
        return back()->with('success', "Status diubah ke {$quotation->statusBadge()[0]}.");
    }

    public function markSent(Quotation $quotation)
    {
        if (in_array($quotation->status, ['converted','rejected','expired'])) {
            return back()->with('error', 'Quotation tidak dapat ditandai terkirim.');
        }
        $quotation->update([
            'status'  => $quotation->status === 'draft' ? 'sent' : $quotation->status,
            'sent_at' => $quotation->sent_at ?? now(),
        ]);
        return back()->with('success', 'Quotation ditandai terkirim. Link share aktif.');
    }

    public function convertToInvoice(Quotation $quotation)
    {
        if (in_array($quotation->status, ['rejected','expired','cancelled'])) {
            return back()->with('error', 'Status quotation saat ini tidak dapat dikonversi.');
        }
        $existing = $quotation->invoices()->first();
        if ($existing) return redirect()->route('invoices.show', $existing);

        return DB::transaction(function () use ($quotation) {
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateNumber(),
                'client_id'      => $quotation->client_id,
                'project_id'     => $quotation->project_id,
                'quotation_id'   => $quotation->id,
                'company_id'     => $quotation->company_id,
                'issue_date'     => now()->toDateString(),
                'due_date'       => now()->addDays(30)->toDateString(),
                'subject'        => $quotation->subject,
                'discount'       => $quotation->discount,
                'tax_percent'    => $quotation->tax_percent,
                'status'         => 'draft',
                'notes'          => $quotation->notes,
                'terms'          => $quotation->terms,
                'created_by'     => auth()->id(),
            ]);

            foreach ($quotation->items as $i => $item) {
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit'        => $item->unit,
                    'unit_price'  => $item->unit_price,
                    'amount'      => $item->amount,
                    'sort_order'  => $item->sort_order ?? $i,
                ]);
            }

            $invoice->recalculate();
            $quotation->update(['status' => 'converted']);

            return redirect()->route('invoices.show', $invoice)
                ->with('success', "Quotation dikonversi menjadi invoice {$invoice->invoice_number}.");
        });
    }

    public function pdf(Quotation $quotation)
    {
        $quotation->load(['client','company','project','items']);
        $pdf = Pdf::loadView('b2b.pdf_quotation', compact('quotation'))->setPaper('a4');
        return $pdf->stream("{$quotation->quotation_number}.pdf");
    }

    public function destroy(Quotation $quotation)
    {
        if ($quotation->invoices()->exists()) {
            return back()->with('error', 'Quotation sudah dikonversi menjadi invoice.');
        }
        $quotation->delete();
        return redirect()->route('quotations.index')->with('success', 'Quotation dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'project_id'       => 'nullable|exists:projects,id',
            'company_id'       => 'nullable|exists:companies,id',
            'issue_date'       => 'required|date',
            'valid_until'      => 'nullable|date|after_or_equal:issue_date',
            'subject'          => 'nullable|string|max:200',
            'discount'         => 'nullable|numeric|min:0',
            'tax_percent'      => 'nullable|numeric|min:0|max:100',
            'status'           => 'nullable|in:draft,sent,accepted,rejected,expired,converted',
            'notes'            => 'nullable|string|max:2000',
            'terms'            => 'nullable|string|max:2000',
            'items'            => 'required|array|min:1',
            'items.*.description' => 'required|string|max:300',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit'        => 'nullable|string|max:30',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ], [], ['items' => 'item']);
    }

    private function saveItems($quotation, array $items): void
    {
        foreach (array_values($items) as $i => $item) {
            $amount = round(((float) $item['quantity']) * ((float) $item['unit_price']), 2);
            $quotation->items()->create([
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit'        => $item['unit'] ?? null,
                'unit_price'  => $item['unit_price'],
                'amount'      => $amount,
                'sort_order'  => $i,
            ]);
        }
    }
}
