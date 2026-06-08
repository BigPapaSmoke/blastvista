# Kiosk Deployment Guide

## Step 1: Prepare Files on Your Computer

1. **Create deployment package:**
   ```bash
   cd /home/bigpapa/Projects/blastvistafiruman
   zip -r kiosk-app.zip . -x "vendor/*" "node_modules/*" "storage/app/public/videos/*" "public/build/*" ".git/*"
   ```

2. **Create a videos backup separately** (videos are large):
   ```bash
   cd storage/app/public
   zip -r videos-backup.zip videos/
   ```

3. **You should now have:**
   - `kiosk-app.zip` (the application)
   - `videos-backup.zip` (all video files)

---

## Step 2: Setup on Mini PC (Do This ONCE - Create Master)

1. **Copy files to mini PC:**
   - Transfer `kiosk-app.zip` and `videos-backup.zip` to mini PC

2. **Extract application:**
   ```bash
   cd /home/[your-username]
   mkdir blastvistafiruman
   cd blastvistafiruman
   unzip /path/to/kiosk-app.zip
   ```

3. **Install PHP dependencies:**
   ```bash
   composer install
   ```

4. **Setup storage:**
   ```bash
   php artisan storage:link
   ```

5. **Extract videos:**
   ```bash
   cd storage/app/public
   unzip /path/to/videos-backup.zip
   ```

6. **Clear caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

7. **Setup database:**
   ```bash
   php artisan migrate
   ```

8. **Create admin user:**
   ```bash
   php artisan tinker --execute="\$user = \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@admin.com', 'password' => bcrypt('password'), 'email_verified_at' => now()]); echo 'User created';"
   ```

9. **Test the application:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```
   - Open browser to `http://localhost:8000`
   - Videos should play
   - Click "admin" link at bottom
   - Login with: `admin@admin.com` / `password`
   - Verify admin panel works

10. **Setup browser auto-restart (recommended):**
   Install user systemd services so Laravel and Chromium both restart if they crash.

   ```bash
   mkdir -p ~/.config/systemd/user
   cp /home/[username]/blastvistafiruman/blastoff/kiosk-laravel.service ~/.config/systemd/user/kiosk-laravel.service
   cp /home/[username]/blastvistafiruman/blastoff/kiosk-browser.service ~/.config/systemd/user/kiosk-browser.service
   sed -i "s#%h/Downloads/blastvistafiruman#/home/[username]/blastvistafiruman#g" ~/.config/systemd/user/kiosk-laravel.service
   sed -i "s#%h/Downloads/blastvistafiruman#/home/[username]/blastvistafiruman#g" ~/.config/systemd/user/kiosk-browser.service
   systemctl --user daemon-reload
   systemctl --user enable --now kiosk-laravel.service kiosk-browser.service
   systemctl --user status kiosk-laravel.service
   systemctl --user status kiosk-browser.service
   ```

   Notes:
   - If your distro uses a different display, change `DISPLAY=:0` in the service file.
   - Keep `Environment=RUN_FOREGROUND=1` so systemd can monitor and restart the browser process correctly.
   - Browser service is already configured to start after `kiosk-laravel.service`.

11. **Setup the daily missing-barcode email:**
      Choose one of these options so Laravel's scheduler runs all the time.

      **Option A: systemd service + timer**
      - Copy `blastoff/kiosk-scheduler.service` to `/etc/systemd/system/kiosk-scheduler.service`
      - Copy `blastoff/kiosk-scheduler.timer` to `/etc/systemd/system/kiosk-scheduler.timer`
      - Replace `[username]` with your Linux username in the service file
      - Run:
         ```bash
         sudo systemctl daemon-reload
         sudo systemctl enable --now kiosk-scheduler.timer
         sudo systemctl status kiosk-scheduler.timer
         ```

      **Option B: cron**
      Add this line with `crontab -e`:
      ```bash
      * * * * * cd /home/[username]/blastvistafiruman && php artisan schedule:run >> storage/logs/scheduler.log 2>&1
      ```

      **Recipient address**
      Set one of these in `.env`:
      ```
      MISSING_BARCODE_REPORT_EMAIL=you@example.com
      ADMIN_EMAIL=you@example.com
      ```

---

## Step 3: Create Master Image for Cloning

**IMPORTANT:** Once mini PC is working perfectly, create the master image.

1. **Stop the application**
2. **Create master backup:**
   ```bash
   cd /home/[username]
   zip -r kiosk-master.zip blastvistafiruman/
   ```
3. **Keep this `kiosk-master.zip` safe - use it to deploy to all other units**

---

## Step 4: Deploy to Each New Kiosk Unit

For each of the 10+ units:

1. **Copy `kiosk-master.zip` to the new unit**

2. **Extract:**
   ```bash
   cd /home/[username]
   unzip kiosk-master.zip
   ```

3. **Setup database:**
   ```bash
   cd blastvistafiruman
   php artisan migrate
   ```

4. **Create admin user:**
   ```bash
   php artisan tinker --execute="\$user = \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@admin.com', 'password' => bcrypt('password'), 'email_verified_at' => now()]); echo 'User created';"
   ```

5. **Start the application and browser services:**
   ```bash
   systemctl --user start kiosk-laravel.service kiosk-browser.service
   ```

6. **Test:** Videos should auto-play, admin link should work

---

## Troubleshooting

### Videos not showing:
```bash
cd /home/[username]/blastvistafiruman
php artisan storage:link
```

### Admin gives 403 error:
Make sure user is created and email is verified (Step 4, item 4)

### Need to change admin password:
Check the `.env` file in the app directory:
```
ADMIN_EMAIL=admin@admin.com
ADMIN_PASSWORD=password
```

### Application not starting:
```bash
cd /home/[username]/blastvistafiruman
php artisan config:clear
php artisan cache:clear
php artisan serve --host=0.0.0.0 --port=8000
```

---

## Important Notes

- **Each unit is independent** - no network connection between them needed
- **Videos are stored locally** on each unit in `storage/app/public/videos/`
- **Database is local** - SQLite file in `database/database.sqlite`
- **Admin credentials** are in `.env` file if you forget: `admin@admin.com` / `password`
- **Different IPs don't matter** - each runs on localhost (127.0.0.1)
- **Browser kiosk mode** - Use the `kiosk-browser.service` user unit for auto-restart on crashes
- **Scheduler required** - The daily missing-barcode email only sends when either the systemd timer or cron job is running

---

## Quick Reference Commands

Start application:
```bash
systemctl --user start kiosk-laravel.service
```

Start browser service in kiosk mode:
```bash
systemctl --user start kiosk-browser.service
```

Admin login:
- URL: `http://localhost:8000` then click "admin" at bottom
- Email: `admin@admin.com`
- Password: `password`
