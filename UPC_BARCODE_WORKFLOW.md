# 🏷️ UPC Barcode Workflow for Firework Videos

## Understanding the System

Your firework products have **actual UPC barcodes** (like retail products). These UPC barcodes are what you scan to trigger the video player, NOT the filename.

### The Flow:
1. **Product has UPC barcode** → e.g., `012345678901`
2. **Scan UPC barcode** → Scanner reads `012345678901`
3. **System looks up video** → Finds video with barcode `012345678901`
4. **Video plays** → Uses the filename to stream from storage

---

## 📤 Uploading 100+ Videos with UPC Barcodes

### **Recommended Workflow:**

#### Step 1: Upload Video Files to Server
Place all your video files in the storage directory:
```bash
storage/app/public/videos/
```

You can do this via:
- FTP/SFTP
- File manager
- Command line

#### Step 2: Sync Videos to Database
Run the sync command:
```bash
php artisan videos:sync
```

**What happens:**
- Scans all video files in `storage/app/public/videos`
- Creates database entries for each video
- Assigns **temporary barcodes** like `TEMP_filename`
- Shows progress and summary

**Example output:**
```
Found 258 video files
[████████████████████] 100%

Sync completed!
┌────────┬───────┐
│ Status │ Count │
├────────┼───────┤
│ Synced │ 258   │
│ Skipped│ 0     │
│ Errors │ 0     │
└────────┴───────┘

⚠️  IMPORTANT: Videos synced with TEMPORARY barcodes!
   You MUST edit each video in /admin/videos to add the correct UPC barcode.
```

#### Step 3: Add Real UPC Barcodes
1. Go to `/admin/videos` in your browser
2. You'll see all 258 videos with temporary barcodes (TEMP_filename)
3. For each video:
   - Click the **Edit** button
   - Replace the temporary barcode with the **actual product UPC**
   - Example: Change `TEMP_Firework_Show` to `012345678901`
   - Click **Save**

#### Step 4: Test
1. Scan a product UPC barcode
2. Video should play automatically

---

## 🎯 Single Video Upload (For 1-5 Videos)

### When to Use:
- Adding just a few new videos
- You have the UPC barcode ready
- Want immediate results

### Steps:
1. Go to `/admin/videos/create`
2. **Enter Product UPC Barcode** (required)
   - Example: `012345678901`
   - This is the barcode from your firework product
3. **Upload Video File**
   - Click to select file
   - Filename is automatically saved
4. **Mark as Favorite** (optional)
5. Click **Create**

---

## 🔍 Managing Videos

### View All Videos:
- Go to `/admin/videos`
- Search by barcode or filename
- Sort by any column
- Use pagination

### Edit a Video:
1. Find video in `/admin/videos`
2. Click **Edit** icon
3. Update the UPC barcode
4. Click **Save**

### Bulk Edit (Future Enhancement):
Currently, you need to edit videos one by one. Consider creating a CSV import feature for bulk barcode updates if needed.

---

## 📋 Example Workflow for 100 New Videos

### Scenario:
You have 100 new firework product videos to add, each with a UPC barcode.

### Process:

**Day 1: Upload Files**
1. Copy all 100 video files to `storage/app/public/videos` via FTP
2. Run `php artisan videos:sync`
3. Verify 100 videos are in database with TEMP barcodes

**Day 2-3: Add UPC Barcodes**
1. Open `/admin/videos`
2. Edit each video to add real UPC barcode
3. Work through the list systematically
4. Use search to find specific videos

**Day 4: Test**
1. Scan various product UPC barcodes
2. Verify correct videos play
3. Mark favorites as needed

---

## 🔧 Technical Details

### Database Structure:
```
videos table:
- id (primary key)
- barcode (string, unique) ← Product UPC barcode
- filename (string) ← Actual file in storage
- is_favorite (boolean)
- created_at, updated_at
```

### File Storage:
```
storage/app/public/videos/
├── Firework_Show_2024.mp4
├── Grand_Finale.mp4
├── Summer_Display.mp4
└── ... (more videos)
```

### Barcode Lookup:
```php
// When barcode is scanned:
$video = Video::where('barcode', $scannedBarcode)->first();

// If found, play video using filename:
return response()->file(storage_path('app/public/videos/' . $video->filename));
```

---

## ⚠️ Important Notes

### Temporary Barcodes:
- Format: `TEMP_filename`
- Example: `TEMP_Firework_Show_2024`
- **Will NOT work with barcode scanner**
- Must be replaced with real UPC barcodes

### UPC Barcode Format:
- Usually 12 digits: `012345678901`
- Sometimes 13 digits (EAN): `0123456789012`
- Must match exactly what's on the product
- Case-sensitive if alphanumeric

### Unique Barcodes:
- Each barcode must be unique
- System prevents duplicate barcodes
- One barcode = One video

### Filename vs Barcode:
- **Filename**: What's stored on server (e.g., `Firework_Show.mp4`)
- **Barcode**: What you scan to play (e.g., `012345678901`)
- They are completely separate!

---

## 🆘 Troubleshooting

### Video Won't Play When Scanned:
**Problem**: Scan barcode but video doesn't play

**Solutions**:
1. Check if barcode in database matches product UPC exactly
2. Verify video file exists in `storage/app/public/videos`
3. Check for typos in barcode entry
4. Ensure barcode doesn't start with `TEMP_`

### Can't Find Video in List:
**Problem**: Video uploaded but can't find it

**Solutions**:
1. Use search function in `/admin/videos`
2. Check if it has a TEMP barcode
3. Sort by created_at to see newest videos
4. Verify file is in storage folder

### Duplicate Barcode Error:
**Problem**: Can't save video - barcode already exists

**Solutions**:
1. Check if another video has the same UPC
2. Verify you're entering the correct UPC
3. Each product should have unique UPC
4. If products share UPC, you may need different approach

---

## 💡 Tips for Efficiency

### Batch Processing:
1. Group videos by product line
2. Have UPC list ready before editing
3. Use consistent naming for files
4. Keep a spreadsheet: filename → UPC barcode

### Organization:
1. Name files descriptively
2. Use product names in filenames
3. Mark popular videos as favorites
4. Regular backups of database

### Quality Control:
1. Test each video after adding UPC
2. Verify barcode scans correctly
3. Check video plays smoothly
4. Update favorites list regularly

---

## 📞 Need Help?

If you encounter issues:
1. Check this guide first
2. Verify file permissions on storage folder
3. Check Laravel logs: `storage/logs/laravel.log`
4. Test with a single video first
5. Contact system administrator if needed

---

**Last Updated**: Based on UPC barcode requirements
**System**: Laravel + Filament Admin Panel
**Storage**: `storage/app/public/videos`
**Database**: SQLite/MySQL (videos table)
