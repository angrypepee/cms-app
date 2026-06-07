# Production Safety Guidelines

## 🚨 CRITICAL - NEVER DO THIS IN PRODUCTION

```bash
# ❌ WILL DELETE ALL DATA - NEVER USE IN PRODUCTION
php artisan migrate:fresh
php artisan migrate:reset
php artisan tinker
php artisan db:seed
```

## ✅ SAFE PRODUCTION OPERATIONS

```bash
# ✅ Safe - only runs new migrations
php artisan migrate

# ✅ Safe - creates backup
mysqldump -u cms -pcms_secret cms_app > backup.sql

# ✅ Safe - reads data only
php artisan tinker --no-interaction  # (view only, no modifications)
```

## 📋 Production Database Workflow

1. **Always backup BEFORE changes:**
   ```bash
   docker compose exec -T db mariadb-dump -u cms -pcms_secret cms_app > backups/pre-migration-$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Test migrations on staging first**
   - Never run untested migrations on production
   - Verify data integrity after each migration

3. **Keep automated daily backups:**
   - Store in `/storage/backups/`
   - Rotate old backups monthly
   - Test backup restoration monthly

4. **Monitor for issues:**
   - Check application logs: `/storage/logs/`
   - Verify database integrity: `REPAIR TABLE [table_name]`
   - Monitor disk space for backups

## 🔐 Backup Recovery

If database is corrupted, restore from backup:

```bash
cd /Applications/MAMP/htdocs/payroll_lim
docker compose exec -T db mariadb -u cms -pcms_secret cms_app < storage/backups/[backup_file].sql
```

## 📞 Emergency Contact

- Production backup location: `storage/backups/`
- Recent backups:
  - `PRODUCTION_RESTORED_20260607_225712.sql` (Latest - June 7)
  - `prod_cms_app_20260606_150839.sql` (June 6 @ 15:08)

**Last incident:** Database replaced with dev data on June 7, 2026 @ 22:30
**Recovery time:** < 5 minutes via backup restore
