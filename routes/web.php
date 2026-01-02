<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\VideoController;
use App\Models\Video;

/*
|--------------------------------------------------------------------------
| Public Idle Player (Main Screen)
|--------------------------------------------------------------------------
| Displays looping favorite videos and listens for barcode scans.
| Videos are streamed directly from the VPS (Nginx), not Laravel.
*/

Route::get('/', function () {

    $videos = Video::where('is_favorite', true)
        ->orderBy('created_at', 'desc')
        ->limit(50)
        ->get()
        ->map(function ($video) {
            // Centralized streaming URL (VPS-hosted videos)
            $video->stream_url =
                'http://89.40.11.5/videos/' . rawurlencode(trim($video->filename));
            return $video;
        });

    return view('idleRedirect', [
        'videos' => $videos
    ]);
})->name('idle');

/*
|--------------------------------------------------------------------------
| Authenticated Dashboard (Jetstream)
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

});

/*
|--------------------------------------------------------------------------
| Barcode Handling (AJAX / JSON)
|--------------------------------------------------------------------------
| Barcode scans return a video URL immediately.
| No redirects. No page reloads.
*/

Route::post('/barcode', function (Request $request) {

    $barcode = trim($request->input('barcode'));

    if (!$barcode) {
        return response()->json(['error' => 'Empty barcode'], 400);
    }

    $video = Video::where('barcode', $barcode)->first();

    if (!$video) {
        return response()->json(['error' => 'Video not found'], 404);
    }

    return response()->json([
        'video_url' =>
            'http://89.40.11.5/videos/' . rawurlencode(trim($video->filename))
    ]);

})->name('barcode.input');

/*
|--------------------------------------------------------------------------
| Video Admin / Management Routes
|--------------------------------------------------------------------------
| These remain for uploads, deletes, and admin tooling.
| NOTE: /play/{filename} is intentionally NOT used anymore.
*/

Route::get('/upload', [VideoController::class, 'upload'])
    ->name('video.upload');

Route::post('/upload', [VideoController::class, 'handleUpload'])
    ->name('video.handle_upload');

Route::post('/delete', [VideoController::class, 'delete'])
    ->name('video.delete');

/*
|--------------------------------------------------------------------------
| Legacy Route (Deprecated)
|--------------------------------------------------------------------------
| Left in place for safety but should NOT be used.
| Videos are streamed via Nginx from the VPS.
*/

// Route::get('/play/{filename}', [VideoController::class, 'play'])
//     ->name('video.play');
