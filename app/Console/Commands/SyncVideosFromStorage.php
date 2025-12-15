<?php

namespace App\Console\Commands;

use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncVideosFromStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:sync {--force : Force sync even if videos exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync video files from storage/app/public/videos to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting video sync from storage...');
        
        // Get all video files from storage
        $files = Storage::disk('public')->files('videos');
        
        if (empty($files)) {
            $this->warn('No video files found in storage/app/public/videos');
            return Command::FAILURE;
        }
        
        $this->info('Found ' . count($files) . ' video files');
        
        $synced = 0;
        $skipped = 0;
        $errors = 0;
        
        $progressBar = $this->output->createProgressBar(count($files));
        $progressBar->start();
        
        foreach ($files as $file) {
            $filename = basename($file);
            
            // Skip non-video files
            if (!$this->isVideoFile($filename)) {
                $skipped++;
                $progressBar->advance();
                continue;
            }
            
            try {
                // Generate TEMPORARY barcode from filename (will need to be updated with actual UPC)
                $tempBarcode = 'TEMP_' . pathinfo($filename, PATHINFO_FILENAME);
                
                // Check if video already exists by filename only
                $existingVideo = Video::where('filename', $filename)->first();
                
                if ($existingVideo && !$this->option('force')) {
                    $skipped++;
                } else {
                    if ($existingVideo && $this->option('force')) {
                        // Update existing video (don't overwrite barcode if it's not a temp one)
                        if (!str_starts_with($existingVideo->barcode, 'TEMP_')) {
                            // Keep existing barcode
                            $existingVideo->update([
                                'filename' => $filename,
                            ]);
                        } else {
                            // Update temp barcode
                            $existingVideo->update([
                                'filename' => $filename,
                                'barcode' => $tempBarcode,
                            ]);
                        }
                        $synced++;
                    } else {
                        // Create new video entry with temporary barcode
                        Video::create([
                            'barcode' => $tempBarcode,
                            'filename' => $filename,
                            'is_favorite' => false,
                        ]);
                        $synced++;
                    }
                }
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error('Error syncing ' . $filename . ': ' . $e->getMessage());
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info('Sync completed!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Synced', $synced],
                ['Skipped', $skipped],
                ['Errors', $errors],
                ['Total Files', count($files)],
            ]
        );
        
        $this->info('Total videos in database: ' . Video::count());
        
        // Important warning about barcodes
        $this->newLine();
        $this->warn('⚠️  IMPORTANT: Videos synced with TEMPORARY barcodes!');
        $this->warn('   You MUST edit each video in /admin/videos to add the correct UPC barcode.');
        $this->warn('   Temporary barcodes start with "TEMP_" and will not work with your barcode scanner.');
        $this->newLine();
        
        return Command::SUCCESS;
    }
    
    /**
     * Check if file is a video file
     */
    private function isVideoFile($filename): bool
    {
        $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', 'm4v'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        return in_array($extension, $videoExtensions);
    }
}
