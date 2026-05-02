# Laravel 13 Project

## Overview
A standard Laravel 13 skeleton application with SQLite database, Vite asset bundling, and Tailwind CSS v4.

## Architecture
- **Framework**: Laravel 13
- **PHP Version**: 8.4
- **Database**: SQLite (file: `database/database.sqlite`)
- **Frontend**: Vite + Tailwind CSS v4
- **Node**: v20

## Project Structure
- `app/` — Application code (Http controllers, Models, Providers)
- `routes/web.php` — Web routes
- `resources/` — Views (Blade), CSS, JS
- `database/` — Migrations, factories, seeders, SQLite file
- `public/` — Web root (built assets in `public/build/`)
- `config/` — Application configuration files
- `storage/` — Logs, cached views, file uploads

## Development Setup
The app runs via `php artisan serve` on port 5000.

### Key Commands
- `composer install` — Install PHP dependencies
- `npm install` — Install JS dependencies  
- `npm run build` — Build frontend assets (Vite)
- `php artisan migrate` — Run database migrations
- `php artisan key:generate` — Generate app key

## Environment
- `.env` file is required (copied from `.env.example`)
- `APP_KEY` must be set (use `php artisan key:generate`)
- `DB_CONNECTION=sqlite` with `database/database.sqlite`
- Sessions use database driver

## Workflow
- **Start application**: `php artisan serve --host=0.0.0.0 --port=5000` on port 5000 (webview)

## Deployment
- Target: autoscale
- Build: `composer install --no-dev --optimize-autoloader && npm run build`
- Run: `php artisan config:cache && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=5000`
