<x-filament::page>
    <h2 class="text-2xl font-bold mb-4">Favorite Videos Player</h2>
    <div id="video-player" style="position: relative;">
        <video id="video-element" width="1920" height="1080" controls autoplay playsinline muted>
            <source id="video-source" src="{{ asset('storage/videos/' . $videos->first()->filename) }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <!-- Barcode Input Resized -->
   <input type="text"
       id="barcodeInput"
       placeholder="Scan here"
       style="position: absolute; bottom: 100px; left: 20px; z-index: 1000; background: white; border: 1px solid black; padding: 5px; width: 100px;">
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

            let currentIndex = 0;

            // Handle video looping - restart same video
            videoElement.addEventListener('ended', function () {
                console.log('[Video] Video ended, restarting video');
                videoElement.currentTime = 0;
                videoElement.play().catch(function(error) {
                    console.warn('[Video] Restart play failed:', error);
                });
            });

            // Toggle full-screen mode and ensure barcodeInput stays visible
            videoPlayer.addEventListener('click', function() {
                if (videoPlayer.requestFullscreen) {
                    videoPlayer.requestFullscreen();
                } else if (videoPlayer.webkitRequestFullscreen) {
                    videoPlayer.webkitRequestFullscreen();
                } else if (videoPlayer.mozRequestFullScreen) {
                    videoPlayer.mozRequestFullScreen();
                } else if (videoPlayer.msRequestFullscreen) {
                    videoPlayer.msRequestFullscreen();
                }
            });

            // Adjust barcodeInput styling during full-screen mode
            function handleFullscreenChange() {
                if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
                    barcodeInput.style.position = 'fixed';
                    barcodeInput.style.bottom = '100px'; // Adjusted position
                    barcodeInput.style.left = '20px';
                    barcodeInput.style.zIndex = '1000';
                    barcodeInput.focus(); // Auto-focus during full-screen
                } else {
                    barcodeInput.style.position = 'absolute';
                    barcodeInput.style.bottom = '100px';
                    barcodeInput.style.left = '20px';
                    barcodeInput.focus(); // Auto-focus after exiting full-screen
                }
            }

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
            });

            // Automatically focus the barcode input field on page load
            barcodeInput.focus();
        });
    </script>
</x-filament::page>
