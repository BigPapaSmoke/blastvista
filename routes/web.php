<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\VideoController;
use App\Models\Video;

$streamBaseUrl = rtrim((string) env('VIDEO_STREAM_BASE_URL', ''), '/');

$buildStreamUrl = static function (string $filename) use ($streamBaseUrl): string {
    $encodedFilename = rawurlencode(trim($filename));

    if ($streamBaseUrl !== '') {
        return $streamBaseUrl . '/videos/' . $encodedFilename;
    }

    return '/storage/videos/' . $encodedFilename;
};

$normalizeBarcode = static function (?string $value): string {
    $value = strtoupper(trim((string) $value));

    return preg_replace('/[^A-Z0-9]/', '', $value) ?? '';
};

$findVideoByBarcode = static function (string $rawBarcode) use ($normalizeBarcode): ?Video {
    $normalizedBarcode = $normalizeBarcode($rawBarcode);

    if ($normalizedBarcode === '') {
        return null;
    }

    $candidateBarcodes = array_values(array_unique(array_filter([
        $rawBarcode,
        trim($rawBarcode),
        $normalizedBarcode,
        ltrim($normalizedBarcode, '0'),
        str_pad($normalizedBarcode, 12, '0', STR_PAD_LEFT),
    ], static fn (string $value): bool => $value !== '')));

    $video = Video::query()
        ->whereIn('barcode', $candidateBarcodes)
        ->first();

    if ($video) {
        return $video;
    }

    return Video::query()
        ->whereNotNull('barcode')
        ->get()
        ->first(static function (Video $video) use ($normalizeBarcode, $normalizedBarcode): bool {
            $storedBarcode = $normalizeBarcode($video->barcode);

            if ($storedBarcode === '') {
                return false;
            }

            return $storedBarcode === $normalizedBarcode
                || ltrim($storedBarcode, '0') === ltrim($normalizedBarcode, '0');
        });
};

/*
|--------------------------------------------------------------------------
| Public Idle Player (Main Screen)
|--------------------------------------------------------------------------
| Displays looping favorite videos and listens for barcode scans.
| Videos are streamed directly from the VPS (Nginx), not Laravel.
*/

Route::get('/', function () use ($buildStreamUrl) {

    $videos = Video::where('is_favorite', true)
        ->orderBy('created_at', 'desc')
        ->orderBy('id', 'desc')
        ->limit(50)
        ->get()
        ->map(function ($video) use ($buildStreamUrl) {
            $video->stream_url = $buildStreamUrl($video->filename);
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

Route::post('/barcode', function (Request $request) use ($buildStreamUrl, $findVideoByBarcode, $normalizeBarcode) {

    $barcode = trim((string) $request->input('barcode'));

    if (!$barcode) {
        return response()->json([
            'ok' => false,
            'error' => 'Empty barcode',
        ]);
    }

    $video = $findVideoByBarcode($barcode);

    if (!$video) {
        Storage::disk('local')->append('reports/missing_scans.jsonl', json_encode([
            'timestamp' => now()->toIso8601String(),
            'raw_barcode' => $barcode,
            'normalized_barcode' => $normalizeBarcode($barcode),
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ], JSON_UNESCAPED_SLASHES));

        return response()->json([
            'ok' => false,
            'error' => 'Video not found',
            'scanned_barcode' => $barcode,
            'normalized_barcode' => $normalizeBarcode($barcode),
        ]);
    }

    return response()->json([
        'ok' => true,
        'video_url' => $buildStreamUrl($video->filename),
        'matched_barcode' => $video->barcode,
        'filename' => $video->filename,
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
