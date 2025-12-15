# Port Management Guide

## Problem Solved
Successfully shut down all PHP development server processes that were occupying ports 8000-8006.

## What Was Running
Multiple instances of `php artisan serve` were running on different ports:
- Port 8000-8006: Laravel development servers
- Some processes were zombie processes (defunct) that needed force killing

## Commands Used

### Check what's using a specific port:
```bash
lsof -i :8000
```

### Check multiple ports:
```bash
lsof -i :8000-8006
```

### Kill a specific process:
```bash
kill -9 <PID>
```

### Kill all processes on specific ports:
```bash
lsof -ti :8000-8006 | xargs -r kill -9
```

### Kill all PHP processes (use with caution):
```bash
pkill -9 php
```

### List all PHP processes:
```bash
ps aux | grep "php" | grep -v grep
```

## Prevention Tips

1. **Always stop servers properly**: Use `Ctrl+C` in the terminal where the server is running
2. **Check before starting**: Run `lsof -i :8000` before starting a new server
3. **Use one terminal**: Keep track of which terminal is running your development server
4. **Clean up regularly**: Periodically check for zombie processes

## Starting Your Laravel Server

After clearing all ports, you can start your server on port 8000:
```bash
php artisan serve
```

Or specify a different port:
```bash
php artisan serve --port=8080
```

## Filament Admin Dashboard Fix

The Filament admin dashboard route issue was also resolved:
- Removed conflicting `/admin` route from `routes/web.php`
- Configured custom Dashboard page in `app/Filament/Pages/Dashboard.php`
- Registered Dashboard in `app/Providers/Filament/AdminPanelProvider.php`
- Route `filament.admin.pages.dashboard` is now properly registered at `/admin`
