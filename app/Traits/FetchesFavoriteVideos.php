<?php
// app/Traits/FetchesFavoriteVideos.php

namespace App\Traits;

use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;

trait FetchesFavoriteVideos
{
    public function getTop20FavoriteVideos(): Builder
    {
        return Video::query()
                    ->where('is_favorite', true)
                    ->orderBy('created_at', 'desc')
                    ->limit(50);
    }
}
