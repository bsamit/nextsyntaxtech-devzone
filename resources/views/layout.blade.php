<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - NextSyntaxTech DevZone</title>

    <!-- Immediate Theme Application to prevent FOUC (Flash of Unstyled Content) -->
    <script>
        (function() {
            var savedTheme = localStorage.getItem('devzone_theme') || localStorage.getItem('devpanel_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        /* ============================================================== */
        /* THEME VARIABLES                                                */
        /* ============================================================== */
        :root, [data-theme="light"] {
            --bg-body: #f1f5f9;            /* Soft slate off-white, low glare */
            --bg-header: #ffffff;
            --bg-card: #ffffff;
            --bg-card-sub: #f8fafc;
            --bg-card-hover: #f1f5f9;
            --border-color: #e2e8f0;
            --border-light: #cbd5e1;
            --border-focus: #3b82f6;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --info: #0284c7;
            --text-main: #0f172a;          /* Slate 900 - maximum clarity */
            --text-muted: #475569;         /* Slate 600 - crisp readable contrast */
            --text-sub: #64748b;           /* Slate 500 */
            --table-header-bg: #f8fafc;
            --table-row-hover: #f8fafc;
            --table-border: #e2e8f0;
            --input-bg: #ffffff;
            --input-border: #cbd5e1;
            --input-text: #0f172a;
            --badge-neutral-bg: #f1f5f9;
            --badge-neutral-text: #334155;
            --command-code-bg: #e2e8f0;
            --command-code-text: #0f172a;
            --terminal-bg: #0b1120;
            --terminal-text: #38bdf8;
            --card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.06), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
            --header-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            --btn-sec-bg: #ffffff;
            --btn-sec-text: #1e293b;
            --btn-sec-border: #cbd5e1;
            --btn-sec-hover: #f8fafc;
            --pill-local-bg: rgba(16, 185, 129, 0.12);
            --pill-local-color: #047857;
            --pill-prod-bg: rgba(239, 68, 68, 0.12);
            --pill-prod-color: #b91c1c;
            --pill-debug-on-bg: rgba(245, 158, 11, 0.12);
            --pill-debug-on-color: #b45309;
            --pill-debug-off-bg: rgba(100, 116, 139, 0.12);
            --pill-debug-off-color: #475569;
        }

        [data-theme="dark"] {
            --bg-body: #0b0f19;            /* Deep rich slate */
            --bg-header: #0f172a;
            --bg-card: #111827;
            --bg-card-sub: #172033;
            --bg-card-hover: #1e293b;
            --border-color: #1e293b;
            --border-light: #334155;
            --border-focus: #60a5fa;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            --text-main: #f8fafc;          /* Crisp clear white */
            --text-muted: #94a3b8;         /* Clear readable gray */
            --text-sub: #64748b;
            --table-header-bg: #090e1a;
            --table-row-hover: #1e293b;
            --table-border: #1e293b;
            --input-bg: #172033;
            --input-border: #334155;
            --input-text: #f8fafc;
            --badge-neutral-bg: #1e293b;
            --badge-neutral-text: #cbd5e1;
            --command-code-bg: #1e293b;
            --command-code-text: #38bdf8;
            --terminal-bg: #050811;
            --terminal-text: #38bdf8;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
            --header-shadow: 0 2px 12px rgba(0, 0, 0, 0.25);
            --btn-sec-bg: #1e293b;
            --btn-sec-text: #f8fafc;
            --btn-sec-border: #334155;
            --btn-sec-hover: #283548;
            --pill-local-bg: rgba(16, 185, 129, 0.15);
            --pill-local-color: #34d399;
            --pill-prod-bg: rgba(239, 68, 68, 0.15);
            --pill-prod-color: #f87171;
            --pill-debug-on-bg: rgba(245, 158, 11, 0.15);
            --pill-debug-on-color: #fbbf24;
            --pill-debug-off-bg: rgba(148, 163, 184, 0.15);
            --pill-debug-off-color: #cbd5e1;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            padding-bottom: 60px;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* Top Header Navigation */
        .dev-header {
            background: var(--bg-header);
            border-bottom: 1px solid var(--border-color);
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: var(--header-shadow);
            transition: background-color 0.25s ease, border-color 0.25s ease;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text-main);
        }
        .header-brand:hover {
            color: var(--primary);
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            flex-shrink: 0;
        }

        /* Status Pills */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 0.3px;
        }
        .pill-local { background: var(--pill-local-bg); color: var(--pill-local-color); border: 1px solid var(--pill-local-color); }
        .pill-prod { background: var(--pill-prod-bg); color: var(--pill-prod-color); border: 1px solid var(--pill-prod-color); }
        .pill-debug-on { background: var(--pill-debug-on-bg); color: var(--pill-debug-on-color); border: 1px solid var(--pill-debug-on-color); }
        .pill-debug-off { background: var(--pill-debug-off-bg); color: var(--pill-debug-off-color); border: 1px solid var(--border-color); }

        /* KPI Quick Metric Cards */
        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 18px 20px;
            transition: all 0.2s ease;
            box-shadow: var(--card-shadow);
        }
        .kpi-card:hover {
            border-color: var(--border-light);
            transform: translateY(-2px);
        }
        .kpi-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .kpi-value {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 2px;
            line-height: 1.2;
        }
        .kpi-sub {
            font-size: 12.5px;
            color: var(--text-muted);
        }

        /* Custom Modern Tabs */
        .dev-nav-tabs {
            border-bottom: 2px solid var(--border-color);
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding-bottom: 0;
            margin-bottom: 24px;
        }
        .dev-nav-tab {
            padding: 10px 16px;
            border-radius: 8px 8px 0 0;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text-muted);
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s ease;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            white-space: nowrap;
        }
        .dev-nav-tab:hover {
            color: var(--text-main);
            background: var(--bg-card-hover);
        }
        .dev-nav-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background: rgba(37, 99, 235, 0.08);
        }

        /* Card System */
        .dev-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 24px;
            overflow: hidden;
            transition: background-color 0.25s ease, border-color 0.25s ease;
        }
        .dev-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-card-sub);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .dev-card-body {
            padding: 20px;
        }

        /* Standalone Table Component */
        .dev-table-wrapper {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }
        .dev-table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }
        .dev-table th {
            background: var(--table-header-bg);
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--table-border);
            white-space: nowrap;
        }
        .dev-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--table-border);
            color: var(--text-main);
            font-size: 13.5px;
            vertical-align: middle;
            background: var(--bg-card);
        }
        .dev-table tr:hover td {
            background: var(--table-row-hover);
        }
        .dev-table tr:last-child td {
            border-bottom: none;
        }

        /* Action Buttons */
        .btn-action-primary {
            background: var(--primary);
            color: #ffffff !important;
            font-weight: 600;
            border: none;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .btn-action-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }
        .btn-action-secondary {
            background: var(--btn-sec-bg);
            color: var(--btn-sec-text) !important;
            border: 1px solid var(--btn-sec-border);
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-action-secondary:hover {
            background: var(--btn-sec-hover);
            border-color: var(--border-light);
        }

        /* Theme Toggle Button */
        .theme-toggle-btn {
            background: var(--btn-sec-bg);
            color: var(--btn-sec-text);
            border: 1px solid var(--btn-sec-border);
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .theme-toggle-btn:hover {
            background: var(--btn-sec-hover);
            border-color: var(--border-light);
        }

        /* Inputs & Search Bars */
        .dev-form-control {
            background: var(--input-bg) !important;
            color: var(--input-text) !important;
            border: 1px solid var(--input-border) !important;
            border-radius: 6px;
            font-size: 13px;
            padding: 6px 12px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .dev-form-control:focus {
            border-color: var(--border-focus) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
            outline: none;
        }
        .dev-form-control::placeholder {
            color: var(--text-sub) !important;
        }
        .dev-input-group-text {
            background: var(--bg-card-sub) !important;
            color: var(--text-muted) !important;
            border: 1px solid var(--input-border) !important;
            font-size: 14px;
        }

        /* Command Preview Code Boxes */
        .command-code-box {
            background: var(--command-code-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 6px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12px;
            overflow-x: auto;
        }
        .command-prefix {
            color: var(--primary);
            font-weight: 700;
            user-select: none;
            font-size: 13px;
        }
        .command-code-text {
            color: var(--command-code-text);
            font-weight: 700;
            white-space: nowrap;
        }

        .command-subcard {
            background: var(--bg-card-sub);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .command-subcard:hover {
            border-color: var(--border-light);
            background: var(--bg-card-hover);
        }

        /* High-Contrast Migration Badges */
        .badge-status-ran {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            background: #059669;
            color: #ffffff !important;
            border: 1px solid #047857;
            letter-spacing: 0.3px;
        }
        .badge-status-pending {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            background: #d97706;
            color: #ffffff !important;
            border: 1px solid #b45309;
            letter-spacing: 0.3px;
        }

        /* High-Contrast Artisan Category Badges */
        .artisan-cat-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .cat-optimization { background: #059669; color: #ffffff !important; }
        .cat-cache { background: #2563eb; color: #ffffff !important; }
        .cat-views { background: #0284c7; color: #ffffff !important; }
        .cat-filesystem { background: #7c3aed; color: #ffffff !important; }
        .cat-events { background: #475569; color: #ffffff !important; }
        .cat-speedup { background: #0891b2; color: #ffffff !important; }
        .cat-default { background: #64748b; color: #ffffff !important; }

        /* Environment Details Table (Theme Adaptive & Dark Mode Safe) */
        .env-details-table {
            width: 100%;
            margin-bottom: 0;
            background: transparent !important;
        }
        .env-details-table tr {
            background: transparent !important;
        }
        .env-details-table td {
            padding: 9px 6px;
            background: transparent !important;
            border-bottom: 1px dashed var(--border-color);
            font-size: 13px;
            vertical-align: middle;
        }
        .env-details-table tr:last-child td {
            border-bottom: none;
        }
        .env-label {
            color: var(--text-muted);
            font-size: 12.5px;
        }
        .env-val {
            color: var(--text-main);
            font-weight: 700;
            text-align: right;
            font-size: 13px;
        }

        /* Activity Logs Styling (Crystal Clear in Light & Dark Mode) */
        .badge-log-success {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 4px;
            font-size: 11.5px;
            font-weight: 700;
            background: #059669;
            color: #ffffff !important;
        }
        .badge-log-error {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 4px;
            font-size: 11.5px;
            font-weight: 700;
            background: #dc2626;
            color: #ffffff !important;
        }
        .badge-log-notice {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 4px;
            font-size: 11.5px;
            font-weight: 700;
            background: #d97706;
            color: #ffffff !important;
        }
        .activity-time {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .activity-action {
            color: var(--text-main);
            font-weight: 700;
            font-size: 13px;
        }
        .activity-details {
            color: var(--text-main);
            font-size: 12.5px;
            line-height: 1.45;
            word-break: break-word;
        }
        .activity-ip {
            color: var(--info);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12px;
            white-space: nowrap;
        }

        /* Terminal Output Console */
        .terminal-box {
            background: var(--terminal-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 14px 18px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12.5px;
            color: var(--terminal-text);
            max-height: 420px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
            line-height: 1.5;
        }

        /* Modal Adaptive Theming */
        .modal-content {
            background: var(--bg-card) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-main) !important;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25) !important;
            border-radius: 12px;
        }
        .modal-header, .modal-footer {
            border-color: var(--border-color) !important;
            background: var(--bg-card-sub);
        }
        [data-theme="dark"] .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* SweetAlert2 Theming */
        [data-theme="dark"] .swal2-popup {
            background: #111827 !important;
            color: #f8fafc !important;
            border: 1px solid #1e293b !important;
        }
        [data-theme="dark"] .swal2-title,
        [data-theme="dark"] .swal2-html-container {
            color: #f8fafc !important;
        }
        [data-theme="light"] .swal2-popup {
            background: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 20px 40px rgba(0,0,0,0.12) !important;
        }
        [data-theme="light"] .swal2-title,
        [data-theme="light"] .swal2-html-container {
            color: #0f172a !important;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="dev-header">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('devzone.index') }}" class="header-brand">
                    <div class="brand-icon">
                        <i class="bx bx-terminal"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6 lh-1">NextSyntaxTech DevZone</div>
                        <small class="text-secondary" style="font-size: 11px;">{{ config('devzone.admin_email', 'amitsaha.cse@gmail.com') }}</small>
                    </div>
                </a>

                <div class="d-none d-md-flex align-items-center gap-2 ms-2">
                    <span class="status-pill {{ $systemInfo['app_env'] === 'production' ? 'pill-prod' : 'pill-local' }}">
                        <i class="bx bxs-circle" style="font-size: 8px;"></i> {{ strtoupper($systemInfo['app_env']) }}
                    </span>
                    <span class="status-pill {{ $systemInfo['app_debug'] ? 'pill-debug-on' : 'pill-debug-off' }}" id="headerDebugBadge">
                        <i class="bx bx-bug"></i> DEBUG: {{ $systemInfo['app_debug'] ? 'ON' : 'OFF' }}
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Theme Toggle Button -->
                <button type="button" class="theme-toggle-btn" id="themeToggleBtn" onclick="toggleTheme()" title="Toggle Light / Dark Theme">
                    <i class="bx bx-moon" id="themeIcon"></i>
                    <span id="themeLabel">Dark Mode</span>
                </button>

                @if(!empty($apiDocInfo) && $apiDocInfo['active_type'] !== 'none')
                    <a href="{{ $apiDocInfo['primary_url'] }}" target="_blank" class="btn btn-action-secondary" title="Open {{ $apiDocInfo['primary_label'] }}">
                        <i class="bx bx-file"></i> {{ $apiDocInfo['primary_label'] }} <i class="bx bx-link-external small"></i>
                    </a>
                @else
                    <a href="{{ url('api/documentation') }}" target="_blank" class="btn btn-action-secondary" title="Open Interactive API Documentation">
                        <i class="bx bx-file"></i> API Docs <i class="bx bx-link-external small"></i>
                    </a>
                @endif

                <form action="{{ route('devzone.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-action-secondary text-danger border-danger-subtle" title="Lock & Close Developer Session">
                        <i class="bx bx-log-out"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container-fluid px-4 mt-4">
        @yield('content')
    </main>

    <!-- Global Console Output Modal -->
    <div class="modal fade" id="terminalOutputModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-3">
                    <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2 text-main">
                        <i class="bx bx-terminal text-primary"></i> <span id="modalOutputTitle">Console Execution Output</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2 small text-secondary">
                        <span id="modalOutputMeta">Exit Code: 0</span>
                        <button class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-semibold" onclick="copyConsoleOutput()">
                            <i class="bx bx-copy"></i> Copy Output
                        </button>
                    </div>
                    <div class="terminal-box" id="modalOutputContent">Processing command...</div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-action-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // =============================================================
        // THEME TOGGLE & PERSISTENCE (Default: Light)
        // =============================================================
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            const icon = document.getElementById('themeIcon');
            const label = document.getElementById('themeLabel');
            if (theme === 'dark') {
                if (icon) icon.className = 'bx bx-sun text-warning';
                if (label) label.textContent = 'Light Mode';
            } else {
                if (icon) icon.className = 'bx bx-moon text-primary';
                if (label) label.textContent = 'Dark Mode';
            }
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            applyTheme(newTheme);
            localStorage.setItem('devzone_theme', newTheme);
        }

        // =============================================================
        // TAB & SECTION SELECTION PERSISTENCE
        // =============================================================
        document.addEventListener('DOMContentLoaded', () => {
            // Restore theme
            const activeTheme = localStorage.getItem('devzone_theme') || localStorage.getItem('devpanel_theme') || 'light';
            applyTheme(activeTheme);

            // Restore active tab (URL hash has precedence, then localStorage, then default)
            const hash = window.location.hash ? window.location.hash.replace('#', '') : null;
            const savedTab = localStorage.getItem('devzone_active_tab') || localStorage.getItem('devpanel_active_tab');
            const initialTab = hash || savedTab || 'dashboard';
            switchTab(initialTab);
        });

        function switchTab(tabId) {
            const targetPane = document.getElementById('tab-' + tabId);
            if (!targetPane) {
                tabId = 'dashboard';
            }

            document.querySelectorAll('.dev-nav-tab').forEach(tab => {
                tab.classList.toggle('active', tab.getAttribute('data-tab') === tabId);
            });
            document.querySelectorAll('.tab-content-pane').forEach(pane => {
                pane.style.display = (pane.id === 'tab-' + tabId) ? 'block' : 'none';
            });

            window.location.hash = tabId;
            localStorage.setItem('devzone_active_tab', tabId);
        }

        // Show Console Modal Utility
        function showTerminalModal(title, output, meta = '') {
            document.getElementById('modalOutputTitle').textContent = title;
            document.getElementById('modalOutputContent').textContent = output || 'Execution finished with no text output.';
            document.getElementById('modalOutputMeta').textContent = meta || 'Status: Finished';
            new bootstrap.Modal(document.getElementById('terminalOutputModal')).show();
        }

        function copyConsoleOutput() {
            const text = document.getElementById('modalOutputContent').textContent;
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Copied to clipboard', showConfirmButton: false, timer: 1500 });
            });
        }
    </script>

    @stack('scripts')

</body>
</html>
