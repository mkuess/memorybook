# Laravel 11 Project

## Overview
A clean Laravel 11 skeleton application configured for PostgreSQL, Blade templating, Vite + Tailwind CSS v4. Built for standalone production deployment via Docker Compose on a self-hosted VM. Replit is used for development only.

## Architecture
- **Framework**: Laravel 11
- **PHP Version**: 8.4 (dev via Replit), 8.2+ (prod via Docker)
- **Database**: PostgreSQL 16 (Replit-hosted in dev, Docker service in prod)
- **Frontend**: Vite + Tailwind CSS v4 (Blade templates)
- **Node**: v20

## Project Structure
- `app/` — Application code (Http, Models, Providers)
- `routes/web.php` — Web routes
- `resources/views/` — Blade templates
- `database/` — Migrations, factories, seeders
- `public/` — Web root (built assets in `public/build/`)
- `config/` — Application configuration
- `storage/` — Logs, cached views, file uploads
- `nginx/app.conf` — Nginx virtual host config (used in Docker)
- `docker/php/www.conf` — PHP-FPM pool config (used in Docker)
- `Dockerfile` — PHP 8.2-fpm-alpine image with pdo_pgsql, gd, intl
- `docker-compose.yml` — Production stack: app (php-fpm) + web (nginx) + db (postgres:16)

## Development (Replit)
The app runs via `php artisan serve` on port 5000 against the Replit-hosted PostgreSQL instance. Database credentials are injected via Replit's secret environment variables (PGHOST, PGPORT, PGUSER, PGPASSWORD, PGDATABASE).

### Key Commands
- `composer install` — Install PHP dependencies
- `npm install && npm run build` — Build frontend assets
- `php artisan migrate` — Run database migrations
- `php artisan key:generate` — Generate app key

## Production (Docker Compose)
Three services: `app` (php-fpm), `web` (nginx), `db` (postgres:16-alpine).

### First-time production setup
```bash
cp .env.example .env
# Edit .env: set APP_KEY, DB_PASSWORD, APP_URL, etc.
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link
```

### .env for production (required keys)
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=          # generate with artisan key:generate
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=db        # Docker service name
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=postgres
DB_PASSWORD=your-strong-password
```

## Workflow
- **Start application**: `php artisan serve --host=0.0.0.0 --port=5000` on port 5000 (webview)

## Not yet installed
- Filament (admin panel) — add when product features begin
- Authentication scaffolding — add as needed
