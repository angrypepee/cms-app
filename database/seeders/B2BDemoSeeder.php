<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class B2BDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $admin   = User::where('role', 'admin')->first() ?? User::first();

        if (! $company) {
            $this->command?->warn('B2BDemoSeeder dilewati: tidak ada Company.');
            return;
        }

        // ── 1. Klien ──────────────────────────────────────────────
        $clientsData = [
            [
                'name' => 'PT Sinar Abadi Nusantara',
                'contact_person' => 'Andi Wijaya',
                'email' => 'finance@sinarabadi.co.id',
                'phone' => '021-7891234',
                'npwp'  => '02.345.678.9-012.000',
                'address' => 'Jl. MH Thamrin No. 45, Jakarta Pusat 10350',
            ],
            [
                'name' => 'CV Mitra Teknologi Cemerlang',
                'contact_person' => 'Rina Lestari',
                'email' => 'rina@mitrateknologi.com',
                'phone' => '022-5567890',
                'npwp'  => '03.456.789.0-023.000',
                'address' => 'Jl. Asia Afrika No. 78, Bandung 40112',
            ],
            [
                'name' => 'PT Bumi Hijau Lestari',
                'contact_person' => 'Bagus Pratama',
                'email' => 'po@bumihijau.id',
                'phone' => '031-3345678',
                'npwp'  => '04.567.890.1-034.000',
                'address' => 'Jl. Pemuda No. 12, Surabaya 60271',
            ],
            [
                'name' => 'PT Cahaya Logistik Indonesia',
                'contact_person' => 'Dewi Kartika',
                'email' => 'admin@cahayalogistik.co.id',
                'phone' => '021-8896677',
                'npwp'  => '05.678.901.2-045.000',
                'address' => 'Jl. Yos Sudarso No. 200, Jakarta Utara 14350',
            ],
            [
                'name' => 'PT Selaras Niaga Global',
                'contact_person' => 'Faisal Rahman',
                'email' => 'procurement@selarasniaga.com',
                'phone' => '024-7712345',
                'npwp'  => '06.789.012.3-056.000',
                'address' => 'Jl. Pandanaran No. 88, Semarang 50134',
                'is_active' => false,
            ],
        ];

        $clients = [];
        foreach ($clientsData as $data) {
            $clients[] = Client::create(array_merge([
                'company_id' => $company->id,
                'is_active'  => true,
                'notes'      => 'Klien demo untuk modul B2B.',
            ], $data));
        }
        [$c1, $c2, $c3, $c4, $c5] = $clients;

        // ── 2. Project ────────────────────────────────────────────
        $today = Carbon::today();

        $projects = [
            'p1' => Project::create([
                'code'        => 'PRJ-' . $today->format('Ym') . '-0001',
                'client_id'   => $c1->id,
                'company_id'  => $company->id,
                'name'        => 'Implementasi Sistem ERP',
                'description' => 'Pengembangan & implementasi modul Finance + HR.',
                'start_date'  => $today->copy()->subDays(45),
                'end_date'    => $today->copy()->addDays(60),
                'budget'      => 250_000_000,
                'status'      => 'active',
            ]),
            'p2' => Project::create([
                'code'        => 'PRJ-' . $today->format('Ym') . '-0002',
                'client_id'   => $c2->id,
                'company_id'  => $company->id,
                'name'        => 'Redesign Website Korporat',
                'description' => 'Redesign UI/UX + migrasi CMS.',
                'start_date'  => $today->copy()->subDays(20),
                'end_date'    => $today->copy()->addDays(40),
                'budget'      => 75_000_000,
                'status'      => 'active',
            ]),
            'p3' => Project::create([
                'code'        => 'PRJ-' . $today->format('Ym') . '-0003',
                'client_id'   => $c3->id,
                'company_id'  => $company->id,
                'name'        => 'Audit Keamanan Aplikasi',
                'description' => 'Penetration testing aplikasi internal.',
                'start_date'  => $today->copy()->subDays(90),
                'end_date'    => $today->copy()->subDays(30),
                'budget'      => 45_000_000,
                'status'      => 'completed',
            ]),
            'p4' => Project::create([
                'code'        => 'PRJ-' . $today->format('Ym') . '-0004',
                'client_id'   => $c4->id,
                'company_id'  => $company->id,
                'name'        => 'Pengadaan Perangkat Jaringan',
                'description' => 'Supply switch & router untuk 3 cabang.',
                'start_date'  => $today->copy()->addDays(10),
                'budget'      => 120_000_000,
                'status'      => 'planning',
            ]),
        ];

        // ── 3. Quotation ──────────────────────────────────────────
        $defaultTerms = "Harga belum termasuk PPN.\nPembayaran dilakukan dalam 30 hari setelah invoice diterima.";

        // Q1 - draft, project p2
        $q1 = $this->makeQuotation(1, [
            'client_id'   => $c2->id,
            'project_id'  => $projects['p2']->id,
            'company_id'  => $company->id,
            'issue_date'  => $today->copy()->subDays(2),
            'valid_until' => $today->copy()->addDays(28),
            'subject'     => 'Penawaran Redesign Website Korporat',
            'status'      => 'draft',
            'tax_percent' => 11,
            'terms'       => $defaultTerms,
            'created_by'  => $admin?->id,
        ], [
            ['UI/UX Design (10 halaman)', 1, 'paket', 25_000_000],
            ['Front-end Development',     1, 'paket', 30_000_000],
            ['CMS Migration & Training',  1, 'paket', 12_500_000],
        ]);

        // Q2 - sent
        $q2 = $this->makeQuotation(2, [
            'client_id'   => $c4->id,
            'project_id'  => $projects['p4']->id,
            'company_id'  => $company->id,
            'issue_date'  => $today->copy()->subDays(7),
            'valid_until' => $today->copy()->addDays(23),
            'subject'     => 'Penawaran Pengadaan Switch & Router',
            'status'      => 'sent',
            'tax_percent' => 11,
            'terms'       => $defaultTerms,
            'created_by'  => $admin?->id,
        ], [
            ['Switch 24-Port Managed',   6,  'unit', 8_500_000],
            ['Router Enterprise',        3,  'unit', 12_000_000],
            ['Kabel UTP Cat6 (305m)',    4,  'roll', 1_750_000],
            ['Jasa Instalasi & Setup',   1,  'paket', 15_000_000],
        ]);

        // Q3 - accepted (belum dikonversi)
        $q3 = $this->makeQuotation(3, [
            'client_id'   => $c1->id,
            'project_id'  => $projects['p1']->id,
            'company_id'  => $company->id,
            'issue_date'  => $today->copy()->subDays(40),
            'valid_until' => $today->copy()->subDays(10),
            'subject'     => 'Penawaran Modul Tambahan ERP - Inventory',
            'status'      => 'accepted',
            'tax_percent' => 11,
            'terms'       => $defaultTerms,
            'created_by'  => $admin?->id,
        ], [
            ['Modul Inventory Management',     1, 'paket', 45_000_000],
            ['Integrasi dengan Modul Finance', 1, 'paket', 15_000_000],
        ]);

        // Q4 - rejected
        $q4 = $this->makeQuotation(4, [
            'client_id'   => $c5->id,
            'project_id'  => null,
            'company_id'  => $company->id,
            'issue_date'  => $today->copy()->subDays(60),
            'valid_until' => $today->copy()->subDays(30),
            'subject'     => 'Penawaran Konsultasi IT Strategy',
            'status'      => 'rejected',
            'tax_percent' => 11,
            'terms'       => $defaultTerms,
            'created_by'  => $admin?->id,
            'notes'       => 'Klien memilih vendor lain karena pertimbangan budget.',
        ], [
            ['Konsultasi IT Strategy (3 bulan)', 3, 'bulan', 20_000_000],
        ]);

        // Q5 - converted (akan jadi sumber invoice INV-1)
        $q5 = $this->makeQuotation(5, [
            'client_id'   => $c1->id,
            'project_id'  => $projects['p1']->id,
            'company_id'  => $company->id,
            'issue_date'  => $today->copy()->subDays(50),
            'valid_until' => $today->copy()->subDays(20),
            'subject'     => 'Penawaran Implementasi ERP Tahap 1',
            'status'      => 'converted',
            'tax_percent' => 11,
            'terms'       => $defaultTerms,
            'created_by'  => $admin?->id,
        ], [
            ['Analisa Kebutuhan & Blueprint', 1, 'paket', 35_000_000],
            ['Setup Server & Lisensi',         1, 'paket', 40_000_000],
            ['Implementasi Modul Finance',     1, 'paket', 55_000_000],
        ]);

        // Q6 - expired
        $this->makeQuotation(6, [
            'client_id'   => $c3->id,
            'project_id'  => $projects['p3']->id,
            'company_id'  => $company->id,
            'issue_date'  => $today->copy()->subDays(120),
            'valid_until' => $today->copy()->subDays(90),
            'subject'     => 'Penawaran Perpanjangan Audit Keamanan',
            'status'      => 'expired',
            'tax_percent' => 11,
            'terms'       => $defaultTerms,
            'created_by'  => $admin?->id,
        ], [
            ['Annual Security Audit Retainer', 1, 'tahun', 50_000_000],
        ]);

        // ── 4. Invoice ────────────────────────────────────────────
        $payTerms = "Pembayaran via transfer ke:\nBCA 1234567890 a.n. " . $company->name
                  . "\nMohon kirim bukti transfer setelah pembayaran.";

        // INV-1: dari quotation Q5 (converted), status PAID
        $inv1 = $this->makeInvoice(1, [
            'client_id'    => $q5->client_id,
            'project_id'   => $q5->project_id,
            'quotation_id' => $q5->id,
            'company_id'   => $company->id,
            'issue_date'   => $today->copy()->subDays(45),
            'due_date'     => $today->copy()->subDays(15),
            'subject'      => $q5->subject,
            'status'       => 'sent',
            'tax_percent'  => 11,
            'terms'        => $payTerms,
            'created_by'   => $admin?->id,
        ], $q5->items->map(fn($i) => [$i->description, $i->quantity, $i->unit, $i->unit_price])->all());

        // Record full payment
        $inv1->update([
            'paid_amount'       => $inv1->total,
            'payment_date'      => $today->copy()->subDays(10),
            'payment_method'    => 'Transfer Bank',
            'payment_reference' => 'TRF-BCA-2026051501',
        ]);
        $inv1->recalculate();

        // INV-2: PARTIAL paid
        $inv2 = $this->makeInvoice(2, [
            'client_id'    => $c2->id,
            'project_id'   => $projects['p2']->id,
            'company_id'   => $company->id,
            'issue_date'   => $today->copy()->subDays(15),
            'due_date'     => $today->copy()->addDays(15),
            'subject'      => 'Tagihan Tahap 1 - Redesign Website',
            'status'       => 'sent',
            'tax_percent'  => 11,
            'terms'        => $payTerms,
            'created_by'   => $admin?->id,
        ], [
            ['Down Payment 50% - UI/UX & Front-end', 1, 'paket', 27_500_000],
        ]);
        // Partial: bayar 15jt dari ~30.5jt
        $inv2->update([
            'paid_amount'       => 15_000_000,
            'payment_date'      => $today->copy()->subDays(7),
            'payment_method'    => 'Transfer Bank',
            'payment_reference' => 'TRF-MDR-2026051901',
        ]);
        $inv2->recalculate();

        // INV-3: OVERDUE (belum dibayar, due_date sudah lewat)
        $inv3 = $this->makeInvoice(3, [
            'client_id'    => $c3->id,
            'project_id'   => $projects['p3']->id,
            'company_id'   => $company->id,
            'issue_date'   => $today->copy()->subDays(60),
            'due_date'     => $today->copy()->subDays(30),
            'subject'      => 'Tagihan Final Audit Keamanan',
            'status'       => 'sent',
            'tax_percent'  => 11,
            'terms'        => $payTerms,
            'created_by'   => $admin?->id,
        ], [
            ['Pentest Report & Remediation Advisory', 1, 'paket', 35_000_000],
            ['Executive Summary Presentation',         1, 'sesi',  5_000_000],
        ]);
        $inv3->recalculate(); // jadi 'overdue' otomatis

        // INV-4: SENT (belum jatuh tempo, belum dibayar)
        $inv4 = $this->makeInvoice(4, [
            'client_id'    => $c4->id,
            'project_id'   => null,
            'company_id'   => $company->id,
            'issue_date'   => $today->copy()->subDays(5),
            'due_date'     => $today->copy()->addDays(25),
            'subject'      => 'Maintenance & Support Bulan Ini',
            'status'       => 'sent',
            'tax_percent'  => 11,
            'terms'        => $payTerms,
            'created_by'   => $admin?->id,
        ], [
            ['Monthly IT Support Retainer', 1, 'bulan', 8_500_000],
        ]);

        // INV-5: DRAFT
        $this->makeInvoice(5, [
            'client_id'    => $c1->id,
            'project_id'   => $projects['p1']->id,
            'company_id'   => $company->id,
            'issue_date'   => $today->copy(),
            'due_date'     => $today->copy()->addDays(30),
            'subject'      => 'Tagihan Tahap 2 - ERP Implementation',
            'status'       => 'draft',
            'tax_percent'  => 11,
            'terms'        => $payTerms,
            'created_by'   => $admin?->id,
        ], [
            ['Implementasi Modul HR',         1, 'paket', 50_000_000],
            ['Training End-User (3 batch)',   3, 'batch',  5_000_000],
        ]);

        $this->command?->info('B2B demo data berhasil dibuat: ' .
            count($clients) . ' klien, ' . count($projects) . ' project, 6 quotation, 5 invoice.');
    }

    /** @param array<int, array{0:string,1:int|float,2:?string,3:int|float}> $items */
    private function makeQuotation(int $seq, array $attrs, array $items): Quotation
    {
        $attrs['quotation_number'] = 'QUO-' . Carbon::today()->format('Ym')
            . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        $q = Quotation::create($attrs);
        foreach ($items as $i => [$desc, $qty, $unit, $price]) {
            QuotationItem::create([
                'quotation_id' => $q->id,
                'description'  => $desc,
                'quantity'     => $qty,
                'unit'         => $unit,
                'unit_price'   => $price,
                'amount'       => round($qty * $price, 2),
                'sort_order'   => $i,
            ]);
        }
        $q->refresh()->recalculate();
        return $q->refresh();
    }

    /** @param array<int, array{0:string,1:int|float,2:?string,3:int|float}> $items */
    private function makeInvoice(int $seq, array $attrs, array $items): Invoice
    {
        $attrs['invoice_number'] = 'INV-' . Carbon::today()->format('Ym')
            . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        $inv = Invoice::create($attrs);
        foreach ($items as $i => [$desc, $qty, $unit, $price]) {
            InvoiceItem::create([
                'invoice_id'  => $inv->id,
                'description' => $desc,
                'quantity'    => $qty,
                'unit'        => $unit,
                'unit_price'  => $price,
                'amount'      => round($qty * $price, 2),
                'sort_order'  => $i,
            ]);
        }
        $inv->refresh()->recalculate();
        return $inv->refresh();
    }
}
