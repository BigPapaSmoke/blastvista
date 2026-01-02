<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>Favorite Videos Player</title>

    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: black;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background-image: url('/images/bVLogo.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .logo {
            position: fixed;
            width: 80px;
            height: 80px;
            background-image: url('/images/logo-round.png');
            background-size: contain;
            background-repeat: no-repeat;
            z-index: 5;
        }

        .logo-top-left { top: 10px; left: 10px; }
        .logo-top-right { top: 10px; right: 10px; }
        .logo-bottom-left { bottom: 10px; left: 10px; }
        .logo-bottom-right { bottom: 10px; right: 10px; }

        .container {
            width: 100%;
            height: 100%;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        video {
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            background: black;
        }

        form {
            position: fixed;
            bottom: 5vh;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }

        input[type="text"] {
            width: min(600px, 90vw);
            padding: 14px;
            font-size: clamp(20px, 3vw, 36px);
            border-radius: 6px;
            border: none;
            background: rgba(0,0,0,0.7);
            color: #fff;
            text-align: center;
        }

        input[type="text"]:focus {
            outline: none;
        }
    </style>
</head>

<body>

    <!-- Corner logos -->
    <div class="logo logo-top-left"></div>
    <div class="logo logo-top-right"></div>
    <div class="logo logo-bottom-left"></div>
    <div class="logo logo-bottom-right"></div>

    <div class="container">
        @if ($videos->isNotEmpty())
            <video id="video-element" autoplay muted playsinline>
                <source id="video-source"
                    src="{{ route('video.play', str_replace(' ', '+', trim($videos->first()->filename))) }}"
                    type="video/mp4">
            </video>
        @endif
    </div>

    <form id="barcodeForm" method="POST" action="{{ route('barcode.input') }}">
        @csrf
        <input type="text"
               name="barcode"
               id="barcodeInput"
               placeholder="Scan barcode"
               autofocus>
    </form>

<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {

        const videos = {!! json_encode(
            $videos->pluck('filename')->map(fn($f) =>
                route('video.play', str_replace(' ', '+', trim($f)))
            )->toArray()
        ) !!};

        const video = document.getElementById('video-element');
        const source = document.getElementById('video-source');
        const barcodeInput = document.getElementById('barcodeInput');
        const barcodeForm = document.getElementById('barcodeForm');

        let currentIndex = 0;

        function stopVideoImmediately() {
            if (!video) return;
            video.pause();
            video.currentTime = 0;
            video.removeAttribute('src');
            video.load();
        }

        function playNextIdleVideo() {
            if (!videos.length) return;
            currentIndex = (currentIndex + 1) % videos.length;
            source.src = videos[currentIndex];
            video.load();
            video.play().catch(() => {});
        }

        if (video) {
            video.addEventListener('ended', playNextIdleVideo);
            video.play().catch(() => {});
        }

        barcodeInput.addEventListener('change', function () {
            const barcode = barcodeInput.value.trim();
            if (!barcode) return;

            stopVideoImmediately();
            video.muted = false;

            setTimeout(() => {
                barcodeForm.submit();
            }, 50);
        });

        function enterFullscreenOnce() {
            const el = document.documentElement;
            if (!document.fullscreenElement) {
                if (el.requestFullscreen) el.requestFullscreen();
                else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
            }
        }

        setTimeout(enterFullscreenOnce, 500);

    });
})();
</script>

</body>
</html>
