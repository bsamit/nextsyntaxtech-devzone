<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NextSyntaxTech DevZone - Developer Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: #111827;
            --border-color: #1f2937;
            --accent-color: #3b82f6;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
        }
        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .login-header {
            padding: 32px 32px 20px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.08) 0%, transparent 100%);
        }
        .dev-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        .login-body {
            padding: 28px 32px 32px;
        }
        .form-control {
            background: #1f2937;
            border: 1px solid #374151;
            color: #f9fafb;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            background: #111827;
            border-color: #3b82f6;
            color: #f9fafb;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .btn-submit {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
        }
        .input-group-text {
            background: #1f2937;
            border: 1px solid #374151;
            color: #9ca3af;
            cursor: pointer;
        }
        .security-notice {
            background: rgba(31, 41, 55, 0.6);
            border: 1px solid #374151;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="dev-badge">
            <i class="bx bx-shield-quarter"></i> NextSyntaxTech DevZone
        </div>
        <h4 class="fw-bold text-white mb-1">Developer Utility Panel</h4>
        <p class="text-secondary small mb-0">{{ config('app.name', 'Laravel') }} Project Management & Maintenance</p>
    </div>

    <div class="login-body">
        @if(session('success'))
            <div class="alert alert-success py-2 px-3 small border-0 mb-3" style="background: rgba(16, 185, 129, 0.15); color: #6ee7b7;">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger py-2 px-3 small border-0 mb-3" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5;">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning py-2 px-3 small border-0 mb-3" style="background: rgba(245, 158, 11, 0.15); color: #fcd34d;">
                <i class="bx bx-time me-1"></i> {{ session('warning') }}
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info py-2 px-3 small border-0 mb-3" style="background: rgba(59, 130, 246, 0.15); color: #93c5fd;">
                <i class="bx bx-info-circle me-1"></i> {{ session('info') }}
            </div>
        @endif

        <form action="{{ route('devzone.login.submit') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="passkeyInput" class="form-label small fw-semibold text-secondary">Authorized Access Key</label>
                <div class="input-group">
                    <input 
                        type="password" 
                        class="form-control" 
                        id="passkeyInput" 
                        name="passkey" 
                        placeholder="Enter access key..."
                        required 
                        autocomplete="off"
                        autofocus
                    >
                    <button class="input-group-text" type="button" id="togglePasswordBtn" title="Show/Hide Key">
                        <i class="bx bx-show" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-submit d-flex align-items-center justify-content-center gap-2">
                <i class="bx bx-lock-open-alt fs-5"></i> Authenticate & Enter
            </button>
        </form>

        <div class="security-notice">
            <i class="bx bx-info-circle fs-5 text-primary flex-shrink-0"></i>
            <div>Authentication key required on every session. Rate limited against brute-force attempts.</div>
        </div>
    </div>
</div>

<script>
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passInput = document.getElementById('passkeyInput');
    const toggleIcon = document.getElementById('toggleIcon');

    toggleBtn.addEventListener('click', function() {
        const isPassword = passInput.getAttribute('type') === 'password';
        passInput.setAttribute('type', isPassword ? 'text' : 'password');
        toggleIcon.className = isPassword ? 'bx bx-hide' : 'bx bx-show';
    });
</script>

</body>
</html>
