#!/usr/bin/env bash
# Reset Docker DB to fresh production state with ONLY superadmin + admin accounts.
# WARNING: Destroys ALL data. Backup first if needed.

cd "$(dirname "$0")"

read -p "This will WIPE all data in the Docker DB. Type YES to continue: " ans
[ "$ans" != "YES" ] && { echo "Aborted."; exit 1; }

# Prompt for credentials
read -p "Super Admin email [superadmin@example.com]: " SA_EMAIL
SA_EMAIL=${SA_EMAIL:-superadmin@example.com}
read -s -p "Super Admin password: " SA_PASS; echo
[ -z "$SA_PASS" ] && { echo "Password required."; exit 1; }

read -p "Admin email [admin@example.com]: " AD_EMAIL
AD_EMAIL=${AD_EMAIL:-admin@example.com}
read -s -p "Admin password: " AD_PASS; echo
[ -z "$AD_PASS" ] && { echo "Password required."; exit 1; }

read -p "Company name [My Company]: " CO_NAME
CO_NAME=${CO_NAME:-My Company}

echo "Resetting database..."
docker compose exec -T app php artisan migrate:fresh --force

echo "Seeding production accounts..."
docker compose exec -T \
    -e SEED_COMPANY_NAME="$CO_NAME" \
    -e SEED_SUPERADMIN_EMAIL="$SA_EMAIL" \
    -e SEED_SUPERADMIN_PASSWORD="$SA_PASS" \
    -e SEED_ADMIN_EMAIL="$AD_EMAIL" \
    -e SEED_ADMIN_PASSWORD="$AD_PASS" \
    app php artisan db:seed --class=ProductionSeeder --force

echo ""
echo "Done. Login at http://localhost:8080"
echo "  Super Admin: $SA_EMAIL"
echo "  Admin:       $AD_EMAIL"
read -n 1 -p "Press any key to close..."
