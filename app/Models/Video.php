<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'barcode',
        'filename',
        'is_favorite',
        'playlist_id',
    ];

    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
