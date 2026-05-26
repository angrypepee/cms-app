# Dummy Accounts & Demo Data

After running `php artisan migrate:fresh --seed` all of the following accounts and demo data will be available.

---

## Default User Accounts

| # | Name              | Email                     | Password   | Role               | Title                        |
|---|-------------------|---------------------------|------------|--------------------|------------------------------|
| 1 | Admin Utama       | admin@payrolllim.test     | `password` | **Administrator**  | Head of HR                   |
| 2 | HR Staff          | hr@payrolllim.test        | `password` | **HRD / Staff**    | HR Officer                   |
| 3 | Kepala Penandatangan | signer@payrolllim.test | `password` | **Signature Admin**| Direktur Operasional         |
| 4 | Test User         | test@example.com          | `password` | HRD / Staff        | *(none)*                     |

> All passwords are `password` (lowercase). Change them immediately in any non-development environment.

---

## Demo Company

| Field    | Value                              |
|----------|------------------------------------|
| Name     | PT Contoh Makmur Sejahtera         |
| Tagline  | Berkembang Bersama, Maju Bersama   |
| Address  | Jl. Sudirman No. 123, Jakarta Selatan 12190 |
| Phone    | 021-5551234                        |
| Email    | hrd@contohmakmur.co.id             |
| NPWP     | 01.234.567.8-901.000               |

---

## Demo Employees

| Employee ID | Name           | Position         | Department      | Grade | Bank   | Category      |
|-------------|----------------|------------------|-----------------|-------|--------|---------------|
| EMP-001     | Budi Santoso   | Senior Developer | Technology      | IV-A  | BCA    | Karyawan Tetap|
| EMP-002     | Siti Rahma     | HR Manager       | Human Resources | III-B | Mandiri| Karyawan Tetap|
| EMP-003     | Ahmad Fauzi    | Finance Staff    | Finance         | II-A  | BNI    | Karyawan Tetap|

---

## Demo Payroll Slips

### Slip 1 — Budi Santoso (Published)

- **Period:** Mei 2026
- **Status:** `published`
- **Cutoff:** 2026-05-01 → 2026-05-31
- **Payment Date:** 2026-05-31

| Income Items           | Amount (Rp)  |
|------------------------|-------------|
| Gaji Pokok             | 10,000,000  |
| Tunjangan Jabatan      | 1,500,000   |
| Uang Makan             | 750,000     |
| Tunjangan Transport    | 250,000     |
| **Total Income**       | **12,500,000** |

| Deduction Items          | Amount (Rp)  |
|--------------------------|-------------|
| PPh 21                   | 875,000     |
| BPJS Kesehatan           | 250,000     |
| BPJS Ketenagakerjaan     | 250,000     |
| **Total Deductions**     | **1,375,000** |

**Take Home Pay: Rp 11,125,000**

---

### Slip 2 — Siti Rahma (Draft)

- **Period:** Mei 2026
- **Status:** `draft`
- **Cutoff:** 2026-05-01 → 2026-05-31
- **Payment Date:** 2026-05-31

| Income Items             | Amount (Rp)  |
|--------------------------|-------------|
| Gaji Pokok               | 8,000,000   |
| Uang Makan               | 750,000     |
| Tunjangan Komunikasi     | 750,000     |
| **Total Income**         | **9,500,000** |

| Deduction Items          | Amount (Rp)  |
|--------------------------|-------------|
| PPh 21                   | 600,000     |
| BPJS Kesehatan           | 200,000     |
| BPJS Ketenagakerjaan     | 150,000     |
| **Total Deductions**     | **950,000**  |

**Take Home Pay: Rp 8,550,000**

---

## Re-seeding

To reset all data to the demo state:

```bash
php artisan migrate:fresh --seed
```
