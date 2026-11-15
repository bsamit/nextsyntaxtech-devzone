<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'NextSyntaxTech') }} - nextSyntaxTech DevZone Security Gateway</title>
    <!-- Local CSS Dependencies (Zero CDN, 100% Offline) -->
    <link href="{{ \NextSyntaxTech\DevZone\Services\DevZoneService::asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ \NextSyntaxTech\DevZone\Services\DevZoneService::asset('css/boxicons.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --term-bg: #050811;
            --term-card-bg: rgba(9, 14, 28, 0.90);
            --term-border: rgba(56, 189, 248, 0.28);
            --term-cyan: #06b6d4;
            --term-blue: #3b82f6;
            --term-green: #10b981;
            --term-neon-green: #00ff66;
            --term-purple: #a855f7;
            --term-red: #ef4444;
            --term-amber: #f59e0b;
            --term-text: #f0fdf4;
            --term-muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--term-bg);
            color: var(--term-text);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Courier New", monospace;
            min-height: 100vh;
            margin: 0;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Matrix Canvas Background */
        #matrixCanvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
            opacity: 0.22;
            pointer-events: none;
        }

        /* Cyber Perspective Grid Overlay */
        .cyber-grid-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            background: 
                linear-gradient(rgba(6, 182, 212, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(6, 182, 212, 0.04) 1px, transparent 1px);
            background-size: 38px 38px;
            pointer-events: none;
        }

        /* Subtle Retro CRT Scanline Overlay */
        .scanline-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            background: repeating-linear-gradient(
                0deg,
                rgba(0, 0, 0, 0.15),
                rgba(0, 0, 0, 0.15) 1px,
                transparent 1px,
                transparent 2px
            );
            pointer-events: none;
            opacity: 0.6;
        }

        /* Ambient Radial Cyber Glow */
        .ambient-glow {
            position: fixed;
            width: 620px;
            height: 620px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.16) 0%, rgba(6, 182, 212, 0.06) 50%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            filter: blur(40px);
            pointer-events: none;
            animation: pulse-ambient 4s ease-in-out infinite alternate;
        }

        @keyframes pulse-ambient {
            0% { transform: translate(-50%, -50%) scale(0.92); opacity: 0.6; }
            100% { transform: translate(-50%, -50%) scale(1.1); opacity: 0.95; }
        }

        /* ============================================================== */
        /* HEART PULSE BASE ANIMATION & TELEMETRY                        */
        /* ============================================================== */
        .heart-beat-anim {
            display: inline-block;
            animation: heartPulseRhythm 1.3s infinite ease-in-out;
            transform-origin: center center;
            vertical-align: middle;
        }

        @keyframes heartPulseRhythm {
            0% {
                transform: scale(1);
                filter: drop-shadow(0 0 2px rgba(239, 68, 68, 0.4));
            }
            14% {
                transform: scale(1.35);
                filter: drop-shadow(0 0 12px rgba(239, 68, 68, 0.95));
            }
            28% {
                transform: scale(1.05);
                filter: drop-shadow(0 0 4px rgba(239, 68, 68, 0.5));
            }
            42% {
                transform: scale(1.22);
                filter: drop-shadow(0 0 8px rgba(239, 68, 68, 0.8));
            }
            70% {
                transform: scale(1);
                filter: drop-shadow(0 0 2px rgba(239, 68, 68, 0.4));
            }
            100% {
                transform: scale(1);
                filter: drop-shadow(0 0 2px rgba(239, 68, 68, 0.4));
            }
        }

        /* Heart Pulse Wave / Sonar Ripple */
        .heart-pulse-box {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
        }
        .heart-pulse-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 14px;
            height: 14px;
            margin-top: -7px;
            margin-left: -7px;
            border-radius: 50%;
            border: 1.5px solid rgba(239, 68, 68, 0.8);
            animation: heartWaveExpand 1.3s infinite cubic-bezier(0.2, 0.8, 0.4, 1);
            pointer-events: none;
        }
        @keyframes heartWaveExpand {
            0% {
                transform: scale(0.6);
                opacity: 1;
            }
            60% {
                transform: scale(2.2);
                opacity: 0.15;
            }
            100% {
                transform: scale(2.8);
                opacity: 0;
            }
        }

        /* Heart Pulse Telemetry Pill */
        .heart-telemetry-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 20px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.35);
            font-size: 10.5px;
            font-weight: 800;
            color: #fca5a5;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Animated ECG Waveform Graph */
        .ecg-graph-wrap {
            display: inline-flex;
            align-items: center;
            height: 20px;
            width: 66px;
            overflow: hidden;
            vertical-align: middle;
            margin: 0 4px;
            opacity: 0.9;
        }
        .ecg-svg {
            width: 100%;
            height: 100%;
        }
        .ecg-line-draw {
            stroke-dasharray: 120;
            stroke-dashoffset: 120;
            animation: ecgSweep 2.2s linear infinite;
        }
        @keyframes ecgSweep {
            0% { stroke-dashoffset: 120; }
            55% { stroke-dashoffset: 0; }
            100% { stroke-dashoffset: -120; }
        }

        /* ============================================================== */
        /* ROOT BRANDING: nextSyntaxTech                                 */
        /* ============================================================== */
        .brand-nextsyntaxtech {
            font-weight: 800;
            letter-spacing: 0.4px;
            display: inline-flex;
            align-items: baseline;
        }
        .brand-nextsyntaxtech .b-next {
            color: var(--term-cyan);
            font-weight: 800;
        }
        .brand-nextsyntaxtech .b-syntax {
            color: #ffffff;
            font-weight: 700;
        }
        .brand-nextsyntaxtech .b-tech {
            color: var(--term-blue);
            font-weight: 800;
        }

        /* Terminal Window Container */
        .terminal-window {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 530px;
            background: var(--term-card-bg);
            border: 1px solid var(--term-border);
            border-radius: 14px;
            box-shadow: 
                0 0 30px rgba(6, 182, 212, 0.15),
                0 25px 60px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            overflow: hidden;
            animation: terminal-pop 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes terminal-pop {
            0% { opacity: 0; transform: scale(0.94) translateY(16px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* Top Title Bar */
        .terminal-titlebar {
            padding: 12px 18px;
            background: rgba(13, 20, 38, 0.95);
            border-bottom: 1px solid rgba(56, 189, 248, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .window-controls {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-shrink: 0;
        }
        .window-dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            display: inline-block;
        }
        .dot-close { background: #ef4444; box-shadow: 0 0 6px rgba(239, 68, 68, 0.5); }
        .dot-min { background: #f59e0b; box-shadow: 0 0 6px rgba(245, 158, 11, 0.5); }
        .dot-max { background: #10b981; box-shadow: 0 0 6px rgba(16, 185, 129, 0.5); }

        .terminal-title {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Terminal Body */
        .terminal-body {
            padding: 26px 28px 24px;
        }

        /* Diagnostic CMD Readout Screen */
        .cmd-readout {
            background: rgba(4, 7, 18, 0.9);
            border: 1px solid rgba(6, 182, 212, 0.25);
            border-left: 3px solid var(--term-cyan);
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 22px;
            font-size: 12px;
            line-height: 1.65;
            color: #94a3b8;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.6);
        }
        .cmd-line-prompt {
            color: var(--term-cyan);
            font-weight: 700;
        }
        .cmd-highlight {
            color: #ffffff;
            font-weight: 600;
        }
        .cmd-brand-glow {
            color: #38bdf8;
            font-weight: 800;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.5);
        }
        .cmd-cursor {
            display: inline-block;
            width: 8px;
            height: 13px;
            background: var(--term-cyan);
            margin-left: 5px;
            vertical-align: -1px;
            animation: cursor-blink 1s infinite;
        }
        @keyframes cursor-blink {
            0%, 49% { opacity: 1; }
            50%, 100% { opacity: 0; }
        }

        /* Cyber Form Controls */
        .cyber-input-wrap {
            margin-bottom: 20px;
        }
        .cyber-label {
            font-size: 11.5px;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .cyber-label-prefix {
            color: var(--term-cyan);
        }

        .cyber-input-group {
            position: relative;
            display: flex;
            align-items: stretch;
            background: rgba(6, 10, 22, 0.85);
            border: 1px solid rgba(56, 189, 248, 0.35);
            border-radius: 8px;
            transition: all 0.2s ease;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5);
        }
        .cyber-input-group:focus-within {
            border-color: var(--term-cyan);
            box-shadow: 
                0 0 16px rgba(6, 182, 212, 0.35),
                inset 0 0 10px rgba(6, 182, 212, 0.1);
        }

        .cyber-input-prefix {
            padding: 12px 14px;
            color: var(--term-cyan);
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            border-right: 1px solid rgba(56, 189, 248, 0.2);
            background: rgba(13, 20, 38, 0.6);
            user-select: none;
        }

        .cyber-input {
            flex-grow: 1;
            background: transparent !important;
            border: none !important;
            color: #f8fafc !important;
            padding: 12px 14px;
            font-family: inherit;
            font-size: 14px;
            outline: none !important;
            letter-spacing: 1px;
        }
        .cyber-input::placeholder {
            color: #475569 !important;
            letter-spacing: 0;
            font-size: 12.5px;
        }

        .cyber-toggle-btn {
            background: transparent;
            border: none;
            color: #64748b;
            padding: 0 14px;
            cursor: pointer;
            transition: color 0.18s ease;
            display: flex;
            align-items: center;
        }
        .cyber-toggle-btn:hover {
            color: var(--term-cyan);
        }

        /* Glow Submit Button */
        .btn-cyber-submit {
            position: relative;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            border: 1px solid #38bdf8;
            border-radius: 8px;
            color: #ffffff;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 800;
            letter-spacing: 1px;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.25s ease;
            box-shadow: 0 0 20px rgba(37, 99, 235, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-cyber-submit:hover {
            background: linear-gradient(135deg, #0ea5e9 0%, #1d4ed8 100%);
            box-shadow: 0 0 30px rgba(6, 182, 212, 0.65);
            transform: translateY(-1px);
        }
        .btn-cyber-submit:active {
            transform: translateY(1px);
        }

        /* Status & Telemetry Bar */
        .terminal-statusbar {
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px dashed rgba(56, 189, 248, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 11.5px;
            color: #64748b;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .status-badge i {
            color: var(--term-cyan);
        }

        /* DevZone Alert Theming for Terminal */
        .devzone-alert {
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12.5px;
            font-family: inherit;
        }
        .devzone-alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #6ee7b7; }
        .devzone-alert-danger  { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; }
        .devzone-alert-warning { background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); color: #fcd34d; }
        .devzone-alert-info    { background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); color: #93c5fd; }
        .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    </style>
</head>
<body>

<!-- Dynamic Matrix Canvas Stream -->
<canvas id="matrixCanvas"></canvas>

<!-- Perspective Cyber Grid -->
<div class="cyber-grid-overlay"></div>

<!-- CRT Scanline Overlay -->
<div class="scanline-overlay"></div>

<!-- Glowing Ambient Core -->
<div class="ambient-glow"></div>

<!-- Terminal Main Window -->
<div class="terminal-window">
    <!-- Window Title Bar -->
    <div class="terminal-titlebar">
        <div class="window-controls">
            <span class="window-dot dot-close" title="Restricted Session"></span>
            <span class="window-dot dot-min" title="Standby"></span>
            <span class="window-dot dot-max" title="Full Shell"></span>
        </div>
        <div class="terminal-title">
            <span class="brand-nextsyntaxtech">
                <span class="b-next">next</span><span class="b-syntax">Syntax</span><span class="b-tech">Tech</span>
            </span>
            <span style="color: var(--term-cyan); opacity: 0.7;">//</span>
            <span>DevZone</span>
        </div>
    </div>

    <!-- Terminal Inner Body -->
    <div class="terminal-body">
        <!-- Interactive Diagnostic Readout -->
        <div class="cmd-readout">
            <div><span class="cmd-line-prompt">> HOST:</span> <span class="cmd-highlight">{{ config('app.name', 'Software') }}</span> [{{ strtoupper(app()->environment()) }}] // ROOT RESTRICTED</div>
            <div>
                <span class="cmd-line-prompt">> HEARTBEAT:</span> 
                <span class="text-success fw-bold">ONLINE</span>
                <span class="ecg-graph-wrap" title="Cardiac System Telemetry">
                    <svg class="ecg-svg" viewBox="0 0 100 24" fill="none">
                        <path class="ecg-line-draw" d="M0,12 L20,12 L26,12 L30,2 L35,22 L40,6 L45,16 L50,12 L100,12" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="text-info font-monospace" style="font-size: 11px;">[SYS_PULSE_STABLE]</span>
            </div>
            <div id="typewriterText"><span class="cmd-line-prompt">> STATUS:</span> READY_FOR_CREDENTIALS<span class="cmd-cursor"></span></div>
        </div>

        <!-- Flash Messages & Error Displays -->
        <x-devzone::alert session />

        <!-- Master Authentication Form -->
        <form action="{{ route('devzone.login.submit') }}" method="POST" id="authForm">
            @csrf

            <div class="cyber-input-wrap">
                <div class="cyber-label">
                    <span><span class="cyber-label-prefix">></span> MASTER_PASSKEY</span>
                    <span class="text-muted" style="font-size: 10.5px;">MIN_LENGTH: 6</span>
                </div>
                <div class="cyber-input-group">
                    <span class="cyber-input-prefix">
                        <i class="bx bx-key"></i>
                    </span>
                    <input 
                        type="password" 
                        class="cyber-input" 
                        id="passkeyInput" 
                        name="passkey" 
                        placeholder="ENTER_DEV_PASSKEY..."
                        required 
                        autocomplete="off"
                        autofocus
                    >
                    <button class="cyber-toggle-btn" type="button" id="togglePasswordBtn" title="Reveal / Conceal Key">
                        <i class="bx bx-show fs-5" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-cyber-submit" id="submitBtn">
                <i class="bx bx-shield-quarter fs-5"></i>
                <span id="btnText">[ EXECUTE // AUTHENTICATE ]</span>
            </button>
        </form>

        <!-- Status & Telemetry Bar -->
        <div class="terminal-statusbar justify-content-center text-center">
            <span><span class="text-info fw-bold">next</span><span class="text-light">SyntaxTech</span> DevZone &copy; {{ date('Y') }}</span>
        </div>
    </div>
</div>

<!-- Local JS Dependencies -->
<script src="{{ \NextSyntaxTech\DevZone\Services\DevZoneService::asset('js/bootstrap.bundle.min.js') }}"></script>

<script>
    // =========================================================
    // 1. Password Visibility Toggle
    // =========================================================
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passInput = document.getElementById('passkeyInput');
    const toggleIcon = document.getElementById('toggleIcon');
    const authForm = document.getElementById('authForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');

    toggleBtn.addEventListener('click', function() {
        const isPassword = passInput.getAttribute('type') === 'password';
        passInput.setAttribute('type', isPassword ? 'text' : 'password');
        toggleIcon.className = isPassword ? 'bx bx-hide fs-5' : 'bx bx-show fs-5';
    });

    // Form submission animation feedback
    authForm.addEventListener('submit', function() {
        submitBtn.style.pointerEvents = 'none';
        submitBtn.style.opacity = '0.85';
        btnText.textContent = '[ VERIFYING CHECKSUM... ]';
    });

    // =========================================================
    // 2. Real-Time UTC System Clock
    // =========================================================
    function updateClock() {
        const now = new Date();
        const hrs = String(now.getUTCHours()).padStart(2, '0');
        const mins = String(now.getUTCMinutes()).padStart(2, '0');
        const secs = String(now.getUTCSeconds()).padStart(2, '0');
        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.textContent = `UTC: ${hrs}:${mins}:${secs}`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    // =========================================================
    // 3. Lightweight Vanilla Matrix Rain Animation (60FPS)
    // =========================================================
    const canvas = document.getElementById('matrixCanvas');
    const ctx = canvas.getContext('2d');

    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    const chars = '01ABCDEFXYZ$%#@*><{}+=~';
    const fontSize = 14;
    let columns = Math.floor(canvas.width / fontSize);
    let drops = Array(columns).fill(1);

    function drawMatrix() {
        ctx.fillStyle = 'rgba(5, 8, 17, 0.08)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.fillStyle = '#06b6d4';
        ctx.font = fontSize + 'px monospace';

        for (let i = 0; i < drops.length; i++) {
            const char = chars.charAt(Math.floor(Math.random() * chars.length));
            const x = i * fontSize;
            const y = drops[i] * fontSize;

            // Highlight leading characters
            if (Math.random() > 0.85) {
                ctx.fillStyle = '#38bdf8';
            } else if (Math.random() > 0.6) {
                ctx.fillStyle = '#10b981';
            } else {
                ctx.fillStyle = 'rgba(6, 182, 212, 0.55)';
            }

            ctx.fillText(char, x, y);

            if (y > canvas.height && Math.random() > 0.985) {
                drops[i] = 0;
            }
            drops[i]++;
        }
    }

    let matrixInterval = setInterval(drawMatrix, 45);

    // Pause matrix when window/tab is hidden to save CPU/battery
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(matrixInterval);
        } else {
            matrixInterval = setInterval(drawMatrix, 45);
        }
    });
</script>

</body>
</html>
