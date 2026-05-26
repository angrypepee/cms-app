#!/usr/bin/env bash
# Double-click to pull latest code from GitHub and apply updates.
cd "$(dirname "$0")"

echo "Pulling latest changes from GitHub..."
git pull origin main || { echo "git pull failed"; read -n 1; exit 1; }

echo "Rebuilding container..."
docker compose build app

echo "Restarting with migrations..."
docker compose up -d
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

echo "Update complete. App at http://localhost:8080"
read -n 1
