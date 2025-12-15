<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoController;
use App\Models\Video;

Route::get('/', function () {
    $videos = Video::where('is_favorite', true)->orderBy('created_at', 'desc')->limit(50)->get();
    return view('idleRedirect', ['videos' => $videos]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// VideoController Routes

Route::match(['get', 'post'], '/barcode', [VideoController::class, 'barcodeInput'])->name('barcode.input');

Route::get('/upload', [VideoController::class, 'upload'])->name('video.upload');
Route::post('/upload', [VideoController::class, 'handleUpload'])->name('video.handle_upload');
Route::post('/delete', [VideoController::class, 'delete'])->name('video.delete');
Route::get('/play/{filename}', [VideoController::class, 'play'])->name('video.play');

Route::get('/idleRedirect', function () {
    $videos = Video::where('is_favorite', true)->orderBy('created_at', 'desc')->limit(50)->get();
    return view('idleRedirect', ['videos' => $videos]);
})->name('idleRedirect');
