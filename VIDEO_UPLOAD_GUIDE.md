# 📤 Video Upload Guide for End Users

## ⚠️ IMPORTANT: Understanding Barcodes

### **Your Filename = Your Barcode!**

When you upload a video, the **filename becomes the barcode** that you'll scan or type to play the video.

**Example:**
- Upload file: `Firework_Show_2024.mp4`
- Barcode created: `Firework_Show_2024`
- To play: Scan or type `Firework_Show_2024`

### **Best Practices for Naming Files:**
✅ **DO:**
- Use descriptive names: `Summer_Finale_2024.mp4`
- Use underscores or spaces: `Big_Show.mp4` or `Big Show.mp4`
- Keep it simple and memorable
- Use names that are easy to scan/type

❌ **DON'T:**
- Use special characters: `Show@#$%.mp4`
- Use very long names
- Use confusing abbreviations

---

## Quick Start

### 🚀 Fastest Way: Bulk Upload (Recommended)

**Perfect for uploading 100+ videos at once!**

1. **Login to Admin Panel**
   - Go to: `http://your-site.com/admin`
   - Enter your username and password

2. **Navigate to Bulk Upload**
   - Click on **"Bulk Upload Videos"** in the sidebar
   - Or go directly to: `http://your-site.com/admin/bulk-video-upload`

3. **Prepare Your Files**
   - ⚠️ **IMPORTANT**: Rename your video files BEFORE uploading
   - Use descriptive names that will be easy to scan/type
   - Example: `Firework_Display_2024.mp4`, `Grand_Finale.mp4`, etc.

4. **Upload Your Videos**
   - Click **"Select files"** or drag and drop videos into the upload area
   - Select up to **50 videos** at once
   - Supported formats: MP4, AVI, MOV, WMV, FLV, MKV, WEBM
   - Max file size: **500MB per video**

5. **Submit**
   - Click the **"Upload Videos"** button
   - Wait for the upload to complete
   - You'll see a success message with the number of videos uploaded

6. **Repeat if Needed**
   - If you have more than 50 videos, repeat the process
   - The system will automatically skip any duplicate videos

---

## 📋 Upload Methods Comparison

| Method | Best For | Max Files | Features |
|--------|----------|-----------|----------|
| **Bulk Upload** | 100+ videos | 50 at once | Fast, automatic barcodes, duplicate detection |
| **Single Upload** | 1-5 videos | 1 at a time | Custom barcodes, mark as favorite immediately |
| **Command Line** | Technical users | Unlimited | Sync from storage folder |

---

## 🎯 Step-by-Step: Bulk Upload

### Step 1: Prepare Your Video Files
⚠️ **CRITICAL STEP - Do this BEFORE uploading!**
- Rename your video files with descriptive names
- These names will become the barcodes you scan to play videos
- Example good names:
  - `Firework_Show_2024.mp4`
  - `Grand_Finale.mp4`
  - `Summer_Display.mp4`
  - `New_Years_Eve.mp4`

### Step 2: Access the Upload Page
- Login to `/admin`
- Look for **"Bulk Upload Videos"** in the left sidebar (should be at the top)
- Click to open the upload page
- Read the **"Important: Filename = Barcode"** warning box

### Step 3: Select Your Videos
- Click the **"Select files"** button
- Or drag and drop videos directly into the upload area
- You can select multiple files at once (up to 50)

### Step 4: Review Your Selection
- You'll see thumbnails/names of selected videos
- **Double-check the filenames** - these become your barcodes!
- You can remove any video by clicking the X button
- You can reorder videos if needed

### Step 5: Upload
- Click the **"Upload Videos"** button
- Wait for the progress bar to complete
- Don't close the browser during upload!

### Step 6: Confirmation
- You'll see a success message: "Videos uploaded successfully!"
- The message will show how many videos were uploaded
- If any videos were skipped (duplicates), you'll see that too

---

## 🎬 Step-by-Step: Single Video Upload

### When to Use This:
- Uploading just 1-5 videos
- Need to set a custom barcode (different from filename)
- Want to mark as favorite immediately

### Steps:
1. Go to `/admin/videos`
2. Click **"New Video"** button (top right)
3. Fill in the form:
   - **Barcode**: This will auto-fill from your filename, but you can change it
     - Example: If you upload `Show.mp4`, barcode will be `Show`
     - You can change it to something else like `Summer_Show_2024`
   - **Video File**: Click to upload your video file
   - **Filename**: Auto-filled from your upload (this is the stored filename)
   - **Mark as Favorite**: Toggle if this is a favorite video
4. Click **"Create"**
5. Done! Your video is now in the system

