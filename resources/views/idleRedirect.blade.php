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

        .scanner-panel {
            position: fixed;
            top: max(6px, env(safe-area-inset-top));
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
            width: min(500px, calc(100vw - 20px));
            display: flex;
            flex-direction: column;
            gap: 5px;
            align-items: center;
            padding: 6px;
            border-radius: 9px;
            background: rgba(0, 0, 0, 0.68);
            backdrop-filter: blur(6px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        }

        .scanner-label {
            color: #fff;
            font-size: clamp(11px, 1.4vw, 14px);
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        video {
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            background: black;
        }

        form {
            width: 100%;
        }

        input[type="text"] {
            width: 100%;
            padding: 16px 18px;
            font-size: clamp(22px, 3vw, 34px);
            border-radius: 10px;
            border: 2px solid rgba(255,255,255,0.45);
            background: rgba(20,20,20,0.9);
            color: #fff;
            text-align: center;
            box-sizing: border-box;
        }

        #barcodeInput {
            width: 46%;
            padding: 7px 8px;
            font-size: clamp(10px, 1.3vw, 15px);
            margin: 0 auto;
            display: block;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #7dd3fc;
            box-shadow: 0 0 0 4px rgba(125, 211, 252, 0.18);
        }

        #scanStatus {
            width: 100%;
            color: #fff;
            font-size: clamp(11px, 1.3vw, 14px);
            background: rgba(255, 255, 255, 0.12);
            padding: 5px 7px;
            border-radius: 7px;
            min-height: 16px;
            text-align: center;
            box-sizing: border-box;
        }

        @media (max-width: 640px) {
            .logo {
                width: 56px;
                height: 56px;
            }

            .logo-top-left,
            .logo-top-right {
                top: 90px;
            }
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
            <video id="video" autoplay muted playsinline>
                <source id="videoSource"
                        src="{{ $videos->first()->stream_url }}"
                        type="video/mp4">
            </video>
        @endif
    </div>

    <div class="scanner-panel">
        <div class="scanner-label">Scan Product Barcode</div>
        <form id="barcodeForm">
            @csrf
            <input type="text"
                   id="barcodeInput"
                   placeholder="Scan barcode here"
                   inputmode="numeric"
                   autocomplete="off"
                   autofocus>
        </form>

        <div id="scanStatus">
            Ready to scan
        </div>
    </div>

<script>
(function () {

    const videos = {!! json_encode(
        $videos->pluck('stream_url')->values()
    ) !!};

    const barcodeForm = document.getElementById('barcodeForm');
    const video = document.getElementById('video');
    const source = document.getElementById('videoSource');
    const barcodeInput = document.getElementById('barcodeInput');
    const scanStatus = document.getElementById('scanStatus');

    let currentIndex = 0;
    let scanTimeout = null;
    let isSubmittingBarcode = false;
    let scannerBuffer = '';
    let scannerBufferTimeout = null;
    let audioContext = null;

    function playIdleVideo(index) {
        source.src = videos[index];
        video.load();
        video.play().catch(() => {});
    }

    function playNextIdleVideo() {
        currentIndex = (currentIndex + 1) % videos.length;
        playIdleVideo(currentIndex);
    }

    function playVideoImmediately(url) {
        video.pause();
        video.currentTime = 0;

        source.src = url;
        video.load();

        video.muted = false;
        video.play().catch(() => {});
    }

    function focusBarcodeInput() {
        barcodeInput.focus({ preventScroll: true });
    }

    function setScanStatus(message) {
        scanStatus.textContent = message;
    }

    function playErrorSound() {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;

        if (!AudioCtx) {
            return;
        }

        if (!audioContext) {
            audioContext = new AudioCtx();
        }

        if (audioContext.state === 'suspended') {
            audioContext.resume().catch(() => {});
        }

        const now = audioContext.currentTime;
        const gain = audioContext.createGain();
        gain.connect(audioContext.destination);

        // Longer, higher-pitch double-tone beep for missing barcode feedback.
        const toneA = audioContext.createOscillator();
        toneA.type = 'square';
        toneA.frequency.setValueAtTime(520, now);
        toneA.connect(gain);
        toneA.start(now);
        toneA.stop(now + 0.14);

        const toneB = audioContext.createOscillator();
        toneB.type = 'square';
        toneB.frequency.setValueAtTime(440, now + 0.16);
        toneB.connect(gain);
        toneB.start(now + 0.16);
        toneB.stop(now + 0.32);

        gain.gain.setValueAtTime(0.001, now);
        gain.gain.exponentialRampToValueAtTime(0.2, now + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.38);
    }

    function submitBarcode() {
        const barcode = barcodeInput.value.replace(/\s+/g, '').trim();

        if (!barcode || isSubmittingBarcode) {
            return;
        }

        isSubmittingBarcode = true;
        barcodeInput.value = barcode;
        setScanStatus('Looking up ' + barcode);

        fetch('{{ route('barcode.input') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({ barcode })
        })
        .then(res => res.json())
        .then(data => {
            if (data.ok && data.video_url) {
                setScanStatus('Playing ' + barcode);
                playVideoImmediately(data.video_url);
            } else {
                playErrorSound();
                setScanStatus(data.error ? (data.error + ': ' + barcode) : ('No video for ' + barcode));
            }
        })
        .catch(() => {
            setScanStatus('Scan lookup failed');
        })
        .finally(() => {
            isSubmittingBarcode = false;
            scannerBuffer = '';
            barcodeInput.value = '';
            focusBarcodeInput();
        });
    }

    function queueBarcodeSubmit() {
        clearTimeout(scanTimeout);

        scanTimeout = setTimeout(() => {
            submitBarcode();
        }, 120);
    }

    function queueGlobalScanSubmit() {
        clearTimeout(scannerBufferTimeout);

        scannerBufferTimeout = setTimeout(() => {
            if (scannerBuffer.length >= 8) {
                barcodeInput.value = scannerBuffer;
                submitBarcode();
            } else if (scannerBuffer.length > 0) {
                setScanStatus('Short scan: ' + scannerBuffer);
                scannerBuffer = '';
                barcodeInput.value = '';
            }
        }, 120);
    }

    if (video) {
        video.addEventListener('ended', playNextIdleVideo);
        video.play().catch(() => {});
    }

    barcodeForm.addEventListener('submit', function (event) {
        event.preventDefault();
        submitBarcode();
    });

    barcodeInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            clearTimeout(scanTimeout);
            submitBarcode();
        }
    });

    barcodeInput.addEventListener('input', function () {
        if (barcodeInput.value.replace(/\s+/g, '').length >= 8) {
            queueBarcodeSubmit();
        }
    });

    barcodeInput.addEventListener('change', submitBarcode);

    document.addEventListener('keydown', function (event) {
        if (event.ctrlKey || event.altKey || event.metaKey) {
            return;
        }

        if (event.key === 'Enter' || event.key === 'Tab') {
            if (scannerBuffer.length >= 8) {
                event.preventDefault();
                clearTimeout(scannerBufferTimeout);
                barcodeInput.value = scannerBuffer;
                submitBarcode();
            }

            return;
        }

        if (event.key === 'Backspace') {
            scannerBuffer = scannerBuffer.slice(0, -1);
            barcodeInput.value = scannerBuffer;
            return;
        }

        if (event.key.length !== 1) {
            return;
        }

        if (!/[0-9A-Za-z\-]/.test(event.key)) {
            return;
        }

        scannerBuffer += event.key;
        barcodeInput.value = scannerBuffer;
        setScanStatus('Scanning ' + scannerBuffer);
        queueGlobalScanSubmit();
    }, true);

    document.addEventListener('click', focusBarcodeInput);
    document.addEventListener('fullscreenchange', focusBarcodeInput);
    window.addEventListener('focus', focusBarcodeInput);

    focusBarcodeInput();

    // Fullscreen once
    setTimeout(() => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen?.();
        }
    }, 500);

})();
</script>

</body>
</html>
