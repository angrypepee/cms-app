# Features

## 1. Authentication

- Email/password login via `/login`
- Session-based authentication (Laravel Auth)
- All routes except `/login` are protected by `auth` middleware
- Redirect to `/login` when unauthenticated

---

## 2. Dashboard

Route: `GET /`

Overview statistics and quick-access cards. Displays totals for companies, employees, and payroll slips.

---

## 3. Company Management

Route prefix: `/companies`

| Action       | Route                    | Method |
|--------------|--------------------------|--------|
| List         | `/companies`             | GET    |
| Create form  | `/companies/create`      | GET    |
| Store        | `/companies`             | POST   |
| Show         | `/companies/{id}`        | GET    |
| Edit form    | `/companies/{id}/edit`   | GET    |
| Update       | `/companies/{id}`        | PUT    |
| Delete       | `/companies/{id}`        | DELETE |

**Fields:** Name, Tagline, Address, Phone, Email, Logo (image upload), NPWP

---

## 4. Employee Management

Route prefix: `/employees`

| Action       | Route                            | Method |
|--------------|----------------------------------|--------|
| List         | `/employees`                     | GET    |
| Create form  | `/employees/create`              | GET    |
| Store        | `/employees`                     | POST   |
| Show         | `/employees/{id}`                | GET    |
| Edit form    | `/employees/{id}/edit`           | GET    |
| Update       | `/employees/{id}`                | PUT    |
| Delete       | `/employees/{id}`                | DELETE |
| By company   | `/companies/{id}/employees`      | GET    |

**Fields:**

| Field                    | Description                          |
|--------------------------|--------------------------------------|
| `employee_id`            | Manual ID (e.g. EMP-001)             |
| `name`                   | Full name                            |
| `position`               | Job title                            |
| `department`             | Department name                      |
| `employee_category`      | See categories below                 |
| `grade`                  | Grade/level (e.g. IV-A)              |
| `bank_name`              | Bank name for salary transfer        |
| `bank_account`           | Bank account number                  |
| `npwp`                   | Tax ID (optional)                    |
| `bpjs_kesehatan`         | Health insurance number              |
| `bpjs_ketenagakerjaan`   | Employment insurance number          |
| `is_active`              | Active/inactive status               |

**Employee Categories (`EmployeeCategory` enum):**

| Value        | Label                 |
|--------------|-----------------------|
| `tetap`      | Karyawan Tetap        |
| `kontrak`    | Kontrak               |
| `proyek`     | Berdasarkan Proyek    |
| `probasi`    | Masa Percobaan        |
| `magang`     | Magang                |
| `paruh_waktu`| Paruh Waktu           |

---

## 5. Payroll Slips

Route prefix: `/payroll-slips`

| Action         | Route                                  | Method  |
|----------------|----------------------------------------|---------|
| List           | `/payroll-slips`                       | GET     |
| Create form    | `/payroll-slips/create`                | GET     |
| Store          | `/payroll-slips`                       | POST    |
| Show           | `/payroll-slips/{id}`                  | GET     |
| Edit form      | `/payroll-slips/{id}/edit`             | GET     |
| Update         | `/payroll-slips/{id}`                  | PUT     |
| Delete         | `/payroll-slips/{id}`                  | DELETE  |
| Download PDF   | `/payroll-slips/{id}/pdf`              | GET     |
| Publish        | `/payroll-slips/{id}/publish`          | PATCH   |
| Sign           | `/payroll-slips/{id}/sign`             | PATCH   |

### Slip Lifecycle

```
draft  ──► published ──► signed
```

- **draft** — editable, not visible to employees
- **published** — finalised, PDF downloadable, available for signing
- **signed** — digitally signed by a Signature Admin or Administrator; the signer's name and title are recorded

### Payroll Items

Each slip contains line items split into two types:

- `income` — earnings (e.g. Gaji Pokok, Tunjangan Jabatan)
- `deduction` — deductions (e.g. PPh 21, BPJS Kesehatan)

Items have a `sort_order` for display ordering.

### Slip Number Format

Auto-generated: `SG-YYYYMMDD-XXXX` (e.g. `SG-20260531-0001`)

---

## 6. PDF Export

Route: `GET /payroll-slips/{id}/pdf`

Generates a downloadable PDF of the payroll slip using **DomPDF**. The PDF includes:

- Company header with logo
- Employee details (name, position, department, NPWP, BPJS numbers)
- Period and payment date
- Itemised income and deduction table
- Take-home pay total
- Signature block (name + title if signed)

---

## 7. User Management *(Admin only)*

Route prefix: `/users`

| Action    | Route               | Method |
|-----------|---------------------|--------|
| List      | `/users`            | GET    |
| Edit form | `/users/{id}/edit`  | GET    |
| Update    | `/users/{id}`       | PUT    |

Admins can change any user's **role** and **title** (used on signed slips).
Create/delete user accounts is handled outside this panel (e.g. via Artisan tinker or a registration flow).
