# BlastVista

BlastVista is a Laravel-powered kiosk application for movie loops, barcode-triggered playback, and a Filament admin console. It delivers a home-screen experience that auto-loads favorite videos, accepts barcode scans for on-demand playback, and exposes management pages for curating content.

## Core Features

- Idle loop that continuously plays favorited videos on the welcome screen.
- Barcode workflow for instantly queueing a specific title via `/barcode`.
- Filament admin panel (`/admin`) for uploads, curation, and analytics pages including top-20 reporting.
- Helper scripts in `blastoff` for launching the kiosk browser in fullscreen and booting the Laravel server.
- Artisan command for synchronizing storage with the database (`SyncVideosFromStorage`).

## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8 (or compatible) for persistent storage
- Redis (optional) if you enable queues or cache backends other than the default

## Initial Setup

1. Clone the repository and change into the project directory.
2. Duplicate `.env.example` to `.env` and update database credentials, `APP_URL`, mail settings, and `FILESYSTEM_DISK=public`.
3. Generate your application key:

	php artisan key:generate

4. Install PHP dependencies:

	composer install

5. Install frontend dependencies and build assets:

	npm install
	npm run build

6. Prepare the database:

	php artisan migrate --seed

7. Link the storage disk so public videos resolve correctly:

	php artisan storage:link

## Running Locally

- Start the Laravel HTTP server: `php artisan serve --host=0.0.0.0 --port=8000`
- Start the Vite dev server (if you prefer hot reloading): `npm run dev`
- Or use the helper scripts:
  - `blastoff/start_laravel.sh` starts the Laravel server from the project root.
  - `blastoff/start_chromium_fullscreen.sh` launches Firefox/Chromium in kiosk mode pointing at the idle loop (override the URL with an argument if needed).
  - `blastoff/start_chromium.sh` opens the first available supported browser without forcing fullscreen.

## Barcode Workflow

- Barcode scans post to `/barcode` and resolve to a `Video` record by its `barcode` column.
- Successful scans render the single-video `welcome` view with autoplay; once playback ends (or after five minutes of inactivity) the kiosk returns to the idle loop at `/`.
- Barcode failures surface validation errors on the same screen for quick rescan.

## Admin & Filament

- Sign in and navigate to `/admin` for the Filament dashboard.
- From there you can upload or delete videos, mark favorites, review top performers, and manage playlists.
- The player page under the admin panel offers a quick preview of stored assets without leaving the console.

## Storage & Media

- Uploaded videos are stored on the `public` disk under `storage/app/public/videos` and served through `public/storage`.
- Reference CSVs (`storage/barcodes.csv`, `storage/videos_list.csv`) can be used for bulk updates or external tooling.
- Use the bundled artisan command to ensure the database stays in sync with the filesystem:

	php artisan sync:videos-from-storage

## Testing

- Run the Laravel feature and unit tests with `php artisan test` or `./vendor/bin/phpunit`.
- JavaScript tests (if added) can be run with `npm test`.

## Deployment Notes

- Compile production assets with `npm run build`.
- Cache configuration and routes for faster startup:

	php artisan config:cache
	php artisan route:cache

- Ensure the `storage` and `bootstrap/cache` directories remain writable by the web server user.

## License

This project is proprietary and intended for internal use. Reach out to the maintainer before sharing code externally.
