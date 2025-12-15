# Blast Vista Firuman - Project Overview

## 🎯 Project Summary
This is a **Laravel + Filament** video management system that handles video uploads, favorites, and playback. The project uses Laravel Jetstream for authentication and Filament for the admin panel.

---

## 🔐 Accessing the Admin Dashboard

### **Admin Panel URL:**
```
http://your-domain.com/admin
```

### **Local Development:**
```
http://localhost:8000/admin
```

### **Login Required:**
- You need to be authenticated to access the admin panel
- Uses Laravel Jetstream authentication
- Protected by Filament's `Authenticate` middleware

### **✅ FIXED: Dashboard Route Issue**
- **Issue:** "Route [filament.admin.pages.dashboard] not defined" error
- **Solution:** Removed explicit `Pages\Dashboard::class` registration from AdminPanelProvider
- **Result:** Filament now uses its default dashboard automatically
- **Status:** Routes cleared and working properly

---

## 📁 Project Structure

### **Filament Admin Panel** (`/admin`)

#### **Pages:**
1. **Dashboard** (Default Filament Dashboard)
   - Path: `/admin`
   - Standard Filament dashboard with widgets

2. **Bulk Video Upload** ⭐ NEW
   - Path: `/admin/bulk-video-upload`
   - Icon: Cloud upload (heroicon-o-cloud-arrow-up)
   - Navigation Sort: 1 (appears first)
   - Upload multiple videos at once (up to 50 files)
   - Automatic barcode generation from filename
   - Duplicate detection (skips existing videos)
   - Supported formats: MP4, AVI, MOV, WMV, FLV, MKV, WEBM
   - Max file size: 500MB per video
   - View: `resources/views/filament/pages/bulk-video-upload.blade.php`
   - Class: `app/Filament/Pages/BulkVideoUpload.php`

3. **Top 20 Videos Page**
   - Path: `/admin/top-20-videos`
   - Icon: Rectangle stack (heroicon-o-rectangle-stack)
   - Navigation Sort: 2
   - Interactive table showing favorite videos
   - Columns: Barcode, Filename, Created At
   - View: `resources/views/filament/pages/top-20-videos.blade.php`
   - Class: `app/Filament/Pages/Top20Videos.php`

4. **Player Page** 
   - Path: `/admin/player`
   - Icon: Play icon (heroicon-o-play)
   - Navigation Sort: 3
   - Shows top 20 favorite videos
   - View: `resources/views/filament/pages/player.blade.php`
   - Class: `app/Filament/Pages/Player.php`

#### **Resources:**
1. **Video Resource**
   - Full CRUD for videos
   - Routes:
     - `/admin/videos` - List all videos
     - `/admin/videos/create` - Create new video (with file upload) ⭐ UPDATED
     - `/admin/videos/{id}/edit` - Edit video
   - **Create Form Fields:**
     - Barcode (required, max 255 chars) - Unique identifier
     - Video File Upload ⭐ NEW - Upload single video file
       - Accepts: MP4, AVI, MOV, WMV, FLV, MKV, WEBM
       - Max size: 500MB
       - Auto-fills filename from uploaded file
     - Filename (auto-filled, disabled) - Set automatically from upload
     - Is Favorite (toggle) - Mark as favorite video
   - **Edit Form Fields:**
     - Barcode (editable)
     - Filename (read-only)
     - Is Favorite (toggle)
   - Table columns: Barcode, Filename, Created At, Updated At, Is Favorite
   - Actions: Edit, Delete (bulk)

#### **Widgets:**
- Account Widget
- Filament Info Widget

---

## 🗄️ Database Models

### **Video Model** (`app/Models/Video.php`)
**Fields:**
- `id` (primary key)
- `title`
- `barcode`
- `is_favorite` (boolean)
- `filename`
- `created_at`
- `updated_at`

**Relationships:**
- `belongsTo(Playlist::class)`
- `hasMany(Favorite::class)`

### **Other Models:**
- `User` - Authentication (Jetstream)
- `Playlist` - Video playlists
- `Favorite` - Favorite videos tracking

