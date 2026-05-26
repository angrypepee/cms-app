<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PublicDocumentController extends Controller
{
    public function quotation(string $token)
    {
        $quotation = Quotation::where('share_token', $token)
            ->with(['client','company','project','items'])
            ->firstOrFail();

        if (!$quotation->viewed_at) {
            $quotation->forceFill(['viewed_at' => now()])->saveQuietly();
        }

        return view('b2b.public_quotation', compact('quotation'));
    }

    public function quotationPdf(string $token)
    {
        $quotation = Quotation::where('share_token', $token)
            ->with(['client','company','project','items'])
            ->firstOrFail();

        $pdf = Pdf::loadView('b2b.pdf_quotation', compact('quotation'))->setPaper('a4');
        return $pdf->download("{$quotation->quotation_number}.pdf");
    }

    public function invoice(string $token)
    {
        $invoice = Invoice::where('share_token', $token)
            ->with(['client','company','project','items','bankAccount','payments'])
            ->firstOrFail();

        if (!$invoice->viewed_at) {
            $invoice->forceFill(['viewed_at' => now()])->saveQuietly();
        }

        return view('b2b.public_invoice', compact('invoice'));
    }

    public function invoicePdf(string $token)
    {
        $invoice = Invoice::where('share_token', $token)
            ->with(['client','company','project','items','bankAccount','payments'])
            ->firstOrFail();

        $pdf = Pdf::loadView('b2b.pdf_invoice', compact('invoice'))->setPaper('a4');
        return $pdf->download("{$invoice->invoice_number}.pdf");
    }
}
