# Getting Started

## Requirements

- PHP 8.2+
- Composer
- MySQL (MAMP recommended on macOS)
- Node.js 18+ & npm

---

## Installation

### 1. Clone / place project

```bash
# Already in MAMP htdocs:
cd /Applications/MAMP/htdocs/payroll_lim
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install JS dependencies & build assets

```bash
npm install
npm run build
```

> For development with hot-reload: `npm run dev`

### 4. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` — set your database connection:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306        # MAMP default MySQL port; change to 8889 if needed
DB_DATABASE=payroll_lim
DB_USERNAME=root
DB_PASSWORD=root    # MAMP default password
```

### 5. Create the database

In phpMyAdmin (MAMP) or via CLI, create a database named `payroll_lim`.

### 6. Run migrations

```bash
php artisan migrate
```

### 7. Seed demo data

```bash
# Seed demo users (admin, hr, signature_admin accounts):
php artisan db:seed --class=DatabaseSeeder

# Seed demo company, employees, and payroll slips:
php artisan db:seed --class=DemoSeeder
```

Or run both at once (fresh):

```bash
php artisan migrate:fresh --seed
```

### 8. Link storage

```bash
php artisan storage:link
```

---

## Accessing the App (MAMP)

Start MAMP servers, then open:

```
http://localhost:8888/payroll_lim/public
```

You will be redirected to `/login`.

---

## Running with PHP's Built-in Server (optional)

```bash
php artisan serve
# App available at http://127.0.0.1:8000
```

---

## Resetting to a Clean Demo State

```bash
php artisan migrate:fresh --seed
```

This drops all tables, re-runs migrations, and re-seeds all demo data including default user accounts.