### 💡 Pro Tip:
If you want a custom barcode:
1. Leave the barcode field empty initially
2. Upload your video file
3. The barcode will auto-fill from the filename
4. Edit the barcode to whatever you want
5. Click "Create"

---

## ✅ What Happens After Upload?

### Automatic Processing:
1. **File Storage**: Video is saved to `storage/app/public/videos`
2. **Database Entry**: Video record is created with:
   - Barcode (from filename or your input)
   - Filename
   - Upload date
   - Favorite status
3. **Duplicate Check**: System checks if video already exists
4. **Immediate Availability**: Video appears in the videos list right away

### Where to Find Your Videos:
- **All Videos**: `/admin/videos` - See complete list
- **Favorites**: `/admin/top-20-videos` - See favorite videos
- **Player**: `/admin/player` - Play videos

---

## 🔍 Managing Your Videos

### View All Videos:
1. Go to `/admin/videos`
2. Use the search bar to find specific videos
3. Click column headers to sort
4. Use pagination to browse through pages

### Edit a Video:
1. Go to `/admin/videos`
2. Find your video in the list
3. Click the **Edit** icon (pencil)
4. Update barcode or favorite status
5. Click **"Save"**

### Delete a Video:
1. Go to `/admin/videos`
2. Select videos using checkboxes
3. Click **"Delete"** in the bulk actions dropdown
4. Confirm deletion

### Mark as Favorite:
1. Go to `/admin/videos`
2. Click **Edit** on the video
3. Toggle **"Mark as Favorite"** to ON
4. Click **"Save"**

---

## ⚠️ Important Notes

### File Requirements:
- ✅ **Supported formats**: MP4, AVI, MOV, WMV, FLV, MKV, WEBM
- ✅ **Max file size**: 500MB per video
- ✅ **Max files per upload**: 50 videos at once
- ❌ **Not supported**: Other video formats, files over 500MB

### Naming Conventions:
- **Barcode is auto-generated** from filename (without extension)
- Example: `Summer_Vacation_2024.mp4` → Barcode: `Summer_Vacation_2024`
- Use descriptive filenames for better organization
- **Barcode = What you scan/enter to play the video**
- **Filename = What gets stored on the server**

### Duplicate Detection:
- System checks for existing videos by **barcode** and **filename**
- Duplicate videos are **automatically skipped**
- You'll see a message: "Skipped: X (already exist)"

### Upload Tips:
- 📶 **Use a stable internet connection** for large uploads
- 🕐 **Don't close the browser** during upload
- 📁 **Organize files before uploading** for easier management
- 🔄 **Upload in batches** if you have 100+ videos (50 at a time)

---

## 🆘 Troubleshooting

### Upload Failed?
**Problem**: "Upload failed" error message

**Solutions**:
1. Check your internet connection
2. Verify file size is under 500MB
3. Ensure file format is supported
4. Try uploading fewer files at once
5. Refresh the page and try again

### Video Not Appearing?
**Problem**: Uploaded video doesn't show in the list

**Solutions**:
1. Refresh the videos page (`/admin/videos`)
2. Check if it was marked as duplicate
3. Use the search function to find it
4. Clear your browser cache

### Slow Upload?
**Problem**: Upload taking too long

**Solutions**:
1. Check your internet speed
2. Upload fewer videos at once (try 10-20 instead of 50)
3. Compress videos before uploading if possible
4. Upload during off-peak hours

### Can't Access Upload Page?
**Problem**: Don't see "Bulk Upload Videos" option

**Solutions**:
1. Make sure you're logged in
2. Check if you have admin permissions
3. Clear browser cache and refresh
4. Contact your system administrator

---

## 📞 Need Help?

If you encounter any issues:
1. Check this guide first
2. Try the troubleshooting steps
3. Contact your system administrator
4. Provide details: what you were doing, error messages, screenshots

---

## 🎉 Success Tips

### For Best Results:
1. ✅ **Organize files first** - Name them clearly before uploading
2. ✅ **Upload in batches** - Don't try to upload 200 videos at once
3. ✅ **Use bulk upload** - Much faster than one-by-one
4. ✅ **Check for duplicates** - Review the list before uploading
5. ✅ **Mark favorites** - Use the favorite feature for important videos
6. ✅ **Use search** - Find videos quickly with the search function
7. ✅ **Plan your barcodes** - Filenames become barcodes, so use descriptive names!

### Workflow Example:
**Uploading 150 new videos:**
1. Batch 1: Upload 50 videos → Wait for completion
2. Batch 2: Upload 50 videos → Wait for completion
3. Batch 3: Upload 50 videos → Wait for completion
4. Review all videos in `/admin/videos`
5. Mark important ones as favorites
6. Done! 🎉

---

**Last Updated**: Based on current system configuration
**System**: Laravel + Filament Admin Panel
**Version**: 1.0
