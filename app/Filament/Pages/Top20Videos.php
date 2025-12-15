<?php
// app/Filament/Pages/Top20Videos.php

namespace App\Filament\Pages;

use App\Traits\FetchesFavoriteVideos;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;

class Top20Videos extends Page implements Tables\Contracts\HasTable
{
    use InteractsWithTable, FetchesFavoriteVideos;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 2; // Set a higher sort value to place it at the bottom
    protected static string $view = 'filament.pages.top-20-videos';

    protected function getTableQuery(): Builder
    {
        return $this->getTop20FavoriteVideos();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('barcode')->label('Barcode'),
            Tables\Columns\TextColumn::make('filename')->label('Filename'),
            Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime(),
        ];
    }
}
