<?php
// app/Filament/Pages/Player.php

namespace App\Filament\Pages;

use App\Traits\FetchesFavoriteVideos;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class Player extends Page
{
    use FetchesFavoriteVideos;

    protected static ?string $navigationIcon = 'heroicon-o-play';
    protected static ?int $navigationSort = 3; // Set a higher sort value to place it at the bottom
    protected static string $view = 'filament.pages.player';

    public $videos;

    public function mount()
    {
        $this->videos = $this->getTop20FavoriteVideos()->get();
    }
}
