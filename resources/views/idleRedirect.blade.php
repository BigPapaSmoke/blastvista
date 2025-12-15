<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>Favorite Videos Player</title>
    <style>
        body {
            margin: 0;
            background-image: url('/images/bVLogo.png'); /* True black background with logos */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        .logo {
            position: absolute;
            width: 80px;
            height: 80px;
            background-image: url('/images/logo-round.png'); /* Fallback round logo */
            background-size: contain;
            background-repeat: no-repeat;
        }
        .logo-top-left { top: 10px; left: 10px; }
        .logo-top-right { top: 10px; right: 10px; }
        .logo-bottom-left { bottom: 10px; left: 10px; }
        .logo-bottom-right { bottom: 10px; right: 10px; }
        .container {
            max-width: 1200px;
            width: 100%;
            text-align: center;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent for readability */
            border-radius: 10px;
            padding: 20px;
        }
        video {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
            border: 3px solid #444;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        form {
            margin: 30px 0;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        input[type="text"] {
            padding: 12px;
            font-size: 16px;
            width: 350px;
            border: 1px solid #555;
            border-radius: 6px;
            background: #333;
            color: #fff;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
        }
        h2 {
            font-size: 28px;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <!-- Fallback CSS logos -->
    <div class="logo logo-top-left"></div>
    <div class="logo logo-top-right"></div>
    <div class="logo logo-bottom-left"></div>
    <a href="/admin" style="position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%); color: #333; font-size: 12px; text-decoration: none;">admin</a>
    <div class="logo logo-bottom-right"></div>

    <div class="container">
        <h2>Favorite Videos Player</h2>
        @if ($videos->isNotEmpty())
            <div id="video-player" style="position: relative;">
                <video id="video-element" controls autoplay playsinline muted>
                    <source id="video-source" src="{{ route('video.play', str_replace(' ', '+', trim($videos->first()->filename))) }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            <form id="barcodeForm" method="POST" action="{{ route('barcode.input') }}">
                @csrf
                <input type="text" name="barcode" id="barcodeInput" placeholder="Enter or scan barcode" autofocus>
            </form>
        @else
            <p>No favorite videos available.</p>
            <form id="barcodeForm" method="POST" action="{{ route('barcode.input') }}">
                @csrf
                <input type="text" name="barcode" id="barcodeInput" placeholder="Enter or scan barcode" autofocus>
            </form>
        @endif
    </div>

    <script>
        (function() { // IIFE to avoid conflicts
            document.addEventListener('DOMContentLoaded', function () {
                const videos = {!! json_encode($videos->pluck('filename')->map(function($filename) {
                    return route('video.play', str_replace(' ', '+', trim($filename)));
                })->toArray()) !!};
                const videoElement = document.getElementById('video-element');
                const videoSource = document.getElementById('video-source');
                const barcodeInput = document.getElementById('barcodeInput');
                const barcodeForm = document.getElementById('barcodeForm');
                const videoPlayer = document.getElementById('video-player');

                if (!barcodeInput || !barcodeForm) {
                    console.error('[Barcode] Barcode input or form not found');
                    return;
                }
                if (videoElement && !videoSource) {
                    console.error('[Video] Video source not found');
                    return;
                }

                // Initialize barcode input
                console.log('[Barcode] Input initialized');
                barcodeInput.focus();
                barcodeInput.addEventListener('input', function() {
                    console.log('[Barcode] Input event triggered');
                });
                barcodeInput.addEventListener('change', function() {
                    const barcode = barcodeInput.value;
                    if (barcode.length > 0) {
                        console.log('[Barcode] Barcode scanned:', barcode);
                        barcodeForm.submit();
                    }
                });

                // Video looping logic
                if (videoElement && videoSource) {
                    let currentIndex = 0;
                    
                    // Force video to play immediately
                    videoElement.muted = true; // Ensure muted for autoplay
                    const playPromise = videoElement.play();
                    
                    if (playPromise !== undefined) {
                        playPromise.then(function() {
                            console.log('[Video] Autoplay started successfully');
                        }).catch(function(error) {
                            console.warn('[Video] Autoplay failed:', error);
                            // Show a play button overlay
                            const playButton = document.createElement('button');
                            playButton.textContent = 'Click to Play';
                            playButton.style.cssText = 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; padding: 20px 40px; font-size: 24px; background: #007bff; color: white; border: none; border-radius: 8px; cursor: pointer;';
                            videoPlayer.appendChild(playButton);
                            playButton.addEventListener('click', function() {
                                videoElement.play();
                                playButton.remove();
                            });
                        });
                    }
                    
                    videoElement.addEventListener('ended', function () {
                        console.log('[Video] Video ended, switching to next');
                        currentIndex = (currentIndex + 1) % videos.length;
                        const newVideoSource = videos[currentIndex];
                        if (newVideoSource) {
                            videoSource.src = newVideoSource;
                            videoElement.load();
                            videoElement.play().catch(function(error) {
                                console.warn('[Video] Play failed for next video:', error);
                            });
                            console.log('[Video] Playing next video:', newVideoSource);
                        }
                    });

                    // Toggle full-screen mode
                    videoPlayer.addEventListener('click', function(event) {
                        if (event.target.tagName !== 'INPUT' && event.target.tagName !== 'FORM') {
                            if (videoPlayer.requestFullscreen) {
                                videoPlayer.requestFullscreen();
                            } else if (videoPlayer.webkitRequestFullscreen) {
                                videoPlayer.webkitRequestFullscreen();
                            } else if (videoPlayer.mozRequestFullScreen) {
                                videoPlayer.mozRequestFullScreen();
                            } else if (videoPlayer.msRequestFullscreen) {
                                videoPlayer.msRequestFullscreen();
                            }
                        }
                    });

                    // Adjust form in full-screen
                    function handleFullscreenChange() {
                        if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
                            barcodeForm.style.position = 'fixed';
                            barcodeForm.style.bottom = '30px';
                            barcodeForm.style.left = '50%';
                            barcodeForm.style.transform = 'translateX(-50%)';
                            barcodeForm.style.zIndex = '1000';
                            barcodeInput.focus();
                            console.log('[Video] Entered full-screen, adjusted barcode form');
                        } else {
                            barcodeForm.style.position = 'static';
                            barcodeForm.style.bottom = 'auto';
                            barcodeForm.style.left = 'auto';
                            barcodeForm.style.transform = 'none';
                            barcodeInput.focus();
                            console.log('[Video] Exited full-screen, reset barcode form');
                        }
                    }

                    document.addEventListener('fullscreenchange', handleFullscreenChange);
                    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
                    document.addEventListener('mozfullscreenchange', handleFullscreenChange);
                    document.addEventListener('msfullscreenchange', handleFullscreenChange);
                }

                // Inactivity redirect to self - removed since this is the main loop
                // Videos will continue looping indefinitely until a barcode is scanned
            });
        })();
    </script>
</body>
</html>