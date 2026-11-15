<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NextSyntaxTech') }} - NextSyntaxTech DevZone</title>

    <!-- Immediate Theme Application to prevent FOUC (Flash of Unstyled Content) -->
    <script>
        (function() {
            var savedTheme = localStorage.getItem('devzone_theme') || localStorage.getItem('devpanel_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    
    <!-- Local CSS Dependencies (Zero CDN, 100% Offline) -->
    <link href="{{ \NextSyntaxTech\DevZone\Services\DevZoneService::asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ \NextSyntaxTech\DevZone\Services\DevZoneService::asset('css/boxicons.min.css') }}" rel="stylesheet">

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
        
        /* Heart Pulse Animation & Telemetry Pill */
        .pill-pulse {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.35);
        }
        [data-theme="dark"] .pill-pulse {
            background: rgba(239, 68, 68, 0.16);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, 0.45);
        }
        .heart-beat-anim {
            display: inline-block;
            animation: heartPulseDouble 1.3s infinite ease-in-out;
            transform-origin: center center;
            vertical-align: middle;
        }
        @keyframes heartPulseDouble {
            0% {
                transform: scale(1);
                filter: drop-shadow(0 0 2px rgba(239, 68, 68, 0.4));
            }
            14% {
                transform: scale(1.35);
                filter: drop-shadow(0 0 8px rgba(239, 68, 68, 0.85));
            }
            28% {
                transform: scale(1.05);
                filter: drop-shadow(0 0 3px rgba(239, 68, 68, 0.45));
            }
            42% {
                transform: scale(1.22);
                filter: drop-shadow(0 0 6px rgba(239, 68, 68, 0.7));
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
        .heart-pulse-wave-box {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
        }
        .heart-pulse-wave-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 12px;
            height: 12px;
            margin-top: -6px;
            margin-left: -6px;
            border-radius: 50%;
            border: 1.5px solid rgba(239, 68, 68, 0.8);
            animation: pulseWaveSonar 1.3s infinite cubic-bezier(0.2, 0.8, 0.4, 1);
            pointer-events: none;
        }
        @keyframes pulseWaveSonar {
            0% { transform: scale(0.6); opacity: 1; }
            60% { transform: scale(2.2); opacity: 0.15; }
            100% { transform: scale(2.8); opacity: 0; }
        }

        /* Branding: nextSyntaxTech */
        .brand-next-syntax-tech {
            font-weight: 800;
            letter-spacing: 0.3px;
        }
        .brand-next-syntax-tech .b-next {
            color: #0284c7;
            font-weight: 800;
        }
        [data-theme="dark"] .brand-next-syntax-tech .b-next {
            color: #38bdf8;
        }
        .brand-next-syntax-tech .b-syntax {
            color: var(--text-main);
            font-weight: 700;
        }
        .brand-next-syntax-tech .b-tech {
            color: var(--primary);
            font-weight: 800;
        }

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

        /* Icon-Only Header Buttons */
        .btn-header-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: var(--btn-sec-bg);
            color: var(--btn-sec-text) !important;
            border: 1px solid var(--btn-sec-border);
            font-size: 18px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            text-decoration: none;
            position: relative;
        }
        .btn-header-icon:hover {
            background: var(--btn-sec-hover);
            border-color: var(--border-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .btn-header-icon:active {
            transform: translateY(0);
        }
        .btn-header-icon.btn-danger-hover:hover {
            background: rgba(239, 68, 68, 0.12) !important;
            border-color: rgba(239, 68, 68, 0.4) !important;
            color: #ef4444 !important;
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

        /* Custom DevZone Alert Component Theming */
        .devzone-alert {
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 13.5px;
            border-width: 1px;
            border-style: solid;
            transition: all 0.2s ease;
        }
        .devzone-alert .btn-close {
            padding: 1rem 1rem;
        }
        .devzone-alert-icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            margin-top: 2px;
        }

        [data-theme="light"] .devzone-alert-success {
            background-color: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        [data-theme="light"] .devzone-alert-success .devzone-alert-icon-wrap { color: #059669; }

        [data-theme="light"] .devzone-alert-danger {
            background-color: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }
        [data-theme="light"] .devzone-alert-danger .devzone-alert-icon-wrap { color: #dc2626; }

        [data-theme="light"] .devzone-alert-warning {
            background-color: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }
        [data-theme="light"] .devzone-alert-warning .devzone-alert-icon-wrap { color: #d97706; }

        [data-theme="light"] .devzone-alert-info {
            background-color: #f0f9ff;
            border-color: #bae6fd;
            color: #075985;
        }
        [data-theme="light"] .devzone-alert-info .devzone-alert-icon-wrap { color: #0284c7; }

        [data-theme="dark"] .devzone-alert-success {
            background-color: rgba(16, 185, 129, 0.12);
            border-color: rgba(16, 185, 129, 0.35);
            color: #6ee7b7;
        }
        [data-theme="dark"] .devzone-alert-success .devzone-alert-icon-wrap { color: #34d399; }

        [data-theme="dark"] .devzone-alert-danger {
            background-color: rgba(239, 68, 68, 0.12);
            border-color: rgba(239, 68, 68, 0.35);
            color: #fca5a5;
        }
        [data-theme="dark"] .devzone-alert-danger .devzone-alert-icon-wrap { color: #f87171; }

        [data-theme="dark"] .devzone-alert-warning {
            background-color: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.35);
            color: #fcd34d;
        }
        [data-theme="dark"] .devzone-alert-warning .devzone-alert-icon-wrap { color: #fbbf24; }

        [data-theme="dark"] .devzone-alert-info {
            background-color: rgba(59, 130, 246, 0.12);
            border-color: rgba(59, 130, 246, 0.35);
            color: #93c5fd;
        }
        [data-theme="dark"] .devzone-alert-info .devzone-alert-icon-wrap { color: #60a5fa; }
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
                        <div class="fw-bold fs-6 lh-1 brand-next-syntax-tech">
                            <span class="b-next">nextSyntaxTech</span> <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 11px;">DevZone</span>
                        </div>
                        <small class="text-secondary" style="font-size: 11px;">{{ config('devzone.admin_email') ?? 'Authorized Developer Session' }}</small>
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
                <!-- Home Page (Main Site Base URL in New Tab) -->
                <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" class="btn-header-icon" title="Go to Application Home (Main Site)">
                    <i class="bx bx-home-alt text-primary"></i>
                </a>

                <!-- Change Master Credentials Button (Icon Only) -->
                <button type="button" class="btn-header-icon" data-bs-toggle="modal" data-bs-target="#changeCredentialsModal" title="Security & Master Credentials">
                    <i class="bx bx-shield-quarter text-primary"></i>
                </button>

                <!-- About & Version Updates Button (Icon Only) -->
                <button type="button" class="btn-header-icon" data-bs-toggle="modal" data-bs-target="#aboutDevZoneModal" title="About nextSyntaxTech DevZone & Updates">
                    <i class="bx bx-info-circle text-info"></i>
                </button>

                <!-- Theme Toggle Button (Icon Only) -->
                <button type="button" class="btn-header-icon" id="themeToggleBtn" onclick="toggleTheme()" title="Toggle Dark / Light Theme">
                    <i class="bx bx-moon text-primary" id="themeIcon"></i>
                </button>

                @if(!empty($apiDocInfo) && !empty($apiDocInfo['has_api_docs']) && $apiDocInfo['active_type'] !== 'none' && !empty($apiDocInfo['primary_url']))
                    <a href="{{ $apiDocInfo['primary_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-header-icon" title="Open {{ $apiDocInfo['primary_label'] }}">
                        <i class="bx bx-file text-warning"></i>
                    </a>
                @endif

                <!-- Logout Button (Icon Only) -->
                <form action="{{ route('devzone.logout') }}" method="POST" class="d-inline m-0 p-0">
                    @csrf
                    <button type="submit" class="btn-header-icon btn-danger-hover text-danger border-danger-subtle" title="Lock & Close Developer Session">
                        <i class="bx bx-log-out"></i>
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

    <!-- Change Master Credentials Modal (Zero-CDN, In-Modal Verification) -->
    <div class="modal fade devzone-dialog-modal" id="changeCredentialsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
            <div class="modal-content dev-card border-0 shadow-lg">
                <div class="modal-header py-3 px-4 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 rounded-2 bg-primary bg-opacity-10 text-primary">
                            <i class="bx bx-shield-quarter fs-4"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0">
                                <span class="brand-next-syntax-tech"><span class="b-next">nextSyntaxTech</span></span> Security
                            </h6>
                            <small class="text-muted" style="font-size: 11px;">Update Master Developer Credentials</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="changeCredentialsForm" onsubmit="submitChangeCredentials(event)">
                    @csrf
                    <div class="modal-body p-4">
                        <!-- Alert message container inside modal -->
                        <div id="credModalAlert" class="d-none mb-3"></div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted d-flex align-items-center justify-content-between">
                                <span><i class="bx bx-lock-alt text-primary me-1"></i> Current Master Passkey</span>
                                <span class="text-danger small">* Required</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="credCurrentPasskey" name="current_passkey" placeholder="Enter current passkey..." required autocomplete="current-password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('credCurrentPasskey', this)" title="Show / Hide">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                            <div class="form-text" style="font-size: 11px;">Current passkey will be verified before new credentials are saved.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted d-flex align-items-center justify-content-between">
                                <span><i class="bx bx-key text-success me-1"></i> New Master Passkey</span>
                                <span class="text-muted small">Min 6 characters</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="credNewPasskey" name="new_passkey" placeholder="Enter new passkey..." minlength="6" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('credNewPasskey', this)" title="Show / Hide">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted d-flex align-items-center justify-content-between">
                                <span><i class="bx bx-check-double text-info me-1"></i> Confirm New Passkey</span>
                                <span class="text-muted small">Must match</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="credNewPasskeyConfirm" name="new_passkey_confirmation" placeholder="Repeat new passkey..." minlength="6" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('credNewPasskeyConfirm', this)" title="Show / Hide">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted">
                                <i class="bx bx-envelope text-warning me-1"></i> Administrator Email <span class="fw-normal text-muted">(Optional)</span>
                            </label>
                            <input type="email" class="form-control" id="credAdminEmail" name="admin_email" value="{{ config('devzone.admin_email', '') }}" placeholder="admin@example.com">
                        </div>
                    </div>
                    <div class="modal-footer py-2 px-4 border-top bg-card-sub d-flex justify-content-between">
                        <button type="button" class="btn btn-action-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" id="credSubmitBtn">
                            <i class="bx bx-save"></i> <span id="credSubmitBtnText">Update Credentials</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- About nextSyntaxTech DevZone & Version Update Modal -->
    <div class="modal fade devzone-dialog-modal" id="aboutDevZoneModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
            <div class="modal-content dev-card border-0 shadow-lg">
                <div class="modal-header py-3 px-4 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 rounded-2 bg-info bg-opacity-10 text-info">
                            <i class="bx bx-info-circle fs-4"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0">
                                About <span class="brand-next-syntax-tech"><span class="b-next">next</span><span class="b-syntax">Syntax</span><span class="b-tech">Tech</span></span> DevZone
                            </h6>
                            <small class="text-muted" style="font-size: 11px;">Developer Information & Update Manager</small>
                        </div>
                    </div>
                    <span class="status-pill pill-pulse ms-auto me-2">
                        <span class="heart-pulse-wave-box">
                            <span class="heart-pulse-wave-ring"></span>
                            <i class="bx bxs-heart text-danger heart-beat-anim" style="font-size: 10px;"></i>
                        </span>
                        <small id="aboutModalVersionPill">v{{ \NextSyntaxTech\DevZone\Services\DevZoneService::version() }}</small>
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Brand & Developer Header -->
                    <div class="p-3 rounded-3 mb-3 border" style="background: var(--bg-card-sub);">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="brand-next-syntax-tech fs-5">
                                <span class="b-next">next</span><span class="b-syntax">Syntax</span><span class="b-tech">Tech</span> <span class="badge bg-primary text-white fs-6 ms-1">DevZone</span>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" id="aboutModalInstalledBadge">
                                Installed: v{{ \NextSyntaxTech\DevZone\Services\DevZoneService::version() }} ({{ \NextSyntaxTech\DevZone\Services\DevZoneService::build() }})
                            </span>
                        </div>
                        <p class="small text-muted mb-3">Enterprise Developer Control & Maintenance Panel for Laravel applications.</p>
                        
                        <div class="row g-2 pt-2 border-top small">
                            <div class="col-sm-6">
                                <span class="text-muted d-block" style="font-size: 11px;">Developer:</span>
                                <span class="fw-bold text-main d-flex align-items-center gap-1">
                                    <i class="bx bx-user-pin text-primary"></i> Amit Saha
                                </span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block" style="font-size: 11px;">Support / Email:</span>
                                <a href="mailto:nextsyntaxtech@gmail.com" class="text-decoration-none fw-semibold text-primary d-flex align-items-center gap-1">
                                    <i class="bx bx-envelope text-info"></i> nextsyntaxtech@gmail.com
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Version & Release Update Checker Center -->
                    <div class="p-3 rounded-3 border" style="background: var(--bg-card-sub);">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="fw-bold small text-main d-flex align-items-center gap-1">
                                <i class="bx bx-git-repo-forked text-primary"></i> Release & Update Status
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" id="btnCheckForUpdates" onclick="checkForPackageUpdates()">
                                <i class="bx bx-refresh" id="checkUpdateIcon"></i> <span id="checkUpdateText">Check for Updates</span>
                            </button>
                        </div>

                        <!-- Update Status Card / Output -->
                        <div id="updateStatusContainer" class="p-3 rounded-2 border bg-card small mt-2">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted">Current Version:</span>
                                <span class="font-monospace fw-bold text-main" id="currentInstalledVersion">v{{ \NextSyntaxTech\DevZone\Services\DevZoneService::version() }} (Build: {{ \NextSyntaxTech\DevZone\Services\DevZoneService::build() }})</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted">Latest Release:</span>
                                <span class="font-monospace fw-bold text-info" id="latestReleaseVersion">v{{ \NextSyntaxTech\DevZone\Services\DevZoneService::version() }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted">Release Date:</span>
                                <span class="text-muted" id="latestReleaseDate">{{ date('M d, Y') }}</span>
                            </div>

                            <div id="updateBadgeContainer" class="mt-2 text-center p-2 rounded-2" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25);">
                                <span class="text-success fw-bold d-flex align-items-center justify-content-center gap-1" id="updateBadgeText">
                                    <i class="bx bx-check-circle"></i> You are running the latest version! (v{{ \NextSyntaxTech\DevZone\Services\DevZoneService::version() }} &bull; Build: {{ \NextSyntaxTech\DevZone\Services\DevZoneService::build() }})
                                </span>
                            </div>

                            <div id="updateActionArea" class="d-none mt-3 text-center">
                                <button type="button" class="btn btn-success btn-sm w-100 py-2 d-flex align-items-center justify-content-center gap-2 fw-bold" onclick="executePackageUpdateAction()">
                                    <i class="bx bx-download fs-5"></i> <span id="updateButtonLabel">Install Release Update</span>
                                </button>
                                <div class="mt-2 small text-muted font-monospace" style="font-size: 11px;">
                                    Runs: <code>composer update nextsyntaxtech/devzone</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-4 border-top bg-card-sub d-flex justify-content-between">
                    <a href="https://github.com/bsamit/nextsyntaxtech-devzone" target="_blank" class="btn btn-sm btn-action-secondary d-flex align-items-center gap-1">
                        <i class="bx bxl-github"></i> GitHub Repo
                    </a>
                    <button type="button" class="btn btn-action-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- DevZone Interactive Dialog & Toast Component (Bootstrap 5 Powered, Zero CDN) -->
    <x-devzone::dialog />

    <!-- Local JS Dependencies (Zero CDN, 100% Offline) -->
    <script src="{{ \NextSyntaxTech\DevZone\Services\DevZoneService::asset('js/bootstrap.bundle.min.js') }}"></script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // =============================================================
        // THEME TOGGLE & PERSISTENCE (Default: Light)
        // =============================================================
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            const icon = document.getElementById('themeIcon');
            const btn = document.getElementById('themeToggleBtn');
            const label = document.getElementById('themeLabel');
            if (theme === 'dark') {
                if (icon) icon.className = 'bx bx-sun text-warning';
                if (btn) btn.title = 'Switch to Light Mode';
                if (label) label.textContent = 'Light Mode';
            } else {
                if (icon) icon.className = 'bx bx-moon text-primary';
                if (btn) btn.title = 'Switch to Dark Mode';
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
            // Handle docs/swagger alias
            if (tabId === 'swagger' && !document.getElementById('tab-swagger') && document.getElementById('tab-docs')) {
                tabId = 'docs';
            } else if (tabId === 'docs' && !document.getElementById('tab-docs') && document.getElementById('tab-swagger')) {
                tabId = 'swagger';
            }

            let targetPane = document.getElementById('tab-' + tabId);
            if (!targetPane) {
                tabId = 'dashboard';
                targetPane = document.getElementById('tab-dashboard');
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

        // Show Console Output via Unified DevZone Dialog System
        function showTerminalModal(title, output, meta = '', onOk = null) {
            DevZoneAlert.terminal({
                title: title,
                output: output,
                meta: meta,
                onOk: onOk
            });
        }

        function copyConsoleOutput() {
            const text = document.getElementById('modalOutputContent').textContent;
            navigator.clipboard.writeText(text).then(() => {
                DevZoneAlert.toast({ icon: 'success', title: 'Copied to clipboard', timer: 1500 });
            });
        }

        // =============================================================
        // MASTER CREDENTIALS / PASSKEY CHANGE SYSTEM
        // =============================================================
        function togglePassVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = isPassword ? 'bx bx-hide' : 'bx bx-show';
            }
        }

        function showCredModalAlert(msg, type = 'danger') {
            const alertBox = document.getElementById('credModalAlert');
            if (!alertBox) return;
            alertBox.className = `alert alert-${type} py-2 px-3 small d-flex align-items-center gap-2 mb-3`;
            alertBox.innerHTML = `<i class="bx ${type === 'danger' ? 'bx-error-circle' : 'bx-check-circle'} fs-5"></i> <div>${msg}</div>`;
        }

        async function submitChangeCredentials(e) {
            e.preventDefault();
            const form = document.getElementById('changeCredentialsForm');
            const alertBox = document.getElementById('credModalAlert');
            const submitBtn = document.getElementById('credSubmitBtn');
            const submitText = document.getElementById('credSubmitBtnText');

            const currentKey = document.getElementById('credCurrentPasskey').value.trim();
            const newKey = document.getElementById('credNewPasskey').value.trim();
            const confirmKey = document.getElementById('credNewPasskeyConfirm').value.trim();
            const adminEmail = document.getElementById('credAdminEmail').value.trim();

            if (!currentKey) {
                showCredModalAlert('Please enter your current passkey for verification.', 'danger');
                return;
            }

            if (newKey.length < 6) {
                showCredModalAlert('New passkey must be at least 6 characters long.', 'danger');
                return;
            }

            if (newKey !== confirmKey) {
                showCredModalAlert('New passkey confirmation does not match.', 'danger');
                return;
            }

            if (currentKey === newKey) {
                showCredModalAlert('New passkey cannot be the same as your current passkey.', 'danger');
                return;
            }

            // Set loading state
            if (alertBox) alertBox.className = 'd-none';
            submitBtn.disabled = true;
            submitText.textContent = 'Verifying & Saving...';

            try {
                const response = await fetch("{{ route('devzone.credentials.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        current_passkey: currentKey,
                        new_passkey: newKey,
                        new_passkey_confirmation: confirmKey,
                        admin_email: adminEmail
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    showCredModalAlert(data.message || 'Verification failed. Please check your credentials.', 'danger');
                    submitBtn.disabled = false;
                    submitText.textContent = 'Update Credentials';
                    return;
                }

                // Success!
                submitBtn.disabled = false;
                submitText.textContent = 'Update Credentials';
                form.reset();

                // Close bootstrap modal
                const modalEl = document.getElementById('changeCredentialsModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modalInstance.hide();

                // Show success notification via DevZoneAlert
                DevZoneAlert.toast({
                    icon: 'success',
                    title: data.message || 'Credentials updated successfully!',
                    timer: 4000
                });

            } catch (err) {
                showCredModalAlert('Error: ' + err.message, 'danger');
                submitBtn.disabled = false;
                submitText.textContent = 'Update Credentials';
            }
        }

        // =============================================================
        // ABOUT & AUTO-UPDATE ENGINE
        // =============================================================
        let availableNewVersion = null;

        async function checkForPackageUpdates() {
            const btn = document.getElementById('btnCheckForUpdates');
            const icon = document.getElementById('checkUpdateIcon');
            const text = document.getElementById('checkUpdateText');
            const latestVerEl = document.getElementById('latestReleaseVersion');
            const latestDateEl = document.getElementById('latestReleaseDate');
            const badgeContainer = document.getElementById('updateBadgeContainer');
            const badgeText = document.getElementById('updateBadgeText');
            const actionArea = document.getElementById('updateActionArea');
            const updateBtnLabel = document.getElementById('updateButtonLabel');

            if (btn) btn.disabled = true;
            if (icon) icon.className = 'bx bx-loader-alt bx-spin';
            if (text) text.textContent = 'Checking...';

            try {
                const response = await fetch("{{ route('devzone.version.check') }}", {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                const currentInstalledEl = document.getElementById('currentInstalledVersion');
                if (currentInstalledEl && data.current_version) {
                    currentInstalledEl.textContent = `${data.current_version} (Build: ${data.current_build || 'latest'})`;
                }

                if (latestVerEl) {
                    latestVerEl.textContent = data.latest_version ? `${data.latest_version} (${data.latest_build || 'latest'})` : 'v1.0.2';
                }
                if (latestDateEl) latestDateEl.textContent = data.release_date || 'Recent';

                if (data.has_update) {
                    availableNewVersion = data.latest_version;
                    if (badgeContainer) {
                        badgeContainer.style.background = 'rgba(245, 158, 11, 0.12)';
                        badgeContainer.style.borderColor = 'rgba(245, 158, 11, 0.4)';
                    }
                    if (badgeText) {
                        badgeText.className = 'text-warning fw-bold d-flex align-items-center justify-content-center gap-1';
                        badgeText.innerHTML = `<i class="bx bx-bell"></i> ${data.update_message || ('New Update Available: ' + data.latest_version)}`;
                    }
                    if (actionArea) actionArea.classList.remove('d-none');
                    if (updateBtnLabel) {
                        updateBtnLabel.textContent = (data.update_type === 'build')
                            ? `Install Build Update (${data.latest_build})`
                            : `Update to ${data.latest_version}`;
                    }

                    DevZoneAlert.toast({
                        icon: 'info',
                        title: data.update_message || `New update ${data.latest_version} is available!`,
                        timer: 3500
                    });
                } else {
                    availableNewVersion = null;
                    if (badgeContainer) {
                        badgeContainer.style.background = 'rgba(16, 185, 129, 0.08)';
                        badgeContainer.style.borderColor = 'rgba(16, 185, 129, 0.25)';
                    }
                    if (badgeText) {
                        badgeText.className = 'text-success fw-bold d-flex align-items-center justify-content-center gap-1';
                        badgeText.innerHTML = `<i class="bx bx-check-circle"></i> You are running the latest version! (${data.current_version} &bull; Build: ${data.current_build})`;
                    }
                    if (actionArea) actionArea.classList.add('d-none');

                    DevZoneAlert.toast({
                        icon: 'success',
                        title: 'You are running the latest version!',
                        timer: 2500
                    });
                }
            } catch (err) {
                if (badgeText) {
                    badgeText.className = 'text-danger small';
                    badgeText.textContent = 'Unable to fetch releases: ' + err.message;
                }
            } finally {
                if (btn) btn.disabled = false;
                if (icon) icon.className = 'bx bx-refresh';
                if (text) text.textContent = 'Check for Updates';
            }
        }

        function executePackageUpdateAction() {
            const aboutModalEl = document.getElementById('aboutDevZoneModal');
            if (aboutModalEl) {
                const modalInst = bootstrap.Modal.getInstance(aboutModalEl);
                if (modalInst) modalInst.hide();
            }

            DevZoneAlert.confirm({
                title: 'Update nextSyntaxTech DevZone?',
                text: 'This will execute composer update, re-compile framework caches, and publish the latest assets.',
                icon: 'question',
                confirmButtonText: 'Yes, Run Update Now',
                confirmButtonColor: '#059669',
            }).then(result => {
                if (result.isConfirmed) {
                    DevZoneAlert.loading(
                        'Updating nextSyntaxTech DevZone...',
                        'Executing package update routine and publishing latest assets... Please wait...'
                    );

                    fetch("{{ route('devzone.version.update') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            DevZoneAlert.terminal({
                                title: 'Update Routine Completed Successfully!',
                                output: data.output || 'Package updated and assets refreshed.',
                                meta: 'Execution Duration: ' + (data.duration || 0) + 'ms',
                                onOk: () => window.location.reload()
                            });
                        } else {
                            DevZoneAlert.terminal({
                                title: 'Update Execution Notice',
                                output: data.output || data.message || 'Execution failed.',
                                meta: 'Status: Notice',
                                onOk: () => {}
                            });
                        }
                    })
                    .catch(err => {
                        DevZoneAlert.error('Update Failed', 'An error occurred during update: ' + err.message);
                    });
                }
            });
        }
    </script>

    @stack('scripts')

</body>
</html>
