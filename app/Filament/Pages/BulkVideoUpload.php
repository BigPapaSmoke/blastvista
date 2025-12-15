<?php

namespace App\Filament\Pages;

use App\Models\Video;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class BulkVideoUpload extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-up';
    
    protected static ?string $navigationLabel = 'Bulk Upload Videos';
    
    protected static ?string $title = 'Bulk Video Upload';
    
    protected static ?int $navigationSort = 1;
    
    protected static string $view = 'filament.pages.bulk-video-upload';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Important: UPC Barcodes Required')
                    ->description('Each video must be linked to its product UPC barcode')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('barcode_warning')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                                Bulk Upload Limitation
                                            </h3>
                                            <div class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                                                <p class="font-semibold mb-2">For bulk uploads with 100+ videos, use the command line sync method:</p>
                                                <ol class="list-decimal list-inside space-y-1 ml-2">
                                                    <li>Place video files in <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">storage/app/public/videos</code></li>
                                                    <li>Run: <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">php artisan videos:sync</code></li>
                                                    <li>Then edit each video in the Videos list to add the correct UPC barcode</li>
                                                </ol>
                                                <p class="mt-3"><strong>For uploading with barcodes through the UI:</strong></p>
                                                <p>Use the single video upload at <strong>/admin/videos/create</strong> where you can enter the UPC barcode for each video.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ')),
                    ]),
                Section::make('Alternative: Single Video Upload')
                    ->description('Upload videos one at a time with their UPC barcodes')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('single_upload_info')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div class="space-y-2 text-sm">
                                    <p><strong>Recommended for videos with UPC barcodes:</strong></p>
                                    <ol class="list-decimal list-inside space-y-1 ml-2">
                                        <li>Go to <a href="/admin/videos/create" class="text-blue-600 hover:underline">/admin/videos/create</a></li>
                                        <li>Enter or scan the product UPC barcode</li>
                                        <li>Upload the video file</li>
                                        <li>Mark as favorite if needed</li>
                                        <li>Click Create</li>
                                    </ol>
                                    <p class="mt-3"><strong>This ensures each video is correctly linked to its product barcode.</strong></p>
                                </div>
                            ')),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }
    
    protected function getFormActions(): array
    {
        return [];
    }
}
