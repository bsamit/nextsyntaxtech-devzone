<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NextSyntaxTech DevZone - Initial Setup Wizard</title>
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
            padding: 30px 20px;
        }
        .setup-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 520px;
            overflow: hidden;
        }
        .setup-header {
            padding: 32px 32px 20px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.1) 0%, transparent 100%);
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        .setup-body {
            padding: 28px 32px 32px;
        }
        .form-control {
            background: #1f2937;
            border: 1px solid #374151;
            color: #f9fafb;
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            background: #111827;
            border-color: #3b82f6;
            color: #f9fafb;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .form-control::placeholder {
            color: #6b7280;
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
        .notice-box {
            background: rgba(31, 41, 55, 0.6);
            border: 1px solid #374151;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 12px;
            color: #9ca3af;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 22px;
        }
    </style>
</head>
<body>

<div class="setup-card">
    <div class="setup-header">
        <div class="brand-badge">
            <i class="bx bx-cog"></i> NextSyntaxTech DevZone
        </div>
        <h4 class="fw-bold text-white mb-1">First-Time Setup Wizard</h4>
        <p class="text-secondary small mb-0">{{ config('app.name', 'Laravel Application') }} Master Security Configuration</p>
    </div>

    <div class="setup-body">
        <div class="notice-box">
            <i class="bx bx-info-circle fs-5 text-primary flex-shrink-0 mt-1"></i>
            <div>
                Welcome to <strong>DevZone</strong>! This one-time wizard configures your master access passkey and writes credentials safely to your <code>.env</code> file.
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small border-0 mb-3" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5;">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger py-2 px-3 small border-0 mb-3" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5;">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('devzone.setup.submit') }}" method="POST">
            @csrf

            <!-- Admin Email -->
            <div class="mb-3">
                <label for="emailInput" class="form-label small fw-semibold text-secondary">
                    Authorized Administrator Email <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="emailInput" 
                        name="email" 
                        value="{{ old('email', config('devzone.admin_email', 'amitsaha.cse@gmail.com')) }}" 
                        placeholder="amitsaha.cse@gmail.com"
                        required
                    >
                </div>
                <div class="text-secondary small mt-1" style="font-size: 11px;">Identifies the authorized administrator in audit logs.</div>
            </div>

            <!-- Master Passkey / Token -->
            <div class="mb-3">
                <label for="passkeyInput" class="form-label small fw-semibold text-secondary">
                    Master Access Passkey / Token <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-key"></i></span>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="passkeyInput" 
                        name="passkey" 
                        value="{{ old('passkey', 'amitsaha.cse@gmail.com') }}"
                        placeholder="Choose a strong passkey (min 6 characters)"
                        required
                        autocomplete="new-password"
                    >
                    <button class="input-group-text" type="button" id="togglePasskeyBtn" title="Show/Hide">
                        <i class="bx bx-show" id="passkeyIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Passkey -->
            <div class="mb-3">
                <label for="passkeyConfirmInput" class="form-label small fw-semibold text-secondary">
                    Confirm Master Passkey <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-check-shield"></i></span>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="passkeyConfirmInput" 
                        name="passkey_confirmation" 
                        value="{{ old('passkey_confirmation', 'amitsaha.cse@gmail.com') }}"
                        placeholder="Confirm your passkey"
                        required
                        autocomplete="new-password"
                    >
                </div>
            </div>

            <!-- URL Path -->
            <div class="mb-4">
                <label for="pathInput" class="form-label small fw-semibold text-secondary">
                    Dedicated Panel Access URL Prefix
                </label>
                <div class="input-group">
                    <span class="input-group-text text-secondary" style="font-size: 12px;">/</span>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="pathInput" 
                        name="path" 
                        value="{{ old('path', config('devzone.path', 'developer-panel')) }}" 
                        placeholder="developer-panel"
                    >
                </div>
                <div class="text-secondary small mt-1" style="font-size: 11px;">Example: <code>developer-panel</code> will be accessible at <code>{{ url('/') }}/developer-panel</code></div>
            </div>

            <button type="submit" class="btn btn-submit d-flex align-items-center justify-content-center gap-2">
                <i class="bx bx-check-circle fs-5"></i> Save & Continue to Login
            </button>
        </form>
    </div>
</div>

<script>
    const toggleBtn = document.getElementById('togglePasskeyBtn');
    const passInput = document.getElementById('passkeyInput');
    const passIcon = document.getElementById('passkeyIcon');

    toggleBtn.addEventListener('click', function() {
        const isPassword = passInput.getAttribute('type') === 'password';
        passInput.setAttribute('type', isPassword ? 'text' : 'password');
        passIcon.className = isPassword ? 'bx bx-hide' : 'bx bx-show';
    });
</script>

</body>
</html>
