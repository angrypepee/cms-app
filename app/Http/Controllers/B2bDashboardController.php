<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Project;
use App\Models\Quotation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class B2bDashboardController extends Controller
{
    public function index()
    {
        // Auto-mark overdue once on view
        Invoice::whereIn('status', ['sent','partial'])
            ->whereDate('due_date', '<', now())
            ->whereColumn('paid_amount', '<', 'total')
            ->update(['status' => 'overdue']);

        // ── Summary cards ──
        $summary = [
            'clients_active'   => Client::where('is_active', true)->count(),
            'projects_active'  => Project::whereIn('status', ['planning','active'])->count(),
            'quotations_open'  => Quotation::whereIn('status', ['draft','sent'])->count(),
            'invoices_open'    => Invoice::whereIn('status', ['sent','partial','overdue'])->count(),

            'revenue_ytd'      => (float) Invoice::whereYear('issue_date', now()->year)
                                    ->where('status', 'paid')->sum('total'),
            'revenue_mtd'      => (float) Invoice::whereYear('issue_date', now()->year)
                                    ->whereMonth('issue_date', now()->month)
                                    ->where('status', 'paid')->sum('total'),
            'outstanding'      => (float) Invoice::whereIn('status',['sent','partial','overdue'])
                                    ->sum(DB::raw('total - paid_amount')),
            'overdue_amount'   => (float) Invoice::where('status','overdue')
                                    ->sum(DB::raw('total - paid_amount')),
            'overdue_count'    => Invoice::where('status', 'overdue')->count(),
            'quotation_pipeline'=> (float) Quotation::whereIn('status',['draft','sent'])->sum('total'),
        ];

        // ── Revenue last 6 months (paid invoices, by issue month) ──
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->startOfMonth()->subMonths($i);
            $months->push([
                'label' => $m->isoFormat('MMM YY'),
                'ym'    => $m->format('Y-m'),
                'paid'  => 0.0,
                'invoiced' => 0.0,
            ]);
        }

        $paidRows = Invoice::selectRaw("DATE_FORMAT(issue_date,'%Y-%m') ym, SUM(total) total")
            ->where('status','paid')
            ->where('issue_date', '>=', Carbon::now()->startOfMonth()->subMonths(5))
            ->groupBy('ym')->pluck('total', 'ym');

        $invRows = Invoice::selectRaw("DATE_FORMAT(issue_date,'%Y-%m') ym, SUM(total) total")
            ->whereNotIn('status', ['draft','cancelled'])
            ->where('issue_date', '>=', Carbon::now()->startOfMonth()->subMonths(5))
            ->groupBy('ym')->pluck('total', 'ym');

        $chart = $months->map(function ($m) use ($paidRows, $invRows) {
            $m['paid']     = (float) ($paidRows[$m['ym']]   ?? 0);
            $m['invoiced'] = (float) ($invRows[$m['ym']]    ?? 0);
            return $m;
        })->values();

        // ── Top 5 clients by revenue ──
        $topClients = Invoice::selectRaw('client_id, SUM(total) revenue, COUNT(*) inv_count')
            ->whereIn('status', ['paid','partial','overdue','sent'])
            ->groupBy('client_id')
            ->orderByDesc('revenue')
            ->with('client:id,name')
            ->limit(5)->get();

        // ── Aging buckets (open invoices) ──
        $today  = Carbon::today();
        $aging  = ['current'=>0,'1_30'=>0,'31_60'=>0,'61_90'=>0,'over_90'=>0];
        Invoice::whereIn('status',['sent','partial','overdue'])
            ->get(['due_date','total','paid_amount'])
            ->each(function ($inv) use (&$aging, $today) {
                $balance = (float)$inv->total - (float)$inv->paid_amount;
                if ($balance <= 0) return;
                if (!$inv->due_date || $inv->due_date->gte($today)) { $aging['current'] += $balance; return; }
                $days = $today->diffInDays($inv->due_date);
                if     ($days <= 30) $aging['1_30']   += $balance;
                elseif ($days <= 60) $aging['31_60']  += $balance;
                elseif ($days <= 90) $aging['61_90']  += $balance;
                else                 $aging['over_90'] += $balance;
            });

        // ── Recent invoices & payments ──
        $recentInvoices = Invoice::with('client')->latest()->limit(8)->get();
        $recentPayments = InvoicePayment::with('invoice.client')->latest('payment_date')->latest('id')->limit(8)->get();

        return view('b2b.dashboard', compact(
            'summary','chart','topClients','aging','recentInvoices','recentPayments'
        ));
    }
}
