# PayrollLim — Documentation

**PayrollLim** is a Laravel-based payroll management system designed to manage companies, employees, and payroll slips with multi-role access control.

---

## Table of Contents

1. [Getting Started](./getting-started.md)
2. [Dummy Accounts & Demo Data](./dummy-accounts.md)
3. [Roles & Permissions](./roles-permissions.md)
4. [Features](./features.md)

---

## Tech Stack

| Layer        | Technology              |
|--------------|-------------------------|
| Framework    | Laravel 11              |
| Language     | PHP 8.2+                |
| Database     | MySQL (via MAMP)        |
| Frontend     | Blade Templates + Vite  |
| PDF Export   | DomPDF (barryvdh)       |
| Auth         | Laravel built-in Auth   |

---

## Project Structure (Key Directories)

```
app/
  Enums/            # UserRole, EmployeeCategory enums
  Http/Controllers/ # Auth, Dashboard, Company, Employee, PayrollSlip, User
  Models/           # Company, Employee, PayrollItem, PayrollSlip, User
database/
  migrations/       # All table migrations
  seeders/          # DatabaseSeeder (users) + DemoSeeder (company/employees/slips)
resources/views/    # Blade templates
routes/web.php      # All application routes
docs/               # This documentation folder
```

---

## Quick Links

- App URL (MAMP): [http://localhost:8888/payroll_lim/public](http://localhost:8888/payroll_lim/public)
- Login page: `/login`
- Dashboard: `/`
