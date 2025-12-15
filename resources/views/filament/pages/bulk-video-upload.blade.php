<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Bulk Video Upload
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Upload multiple video files at once. Each video will be automatically added to your video library.
                </p>
            </div>
            
            <form wire:submit="upload">
                {{ $this->form }}
                
                <div class="mt-6 flex justify-end gap-3">
                    <x-filament::button
                        type="submit"
                        color="primary"
                        icon="heroicon-o-cloud-arrow-up"
                    >
                        Upload Videos
                    </x-filament::button>
                </div>
            </form>
        </div>
        
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        Recommended Workflow for 100+ Videos
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <p class="font-semibold mb-2">Best approach for bulk uploads:</p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li><strong>Upload files to server:</strong> Place all video files in <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">storage/app/public/videos</code></li>
                            <li><strong>Sync to database:</strong> Run <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">php artisan videos:sync</code></li>
                            <li><strong>Add UPC barcodes:</strong> Go to <a href="/admin/videos" class="text-blue-600 hover:underline font-semibold">/admin/videos</a> and edit each video to add the correct product UPC barcode</li>
                        </ol>
                        <p class="mt-3"><strong>Why this works:</strong> Videos are synced with temporary barcodes (TEMP_filename), then you can batch-edit them to add the real UPC barcodes from your products.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                Alternative: Sync from Storage
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                If you have videos already in the storage folder, you can sync them using the command:
            </p>
            <code class="block bg-gray-900 text-green-400 p-3 rounded text-sm">
                php artisan videos:sync
            </code>
        </div>
    </div>
</x-filament-panels::page>
