<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Player</title>
    <style>
        body {
            margin: 0;
            background-image: url('/images/bVLogo.png');
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
            background-image: url('/images/logo-round.png');
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
            background: rgba(0, 0, 0, 0.5);
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
            transition: border-color 0.3s; /* Fixed typo */
        }
        input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
        }
        button {
            padding: 12px 30px;
            font-size: 16px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: #ff4d4d;
            margin-top: 15px;
            font-size: 14px;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        a {
            color: #1e90ff;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="logo logo-top-left"></div>
    <div class="logo logo-top-right"></div>
    <div class="logo logo-bottom-left"></div>
    <div class="logo logo-bottom-right"></div>

    <div class="container">
        @if (isset($video))
            <h1>Playing: {{ strtoupper(\Illuminate\Support\Str::replaceLast('.mp4', '', $video->filename)) }}</h1>
            <video id="videoPlayer" autoplay controls muted>
                <source src="{{ route('video.play', str_replace(' ', '+', trim($video->filename))) }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <form id="barcodeForm" method="POST" action="{{ route('barcode.input') }}">
                @csrf
                <input type="text" name="barcode" id="barcodeInput" placeholder="Enter or scan barcode" autofocus>
                <button type="submit">Search</button>
            </form>
            @if ($errors->has('barcode'))
                <p class="error">{{ $errors->first('barcode') }}</p>
            @endif
        @else
            <form id="barcodeForm" method="POST" action="{{ route('barcode.input') }}">
                @csrf
                <input type="text" name="barcode" id="barcodeInput" placeholder="Enter or scan barcode" autofocus>
                <button type="submit">Submit</button>
            </form>
            @if ($errors->has('barcode'))
                <p class="error">{{ $errors->first('barcode') }}</p>
            @endif
        @endif
    </div>

    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                const barcodeInput = document.getElementById('barcodeInput');
                const barcodeForm = document.getElementById('barcodeForm');
                const videoPlayer = document.getElementById('videoPlayer');

                if (barcodeInput) {
                    console.log('[Barcode] Input initialized');
                    barcodeInput.focus();
                    barcodeInput.addEventListener('input', function() {
                        console.log('[Barcode] Input event triggered');
                    });
                }

                // When a scanned video ends, return to the favorite videos loop
                if (videoPlayer) {
                    videoPlayer.addEventListener('ended', function() {
                        console.log('[Video] Video ended, returning to favorite videos loop');
                        window.location.href = '/idleRedirect';
                    });
                    
                    // Force video to play immediately
                    videoPlayer.play().catch(function(error) {
                        console.warn('[Video] Autoplay failed:', error);
                    });

                    // Inactivity redirect logic (5 minutes after video starts, go back to loop)
                    let inactivityTimeout;
                    const inactivityPeriod = 300000; // 5 minutes
                    function resetTimer() {
                        console.log('[Inactivity] Resetting timer');
                        clearTimeout(inactivityTimeout);
                        inactivityTimeout = setTimeout(() => {
                            console.log('[Inactivity] Timeout reached, returning to loop');
                            if (videoPlayer && !videoPlayer.paused) {
                                videoPlayer.pause();
                                console.log('[Inactivity] Video paused');
                            }
                            window.location.href = '/';
                        }, inactivityPeriod);
                    }

                    ['mousemove', 'click', 'keydown', 'touchstart'].forEach(event => {
                        document.addEventListener(event, () => {
                            console.log(`[Inactivity] User activity detected: ${event}`);
                            resetTimer();
                        });
                    });

                    console.log('[Inactivity] Starting timer');
                    resetTimer();
                }
            });
        })();
    </script>
</body>
</html>