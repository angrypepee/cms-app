# Roles & Permissions

The system has three user roles defined in `App\Enums\UserRole`.

---

## Role Summary

| Role              | Value              | Badge Color | Description                                   |
|-------------------|--------------------|-------------|-----------------------------------------------|
| **Administrator** | `admin`            | Red/Danger  | Full access. Manages users, companies, employees, payroll slips. |
| **HRD / Staff**   | `hr`               | Blue/Primary| Can manage companies, employees, and create/edit payroll slips. Cannot manage user accounts or sign slips. |
| **Signature Admin** | `signature_admin` | Green/Success | Same access as HR plus can digitally sign published payroll slips. |

---

## Permission Matrix

| Feature                          | Admin | HR  | Signature Admin |
|----------------------------------|:-----:|:---:|:---------------:|
| View dashboard                   | ✅    | ✅  | ✅              |
| Create / edit / delete companies | ✅    | ✅  | ✅              |
| Create / edit / delete employees | ✅    | ✅  | ✅              |
| Create payroll slips             | ✅    | ✅  | ✅              |
| Edit draft payroll slips         | ✅    | ✅  | ✅              |
| Publish payroll slips            | ✅    | ✅  | ✅              |
| **Sign payroll slips**           | ✅    | ❌  | ✅              |
| Download payroll slip PDF        | ✅    | ✅  | ✅              |
| **View user list**               | ✅    | ❌  | ❌              |
| **Edit user roles / titles**     | ✅    | ❌  | ❌              |

---

## Role Helpers (on `User` model)

```php
$user->isAdmin();           // true if role === admin
$user->isSignatureAdmin();  // true if role === admin OR signature_admin
$user->canSign();           // true if role can sign slips (admin, signature_admin)
$user->canManageUsers();    // true if role === admin only
```

---

## Default Role

New users created via `User::factory()` or direct insert without a role will default to `hr` (set in the migration).

---

## Changing a User's Role

Only an **Administrator** can access `/users` to change roles.

1. Log in as `admin@payrolllim.test`
2. Go to **Users** in the navigation
3. Click **Edit** next to a user
4. Select the new role and optionally set a signature title
5. Click **Save**

The `title` field is printed on payroll slips when the user signs them (e.g., "Direktur Operasional").
