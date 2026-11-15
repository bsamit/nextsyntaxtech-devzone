<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>nextSyntaxTech DevZone - Initial Security Setup Wizard</title>
    <!-- Local CSS Dependencies (Zero CDN, 100% Offline) -->
    <link href="{{ \NextSyntaxTech\DevZone\Services\DevZoneService::asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ \NextSyntaxTech\DevZone\Services\DevZoneService::asset('css/boxicons.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --term-bg: #050811;
            --term-card-bg: rgba(9, 14, 28, 0.92);
            --term-border: rgba(56, 189, 248, 0.28);
            --term-cyan: #06b6d4;
            --term-blue: #3b82f6;
            --term-green: #10b981;
            --term-neon-green: #00ff66;
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
            padding: 30px 16px;
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
            opacity: 0.18;
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

        /* CRT Scanline Overlay */
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

        /* Ambient Glow */
        .ambient-glow {
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.15) 0%, rgba(6, 182, 212, 0.05) 50%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            filter: blur(40px);
            pointer-events: none;
        }

        /* Heart Pulse Animation */
        .heart-beat-anim {
            display: inline-block;
            animation: heartPulseRhythm 1.3s infinite ease-in-out;
            transform-origin: center center;
            vertical-align: middle;
        }

        @keyframes heartPulseRhythm {
            0% { transform: scale(1); filter: drop-shadow(0 0 2px rgba(239, 68, 68, 0.4)); }
            14% { transform: scale(1.35); filter: drop-shadow(0 0 10px rgba(239, 68, 68, 0.95)); }
            28% { transform: scale(1.05); filter: drop-shadow(0 0 4px rgba(239, 68, 68, 0.5)); }
            42% { transform: scale(1.22); filter: drop-shadow(0 0 8px rgba(239, 68, 68, 0.8)); }
            70% { transform: scale(1); filter: drop-shadow(0 0 2px rgba(239, 68, 68, 0.4)); }
            100% { transform: scale(1); filter: drop-shadow(0 0 2px rgba(239, 68, 68, 0.4)); }
        }

        .heart-pulse-box {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
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
            0% { transform: scale(0.6); opacity: 1; }
            60% { transform: scale(2.2); opacity: 0.15; }
            100% { transform: scale(2.8); opacity: 0; }
        }

        /* Branding: nextSyntaxTech */
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

        /* Setup Window Container */
        .setup-window {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 540px;
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
            animation: setup-pop 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes setup-pop {
            0% { opacity: 0; transform: scale(0.94) translateY(16px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* Title Bar */
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
        }
        .window-dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            display: inline-block;
        }
        .dot-close { background: #ef4444; }
        .dot-min { background: #f59e0b; }
        .dot-max { background: #10b981; }

        .terminal-title {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .terminal-body {
            padding: 24px 28px;
        }

        .cmd-readout {
            background: rgba(4, 7, 18, 0.9);
            border: 1px solid rgba(6, 182, 212, 0.25);
            border-left: 3px solid var(--term-cyan);
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: 11.5px;
            line-height: 1.6;
            color: #94a3b8;
        }
        .cmd-line-prompt {
            color: var(--term-cyan);
            font-weight: 700;
        }
        .cmd-highlight {
            color: #ffffff;
            font-weight: 600;
        }

        /* Form Controls */
        .cyber-input-wrap {
            margin-bottom: 18px;
        }
        .cyber-label {
            font-size: 11.5px;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 6px;
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
            box-shadow: 0 0 16px rgba(6, 182, 212, 0.35), inset 0 0 10px rgba(6, 182, 212, 0.1);
        }

        .cyber-input-prefix {
            padding: 10px 14px;
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
            padding: 10px 14px;
            font-family: inherit;
            font-size: 13.5px;
            outline: none !important;
        }
        .cyber-input::placeholder {
            color: #475569 !important;
            font-size: 12px;
        }

        .cyber-toggle-btn {
            background: transparent;
            border: none;
            color: #64748b;
            padding: 0 12px;
            cursor: pointer;
            transition: color 0.18s ease;
            display: flex;
            align-items: center;
        }
        .cyber-toggle-btn:hover {
            color: var(--term-cyan);
        }

        .btn-cyber-submit {
            position: relative;
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border: 1px solid #10b981;
            border-radius: 8px;
            color: #ffffff;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 800;
            letter-spacing: 0.8px;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-cyber-submit:hover {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.55);
            transform: translateY(-1px);
        }

        .terminal-statusbar {
            margin-top: 20px;
            padding-top: 14px;
            border-top: 1px dashed rgba(56, 189, 248, 0.2);
            font-size: 11.5px;
            color: #64748b;
        }

        /* Alert Styling */
        .devzone-alert {
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12.5px;
            font-family: inherit;
            margin-bottom: 16px;
        }
        .devzone-alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #6ee7b7; }
        .devzone-alert-danger  { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; }
        .devzone-alert-warning { background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); color: #fcd34d; }
        .devzone-alert-info    { background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); color: #93c5fd; }
        .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    </style>
</head>
<body>

<!-- Matrix Canvas Background -->
<canvas id="matrixCanvas"></canvas>
<div class="cyber-grid-overlay"></div>
<div class="scanline-overlay"></div>
<div class="ambient-glow"></div>

<!-- Setup Window Container -->
<div class="setup-window">
    <!-- Window Title Bar -->
    <div class="terminal-titlebar">
        <div class="window-controls">
            <span class="window-dot dot-close"></span>
            <span class="window-dot dot-min"></span>
            <span class="window-dot dot-max"></span>
        </div>
        <div class="terminal-title">
            <span class="brand-nextsyntaxtech">
                <span class="b-next">next</span><span class="b-syntax">Syntax</span><span class="b-tech">Tech</span>
            </span>
            <span style="color: var(--term-cyan); opacity: 0.7;">//</span>
            <span>Security Setup Wizard</span>
        </div>
        <div>
            <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 12px;">
                <span class="heart-pulse-box">
                    <span class="heart-pulse-ring"></span>
                    <i class="bx bxs-heart text-danger heart-beat-anim" style="font-size: 9px;"></i>
                </span>
                INITIAL_SETUP
            </span>
        </div>
    </div>

    <!-- Inner Body -->
    <div class="terminal-body">
        <!-- Diagnostic Readout -->
        <div class="cmd-readout">
            <div><span class="cmd-line-prompt">> NOTICE:</span> <span class="cmd-highlight">Fresh Installation Detected</span></div>
            <div><span class="cmd-line-prompt">> TARGET:</span> {{ config('app.name', 'NextSyntaxTech') }} [{{ strtoupper(app()->environment()) }}]</div>
            <div><span class="cmd-line-prompt">> ACTION:</span> Configure your custom master developer credentials below.</div>
        </div>

        <x-devzone::alert session />

        <form action="{{ route('devzone.setup.submit') }}" method="POST" id="setupForm">
            @csrf

            <!-- Admin Email -->
            <div class="cyber-input-wrap">
                <div class="cyber-label">
                    <span><span class="cyber-label-prefix">></span> ADMINISTRATOR_EMAIL</span>
                    <span class="text-danger" style="font-size: 11px;">* Required</span>
                </div>
                <div class="cyber-input-group">
                    <span class="cyber-input-prefix"><i class="bx bx-envelope"></i></span>
                    <input 
                        type="email" 
                        class="cyber-input" 
                        id="emailInput" 
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="your-email@domain.com"
                        required
                        autocomplete="email"
                        autofocus
                    >
                </div>
            </div>

            <!-- Master Passkey -->
            <div class="cyber-input-wrap">
                <div class="cyber-label">
                    <span><span class="cyber-label-prefix">></span> MASTER_PASSKEY</span>
                    <span class="text-muted" style="font-size: 10.5px;">MIN 6 CHARACTERS</span>
                </div>
                <div class="cyber-input-group">
                    <span class="cyber-input-prefix"><i class="bx bx-key"></i></span>
                    <input 
                        type="password" 
                        class="cyber-input" 
                        id="passkeyInput" 
                        name="passkey" 
                        placeholder="Set your secret master passkey..."
                        minlength="6"
                        required
                        autocomplete="new-password"
                    >
                    <button class="cyber-toggle-btn" type="button" onclick="toggleVisibility('passkeyInput', this)" title="Show/Hide">
                        <i class="bx bx-show fs-5"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Master Passkey -->
            <div class="cyber-input-wrap">
                <div class="cyber-label">
                    <span><span class="cyber-label-prefix">></span> CONFIRM_PASSKEY</span>
                    <span class="text-muted" style="font-size: 10.5px;">MUST MATCH</span>
                </div>
                <div class="cyber-input-group">
                    <span class="cyber-input-prefix"><i class="bx bx-check-shield"></i></span>
                    <input 
                        type="password" 
                        class="cyber-input" 
                        id="passkeyConfirmInput" 
                        name="passkey_confirmation" 
                        placeholder="Re-type secret master passkey..."
                        minlength="6"
                        required
                        autocomplete="new-password"
                    >
                    <button class="cyber-toggle-btn" type="button" onclick="toggleVisibility('passkeyConfirmInput', this)" title="Show/Hide">
                        <i class="bx bx-show fs-5"></i>
                    </button>
                </div>
            </div>

            <!-- Custom URL Path Prefix -->
            <div class="cyber-input-wrap mb-4">
                <div class="cyber-label">
                    <span><span class="cyber-label-prefix">></span> PANEL_URL_PREFIX</span>
                    <span class="text-muted" style="font-size: 10.5px;">DEFAULT: developer-panel</span>
                </div>
                <div class="cyber-input-group">
                    <span class="cyber-input-prefix">/</span>
                    <input 
                        type="text" 
                        class="cyber-input" 
                        id="pathInput" 
                        name="path" 
                        value="{{ old('path', config('devzone.path', 'developer-panel')) }}" 
                        placeholder="developer-panel"
                    >
                </div>
            </div>

            <button type="submit" class="btn-cyber-submit" id="submitBtn">
                <i class="bx bx-save fs-5"></i>
                <span id="btnText">[ SAVE CREDENTIALS & INITIALIZE ]</span>
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
    function toggleVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        const icon = btn.querySelector('i');
        if (icon) {
            icon.className = isPassword ? 'bx bx-hide fs-5' : 'bx bx-show fs-5';
        }
    }

    const setupForm = document.getElementById('setupForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');

    setupForm.addEventListener('submit', function(e) {
        const pass = document.getElementById('passkeyInput').value;
        const confirm = document.getElementById('passkeyConfirmInput').value;

        if (pass !== confirm) {
            e.preventDefault();
            alert('Passkey confirmation does not match!');
            return;
        }

        submitBtn.style.pointerEvents = 'none';
        submitBtn.style.opacity = '0.85';
        btnText.textContent = '[ ENCRYPTING & SAVING CONFIGURATION... ]';
    });

    // Matrix Rain Canvas
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
