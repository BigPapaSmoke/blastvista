<?php
// app/Filament/Pages/Player.php

namespace App\Filament\Pages;

use BackedEnum;
use App\Traits\FetchesFavoriteVideos;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class Player extends Page
{
    use FetchesFavoriteVideos;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play';
    protected static ?int $navigationSort = 3; // Set a higher sort value to place it at the bottom
    protected string $view = 'filament.pages.player';

    public $videos;

    public function mount()
    {
        $this->videos = $this->getTop20FavoriteVideos()->get();
    }
}
