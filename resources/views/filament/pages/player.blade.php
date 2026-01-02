<x-filament::page>
    <h2 class="text-2xl font-bold mb-4">Favorite Videos Player</h2>
    <div id="video-player" style="position: relative;">
        <video id="video-element" width="1920" height="1080" controls autoplay playsinline>
            <source id="video-source" src="{{ asset('storage/videos/' . $videos->first()->filename) }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <!-- Barcode Input Resized -->
        <input type="text"
            id="barcodeInput"
            placeholder="Scan here"
            autocomplete="off"
            style="position: absolute; bottom: 32px; left: 32px; z-index: 1000; background: rgba(255, 255, 255, 0.9); border: 2px solid #000; padding: 8px 12px; width: 260px; font-size: 22px; border-radius: 6px;">
</div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const videos = {!! json_encode($videos->pluck('filename')->map(function($filename) {
                return asset('storage/videos/' . $filename);
            })) !!};
            const videoElement = document.getElementById('video-element');
            const videoSource = document.getElementById('video-source');
            const barcodeInput = document.getElementById('barcodeInput');
            const videoPlayer = document.getElementById('video-player');

            if (!videoElement || !videoSource || !barcodeInput) {
                console.error('Video element, source, or barcode input not found');
                return;
            }

            function requestFullscreen(element) {
                if (!element) {
                    return Promise.reject(new Error('Element missing for fullscreen request'));
                }
                const request = element.requestFullscreen || element.webkitRequestFullscreen || element.mozRequestFullScreen || element.msRequestFullscreen;
                if (request) {
                    return request.call(element);
                }
                return Promise.reject(new Error('Fullscreen API not supported'));
            }

            function attemptPlayback() {
                if (!videoElement) {
                    return Promise.resolve();
                }
                videoElement.muted = false;
                videoElement.volume = 1;
                return videoElement.play().catch(function(error) {
                    console.warn('[Video] Playback blocked, awaiting user action:', error);
                });
            }

            attemptPlayback();

            // Handle video looping - restart same video
            videoElement.addEventListener('ended', function () {
                console.log('[Video] Video ended, restarting video');
                videoElement.currentTime = 0;
                videoElement.play().catch(function(error) {
                    console.warn('[Video] Restart play failed:', error);
                });
            });

            // Toggle full-screen mode and ensure barcodeInput stays visible
            videoPlayer.style.cursor = 'pointer';
            videoPlayer.addEventListener('click', function() {
                const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement;
                const fullscreenPromise = isFullscreen ? Promise.resolve() : requestFullscreen(videoPlayer).catch(function(error) {
                    console.warn('[Video] Fullscreen request failed:', error);
                });

                fullscreenPromise.finally(function() {
                    attemptPlayback().finally(function() {
                        barcodeInput.focus({ preventScroll: true });
                    });
                });
            });

            // Adjust barcodeInput styling during full-screen mode
            function handleFullscreenChange() {
                if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
                    barcodeInput.style.position = 'fixed';
                    barcodeInput.style.bottom = '48px';
                    barcodeInput.style.left = '48px';
                    barcodeInput.style.zIndex = '1000';
                    barcodeInput.focus({ preventScroll: true });
                } else {
                    barcodeInput.style.position = 'absolute';
                    barcodeInput.style.bottom = '32px';
                    barcodeInput.style.left = '32px';
                    barcodeInput.focus({ preventScroll: true });
                }
            }

            handleFullscreenChange();

            document.addEventListener('fullscreenchange', handleFullscreenChange);
            document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
            document.addEventListener('mozfullscreenchange', handleFullscreenChange);
            document.addEventListener('msfullscreenchange', handleFullscreenChange);

            // Handle barcode input
            barcodeInput.addEventListener('change', function() {
                const barcode = barcodeInput.value;
                const specificBarcode = '1234567890'; // Replace with your specific barcode value
                if (barcode === specificBarcode) {
                    window.location.href = '/';
                } else {
                    console.log('Scanned barcode does not match the specific barcode.');
                }
                barcodeInput.value = '';
                barcodeInput.focus({ preventScroll: true });
            });

            // Automatically focus the barcode input field on page load
            barcodeInput.focus({ preventScroll: true });
        });
    </script>
</x-filament::page>
