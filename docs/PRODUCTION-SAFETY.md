# ⚠️ PRODUCTION SAFETY GUIDELINES

> **CRITICAL:** Cara yang AMAN dan TIDAK AMAN untuk manage database production.

---

## 🚫 JANGAN PERNAH JALANKAN INI DI PRODUCTION

```bash
# ❌ DESTROY SELURUH DATA & TABEL!
php artisan migrate:fresh --seed --force

# ❌ ATAU
php artisan migrate:fresh --force

# ❌ ATAU
php artisan migrate:fresh --seed
```

**Akibat:** Semua data yang sudah diinput pengguna akan **HILANG SELAMANYA**. Tidak bisa di-recover!

---

## ✅ CARA YANG AMAN UNTUK UPDATE PRODUCTION

### 1. Hanya Apply Migrations (RECOMMENDED)
```bash
# Menambah kolom baru, membuat tabel baru — SAFE
php artisan migrate --force

# Untuk environment production lebih eksplisit
APP_ENV=production php artisan migrate --force
```

✅ **Aman:** Hanya menjalankan migrations yang belum dijalankan. Data existing TETAP AMAN.

### 2. Hanya Seed Kontrak Baru (Untuk Signed Contracts)
```bash
# Hanya sign kontrak yang sudah ada — SAFE
php artisan db:seed --class=SignAllContractDocumentsSeeder --force

# Atau seed kelas lain yang tidak destroy data
php artisan db:seed --class=SpecificSeeder --force
```

✅ **Aman:** Menambah data baru atau update existing records. Tidak drop tabel.

### 3. Backup SEBELUM Operasi Besar
```bash
# BACKUP DULU
docker compose exec -T db mysqldump -u root -proot payroll_app > backup-$(date +%Y%m%d-%H%M%S).sql

# BARU jalankan migrate
php artisan migrate --force

# VERIFY hasil
php artisan tinker
```

---

## 📋 Checklist Sebelum Production Update

- [ ] **Backup database** → `mysqldump` export ke file
- [ ] **Review migrations** → Pastikan hanya ADD kolom, tidak DROP
- [ ] **Test di staging/local** DULU → Jalankan migrate di lokal, pastikan berjalan
- [ ] **Notify stakeholders** → Inform pengguna tentang maintenance window
- [ ] **Plan rollback** → Siapkan backup untuk restore jika ada error
- [ ] **Jalankan migrate** → `php artisan migrate --force`
- [ ] **Verify data** → Cek database struktur dan data integritas
- [ ] **Monitor logs** → Pantau app logs untuk error

---

## 🔒 Environment-Specific Commands

### Development (SAFE untuk experiment)
```bash
# OK untuk develop & test
php artisan migrate:fresh --seed --force
```

### Production (STRICT — data sensitif!)
```bash
# HANYA untuk schema updates
APP_ENV=production php artisan migrate --force

# HANYA untuk seed data yang TIDAK destroy
APP_ENV=production php artisan db:seed --class=SafeSeeder --force
```

---

## 🛑 Emergency: Data Accidentally Deleted

Jika `migrate:fresh` terjalankan di production:

1. **Cek apakah ada backup:**
   ```bash
   ls -lah backup-*.sql
   ```

2. **Restore dari backup:**
   ```bash
   docker compose exec -T db mysql -u root -proot payroll_app < backup-20260607-150000.sql
   ```

3. **Jika tidak ada backup:** ❌ Data sudah tidak bisa di-recover.

---

## 📚 Reference: Database Operations Comparison

| Operation | Command | Production | Development | Data Loss Risk |
|-----------|---------|-----------|-------------|----------------|
| **Fresh + Seed** | `migrate:fresh --seed` | ❌ NEVER | ✅ OK | 🔴 100% |
| **Only Migrate** | `migrate` | ✅ SAFE | ✅ OK | 🟢 0% |
| **Only Seed** | `db:seed` | ✅ SAFE* | ✅ OK | 🟡 ~5%** |
| **Backup** | `mysqldump` | ✅ REQUIRED | ✅ Optional | 🟢 0% |
| **Restore** | `mysql < backup.sql` | ✅ RECOVERY | ✅ OK | 🟢 0% |

\* Seed SAFE jika seeder tidak destroy tabel
\*\* Seed risk tergantung seeder logic

---

## 🎯 Summary

**PRODUCTION RULE:**
```
migrate:fresh = ☠️ DEATH (no exceptions!)
migrate       = ✅ SAFE (always use this)
db:seed       = ✅ SAFE (if seeder is safe)
mysqldump     = ✅ BACKUP (always do this first)
```

**Developer Remember:**
- DEVELOPMENT = ekspermen boleh
- PRODUCTION = data konsumen, HARUS hati-hati
- BACKUP = universal safety net

---

## Last Updated
- **Date:** 2026-06-07
- **Author:** Development Team
- **Status:** 🔴 CRITICAL - READ & ACKNOWLEDGE