---

## 🛣️ Routes

### **Web Routes** (`routes/web.php`)

**Public:**
- `/` - Welcome page

**Authenticated:**
- `/dashboard` - User dashboard (Jetstream)

**Video Management:**
- `/barcode` (GET/POST) - Barcode input
- `/upload` (GET) - Upload form
- `/upload` (POST) - Handle upload
- `/delete` (POST) - Delete video
- `/play/{filename}` - Play video
- `/idleRedirect` - Idle redirect with favorite videos loop

**Admin:**
- `/admin` - Admin route (redirects to Filament)

---

## 🎨 Frontend Stack

- **Laravel Blade** - Templating
- **Tailwind CSS** - Styling
- **Vite** - Asset bundling
- **Livewire** - Dynamic components (via Filament)

---

## 🔧 Key Features

### **Video Management:**
1. **Upload videos** - Three methods available:
   - **Bulk Upload** (NEW): Upload up to 50 videos at once via `/admin/bulk-video-upload`
   - **Single Upload**: Upload one video at a time via `/admin/videos/create`
   - **Command Line Sync**: Sync videos from storage folder using `php artisan videos:sync`
2. Mark videos as favorites
3. Play videos
4. Delete videos
5. View top 20/50 favorite videos
6. Search and filter videos by barcode or filename

### **Admin Panel (Filament):**
1. Full CRUD for videos
2. Interactive tables with search/sort
3. Custom pages for video player and top videos
4. Dashboard with widgets
5. Responsive design

### **Authentication:**
- Laravel Jetstream
- Two-factor authentication support
- API token management
- Profile management

---

## 📂 Important Files

### **Configuration:**
- `app/Providers/Filament/AdminPanelProvider.php` - Filament panel configuration
- `config/filament.php` - Filament settings
- `config/jetstream.php` - Jetstream settings

### **Controllers:**
- `app/Http/Controllers/VideoController.php` - Video operations

### **Traits:**
- `app/Traits/FetchesFavoriteVideos.php` - Reusable logic for fetching top 50 favorite videos

### **Views:**
- `resources/views/dashboard.blade.php` - User dashboard
- `resources/views/welcome.blade.php` - Landing page
- `resources/views/filament/pages/player.blade.php` - Video player page
- `resources/views/filament/pages/top-20-videos.blade.php` - Top videos table

---

## 🚀 Getting Started

### **1. Start the Development Server:**
```bash
php artisan serve
```

### **2. Access the Application:**
- Main site: `http://localhost:8000`
- Admin panel: `http://localhost:8000/admin`

### **3. Login:**
- You need a user account to access the admin panel
- Register at `/register` or login at `/login`

### **4. Navigate the Admin Panel:**
Once logged in to `/admin`, you'll see:
- **Dashboard** - Overview with widgets
- **Bulk Upload Videos** ⭐ NEW - Upload multiple videos at once
- **Videos** - Manage all videos (CRUD with file upload)
- **Top 20 Videos** - View favorite videos in a table
- **Player** - Video player interface

---

## 📤 How to Upload Videos

### **Method 1: Bulk Upload (Recommended for Multiple Videos)**
1. Go to `/admin/bulk-video-upload`
2. Click "Select files" or drag and drop videos
3. Select up to 50 video files
4. Click "Upload Videos"
5. Videos are automatically added with barcodes from filenames

**Features:**
- Upload up to 50 videos at once
- Automatic duplicate detection
- Progress tracking
- Automatic barcode generation

### **Method 2: Single Video Upload**
1. Go to `/admin/videos`
2. Click "New Video" button
3. Enter a barcode (unique identifier)
4. Upload a video file
5. Filename is automatically set
6. Optionally mark as favorite
7. Click "Create"

**Features:**
- Upload one video at a time
- Manual barcode entry
- Immediate feedback

### **Method 3: Command Line Sync**
If you have videos already in `storage/app/public/videos`:
```bash
php artisan videos:sync
```

