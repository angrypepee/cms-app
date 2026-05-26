<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $q      = trim((string) $request->get('q',''));

        Invoice::whereIn('status', ['sent','partial'])
            ->whereDate('due_date', '<', now())
            ->whereColumn('paid_amount', '<', 'total')
            ->update(['status' => 'overdue']);

        $invoices = Invoice::with(['client','project'])
            ->when($status, fn($qq) => $qq->where('status',$status))
            ->when($q !== '', fn($qq) => $qq->where(function($w) use ($q) {
                $w->where('invoice_number','like',"%$q%")
                  ->orWhere('subject','like',"%$q%")
                  ->orWhereHas('client', fn($c) => $c->where('name','like',"%$q%"));
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total_outstanding' => Invoice::whereIn('status',['sent','partial','overdue'])->sum(DB::raw('total - paid_amount')),
            'total_paid'        => Invoice::where('status','paid')->sum('total'),
            'count_overdue'     => Invoice::where('status','overdue')->count(),
        ];

        return view('invoices.index', compact('invoices','status','q','summary'));
    }

    public function create(Request $request)
    {
        $clients   = Client::where('is_active', true)->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        $projects  = Project::with('client')->orderBy('name')->get();
        $banks     = BankAccount::where('is_active', true)->orderBy('sort_order')->get();
        $selectedClient  = $request->get('client_id');
        $selectedProject = $request->get('project_id');
        return view('invoices.create', compact('clients','companies','projects','banks','selectedClient','selectedProject'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        return DB::transaction(function () use ($data) {
            $invoice = Invoice::create([
                'invoice_number'   => Invoice::generateNumber(),
                'client_id'        => $data['client_id'],
                'project_id'       => $data['project_id'] ?? null,
                'company_id'       => $data['company_id'] ?? null,
                'bank_account_id'  => $data['bank_account_id'] ?? null,
                'issue_date'       => $data['issue_date'],
                'due_date'         => $data['due_date'] ?? null,
                'subject'          => $data['subject'] ?? null,
                'discount'         => $data['discount'] ?? 0,
                'tax_percent'      => $data['tax_percent'] ?? 0,
                'status'           => $data['status'] ?? 'draft',
                'notes'            => $data['notes'] ?? null,
                'terms'            => $data['terms'] ?? null,
                'created_by'       => auth()->id(),
            ]);

            $this->saveItems($invoice, $data['items']);
            $invoice->recalculate();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} dibuat.");
        });
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client','project','company','bankAccount','items','creator','quotation','payments.recorder','payments.bankAccount']);
        $banks = BankAccount::where('is_active', true)->orderBy('sort_order')->get();
        return view('invoices.show', compact('invoice','banks'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice yang sudah lunas tidak dapat diedit.');
        }
        $invoice->load('items');
        $clients   = Client::where('is_active', true)->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        $projects  = Project::with('client')->orderBy('name')->get();
        $banks     = BankAccount::where('is_active', true)->orderBy('sort_order')->get();
        return view('invoices.edit', compact('invoice','clients','companies','projects','banks'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($data, $invoice) {
            $invoice->update([
                'client_id'       => $data['client_id'],
                'project_id'      => $data['project_id'] ?? null,
                'company_id'      => $data['company_id'] ?? null,
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'issue_date'      => $data['issue_date'],
                'due_date'        => $data['due_date'] ?? null,
                'subject'         => $data['subject'] ?? null,
                'discount'        => $data['discount'] ?? 0,
                'tax_percent'     => $data['tax_percent'] ?? 0,
                'status'          => $data['status'] ?? $invoice->status,
                'notes'           => $data['notes'] ?? null,
                'terms'           => $data['terms'] ?? null,
            ]);

            $invoice->items()->delete();
            $this->saveItems($invoice, $data['items']);
            $invoice->recalculate();
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice diperbarui.');
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate(['status' => 'required|in:draft,sent,cancelled']);
        $invoice->update(['status' => $request->status]);
        return back()->with('success', "Status diubah ke {$invoice->statusBadge()[0]}.");
    }

    public function markSent(Invoice $invoice)
    {
        if (in_array($invoice->status, ['paid','cancelled'])) {
            return back()->with('error', 'Invoice tidak dapat ditandai terkirim.');
        }
        $invoice->update([
            'status'  => $invoice->status === 'draft' ? 'sent' : $invoice->status,
            'sent_at' => $invoice->sent_at ?? now(),
        ]);
        return back()->with('success', 'Invoice ditandai terkirim. Link share aktif.');
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount'          => 'required|numeric|min:0.01',
            'payment_date'    => 'required|date',
            'method'          => 'nullable|string|max:50',
            'reference'       => 'nullable|string|max:100',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'notes'           => 'nullable|string|max:500',
        ]);

        $currentPaid = (float) $invoice->payments()->sum('amount');
        $newPaid     = $currentPaid + (float) $data['amount'];
        if ($newPaid > (float) $invoice->total + 0.001) {
            return back()->with('error', 'Total pembayaran melebihi nilai invoice. Sisa: Rp ' . number_format($invoice->balance, 0, ',', '.'));
        }

        DB::transaction(function () use ($data, $invoice) {
            InvoicePayment::create([
                'invoice_id'      => $invoice->id,
                'payment_date'    => $data['payment_date'],
                'amount'          => $data['amount'],
                'method'          => $data['method']    ?? null,
                'reference'       => $data['reference'] ?? null,
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'notes'           => $data['notes']     ?? null,
                'recorded_by'     => auth()->id(),
            ]);
            $invoice->update([
                'payment_date'      => $data['payment_date'],
                'payment_method'    => $data['method']    ?? $invoice->payment_method,
                'payment_reference' => $data['reference'] ?? $invoice->payment_reference,
            ]);
            $invoice->refresh()->recalculate();
        });

        return back()->with('success', 'Pembayaran tercatat.');
    }

    public function deletePayment(Invoice $invoice, InvoicePayment $payment)
    {
        abort_unless($payment->invoice_id === $invoice->id, 404);
        DB::transaction(function () use ($payment, $invoice) {
            $payment->delete();
            $invoice->refresh()->recalculate();
        });
        return back()->with('success', 'Pembayaran dihapus.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['client','company','project','items','bankAccount','payments']);
        $pdf = Pdf::loadView('b2b.pdf_invoice', compact('invoice'))->setPaper('a4');
        return $pdf->stream("{$invoice->invoice_number}.pdf");
    }

    public function destroy(Invoice $invoice)
    {
        if (in_array($invoice->status, ['paid','partial']) || $invoice->payments()->exists()) {
            return back()->with('error', 'Invoice yang sudah memiliki pembayaran tidak dapat dihapus. Batalkan dahulu.');
        }
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'project_id'       => 'nullable|exists:projects,id',
            'company_id'       => 'nullable|exists:companies,id',
            'bank_account_id'  => 'nullable|exists:bank_accounts,id',
            'issue_date'       => 'required|date',
            'due_date'         => 'nullable|date|after_or_equal:issue_date',
            'subject'          => 'nullable|string|max:200',
            'discount'         => 'nullable|numeric|min:0',
            'tax_percent'      => 'nullable|numeric|min:0|max:100',
            'status'           => 'nullable|in:draft,sent,partial,paid,overdue,cancelled',
            'notes'            => 'nullable|string|max:2000',
            'terms'            => 'nullable|string|max:2000',
            'items'            => 'required|array|min:1',
            'items.*.description' => 'required|string|max:300',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit'        => 'nullable|string|max:30',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ], [], ['items' => 'item']);
    }

    private function saveItems($invoice, array $items): void
    {
        foreach (array_values($items) as $i => $item) {
            $amount = round(((float) $item['quantity']) * ((float) $item['unit_price']), 2);
            $invoice->items()->create([
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
