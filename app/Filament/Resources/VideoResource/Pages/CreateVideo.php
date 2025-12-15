<?php

namespace App\Filament\Resources\VideoResource\Pages;

use App\Filament\Resources\VideoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateVideo extends CreateRecord
{
    protected static string $resource = VideoResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle the video file upload
        if (isset($data['video_file'])) {
            // The file is already uploaded by Filament to storage/app/public/videos
            // We just need to set the filename
            $data['filename'] = basename($data['video_file']);
            
            // Remove the video_file key as it's not a database column
            unset($data['video_file']);
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Video uploaded successfully!';
    }
}
