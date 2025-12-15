<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VideoController extends Controller
{
public function play($filename)
    {
        \Log::info('Play route called for filename: "' . $filename . '"');
        
        $decodedFilename = urldecode($filename);
        \Log::info('Decoded filename: "' . $decodedFilename . '"');
        
        $path = 'videos/' . $decodedFilename;
        $fullPath = Storage::disk('public')->path($path);
        \Log::info('Checking file at path: "' . $fullPath . '"');
        
        if (!Storage::disk('public')->exists($path)) {
            \Log::error('Video file not found in storage: "' . $fullPath . '"');
            abort(404, 'The video file could not be found.');
        }
        \Log::info('Video file exists in storage: "' . $fullPath . '"');

        $mimeType = \Illuminate\Support\Facades\File::mimeType($fullPath);
        \Log::info('MIME type for video: "' . $mimeType . '"');
        
        $fileSize = Storage::disk('public')->size($path);
        \Log::info('Video file size: ' . $fileSize . ' bytes');

        return response()->file($fullPath, [
            'Content-Type' => 'video/mp4',
            'Content-Length' => $fileSize,
            'Content-Disposition' => 'inline; filename="' . $decodedFilename . '"',
            'Accept-Ranges' => 'bytes',
        ]);
    }

 public function barcodeInput(Request $request)
    {
        if ($request->isMethod('post')) {
            // Validate and process barcode input
            $request->validate(['barcode' => 'required']);
            $barcode = $request->input('barcode');
            // Your logic to find video and redirect, e.g.:
            $video = Video::where('barcode', $barcode)->first();
            if ($video) {
                return view('welcome', compact('video')); // Replace with your view name, e.g., 'welcome'
            }
            return back()->withErrors(['barcode' => 'Invalid barcode']);
        }

        // For GET requests or empty POST, return the home page view
        return view('welcome'); // Replace with your view name, e.g., 'welcome'
    }

    public function upload()
    {
        return view('upload');
    }

    public function handleUpload(Request $request)
    {
        // Placeholder for upload logic
        return redirect()->back()->with('message', 'Upload not implemented.');
    }

    public function delete(Request $request)
    {
        // Placeholder for delete logic
        return redirect()->back()->with('message', 'Delete not implemented.');
    }
}