**Features:**
- Syncs all videos from storage folder
- Generates barcodes from filenames
- Skips existing videos
- Shows progress and summary

---

## 🎯 Current State

**Database:** 258+ videos synced and ready

**Upload Features Available:**
1. ✅ Bulk upload page at `/admin/bulk-video-upload`
2. ✅ Single video upload at `/admin/videos/create`
3. ✅ Command line sync with `php artisan videos:sync`

**End User Access:**
- End users can now upload videos directly through the admin panel
- No need for manual file management or command line access
- Automatic barcode generation and duplicate detection

---

## 🔍 Quick Navigation

**To work on video uploads:**
1. **Bulk upload page:** `app/Filament/Pages/BulkVideoUpload.php`
2. **Bulk upload view:** `resources/views/filament/pages/bulk-video-upload.blade.php`
3. **Single upload logic:** `app/Filament/Resources/VideoResource/Pages/CreateVideo.php`
4. **Video resource form:** `app/Filament/Resources/VideoResource.php`

**To work on the admin panel:**
1. **Panel config:** `app/Providers/Filament/AdminPanelProvider.php`
2. **Video resource:** `app/Filament/Resources/VideoResource.php`
3. **Custom pages:** `app/Filament/Pages/`
4. **Views:** `resources/views/filament/pages/`

**To work on video functionality:**
1. **Controller:** `app/Http/Controllers/VideoController.php`
2. **Model:** `app/Models/Video.php`
3. **Routes:** `routes/web.php`
4. **Sync command:** `app/Console/Commands/SyncVideosFromStorage.php`

**To work on authentication:**
1. **Jetstream config:** `config/jetstream.php`
2. **Auth views:** `resources/views/auth/`
3. **User model:** `app/Models/User.php`

---

## 📝 Notes

- The project uses **Filament v3** (based on the panel configuration)
- Primary color is set to **Amber**
- Videos are stored in `storage/app/public/videos`
- Videos are filtered by `is_favorite = true` for the top videos feature
- The trait fetches top **50** videos (not 20, despite the page name)
- Authentication is required for all admin routes
- The project includes idle redirect functionality for looping favorite videos
- **File uploads are handled by Filament's FileUpload component**
- **Duplicate videos are automatically detected and skipped**
- **Barcodes are auto-generated from filenames (without extension)**

---

## 🎬 Next Steps

**For End Users:**
1. Access `/admin/bulk-video-upload` to upload your 100+ new videos
2. Use the bulk upload feature to add multiple videos at once
3. Mark important videos as favorites
4. Use the search and filter features to find videos

**For Developers:**
If you want to continue development, you might want to:
1. Add video preview/thumbnail generation
2. Add video metadata (duration, size, resolution)
3. Implement video categories or tags
4. Add video compression/optimization
5. Create video playlists management
6. Add video analytics (view counts, etc.)
7. Implement video streaming optimization

---

## 🐛 Troubleshooting

### **Issue: "Route [filament.admin.pages.dashboard] not defined"**
**Solution:** This was caused by registering a non-existent Dashboard class in AdminPanelProvider. Fixed by removing the explicit registration and letting Filament use its default dashboard.

### **Issue: Videos not showing in /admin/videos**
**Problem:** Video files existed in `storage/app/public/videos` but weren't in the database.

**Solution:** 
1. Added `filename` and `playlist_id` to the `$fillable` array in `app/Models/Video.php`
2. Created a sync command: `app/Console/Commands/SyncVideosFromStorage.php`
3. Ran the sync command to populate the database

**To sync videos from storage to database:**
```bash
php artisan videos:sync
```

**To force re-sync (update existing records):**
```bash
php artisan videos:sync --force
```

### **Commands Used:**
```bash
php artisan optimize:clear  # Clear all caches
php artisan route:list --path=admin  # Verify admin routes
php artisan videos:sync  # Sync videos from storage to database
```

---

**Last Updated:** Based on current project scan + dashboard fix
**Framework:** Laravel with Filament Admin Panel
**Authentication:** Laravel Jetstream
