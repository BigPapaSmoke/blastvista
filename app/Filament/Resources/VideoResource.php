<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Models\Video;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('barcode')
                    ->required()
                    ->maxLength(255)
                    ->label('Product UPC Barcode')
                    ->helperText('Enter the actual UPC barcode from the firework product. This is what you scan to play the video.')
                    ->placeholder('e.g., 012345678901')
                    ->unique(ignoreRecord: true),
                Forms\Components\FileUpload::make('video_file')
                    ->label('Video File')
                    ->disk('public')
                    ->directory('videos')
                    ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/mkv', 'video/webm'])
                    ->maxSize(512000) // 500MB max
                    ->helperText('Upload a video file (MP4, AVI, MOV, WMV, FLV, MKV, WEBM). Max size: 500MB.')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->hidden(fn (string $operation): bool => $operation === 'edit')
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $filename = $state->getClientOriginalName();
                            // Auto-fill filename from uploaded file
                            $set('filename', $filename);
                        }
                    }),
                Forms\Components\TextInput::make('filename')
                    ->maxLength(255)
                    ->label('Filename (Stored in system)')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Automatically set from uploaded file. This is the actual file stored on the server.'),
                Forms\Components\Toggle::make('is_favorite')
                    ->label('Mark as Favorite')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('barcode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('filename')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_favorite')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
            // 'player' => Pages\Player::route('/player'),
             // Add this line to register the Player page
        ];
    }
}
