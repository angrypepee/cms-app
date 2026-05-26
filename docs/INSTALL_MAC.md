# Install CMS App on macOS

## One-time setup

1. **Install Docker Desktop for Mac**
   https://www.docker.com/products/docker-desktop/
   (Apple Silicon or Intel — both supported.)

2. **Install Git** (if not already)
   ```bash
   xcode-select --install
   ```

3. **Clone the repo** into a folder of your choice (e.g. `~/Apps`):
   ```bash
   cd ~/Apps
   git clone https://github.com/angrypepee/cms-app.git
   cd cms-app
   ```

4. **Make the launcher scripts executable** (one time):
   ```bash
   chmod +x start.command update.command stop.command
   ```

## Daily use

| Action | What to do |
|--------|------------|
| **Start the app** | Double-click `start.command` in Finder |
| **Open the app** | http://localhost:8080 (auto-opens) |
| **Update to latest version** | Double-click `update.command` |
| **Stop the app** | Double-click `stop.command` |

## What gets persisted

- **Database** → Docker volume `db_data` (survives restarts and updates)
- **Uploaded files / logs** → Docker volume `app_storage`

## First-run notes

- First `start.command` takes 3–5 min (downloads PHP/MariaDB images, builds container, installs Composer deps, runs migrations).
- Subsequent starts: ~10 seconds.
- Default credentials: see `docs/dummy-accounts.md`.

## Troubleshooting

```bash
docker compose logs -f app   # see app logs
docker compose logs -f db    # see database logs
docker compose down -v       # nuke everything (including DB!) and start over
```
