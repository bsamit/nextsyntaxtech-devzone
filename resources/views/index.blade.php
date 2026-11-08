@extends('devzone::layout')

@section('content')

<!-- Quick Stats Row (4 Focused Header KPI Cards) -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
    <!-- Card 1: PHP & Laravel Framework Engine -->
    <div class="col">
        <div class="kpi-card h-100">
            <div class="kpi-title">
                <span>Framework & Runtime</span>
                <i class="bx bxl-php text-primary fs-5"></i>
            </div>
            <div class="kpi-value text-primary">PHP {{ $systemInfo['php_version'] }}</div>
            <div class="kpi-sub d-flex flex-column gap-1">
                <div class="d-flex justify-content-between">
                    <span>Framework:</span>
                    <span class="fw-semibold text-main">Laravel v{{ $systemInfo['laravel_version'] }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Environment:</span>
                    <span class="badge bg-{{ $systemInfo['app_env'] === 'production' ? 'danger' : 'success' }} px-2 py-1">{{ strtoupper($systemInfo['app_env']) }} (Debug: {{ $systemInfo['app_debug'] ? 'ON' : 'OFF' }})</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Database Engine, Version & Size -->
    <div class="col">
        <div class="kpi-card h-100">
            <div class="kpi-title">
                <span>Database Engine & Size</span>
                <i class="bx bx-cylinder text-info fs-5"></i>
            </div>
            <div class="kpi-value text-info">{{ $systemInfo['db_engine'] ?? 'Database' }} <span class="fs-6 fw-normal text-muted">{{ $systemInfo['db_short_version'] ?? '' }}</span></div>
            <div class="kpi-sub d-flex flex-column gap-1">
                <div class="d-flex justify-content-between">
                    <span>Total DB Size:</span>
                    <span class="fw-bold text-main">{{ $systemInfo['db_size_mb'] }} MB</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Version Details:</span>
                    <span class="text-muted text-truncate font-monospace" style="max-width: 140px;" title="{{ $systemInfo['db_version'] ?? 'N/A' }}">{{ $systemInfo['db_version'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Database Info & Connection -->
    <div class="col">
        <div class="kpi-card h-100">
            <div class="kpi-title">
                <span>Database Info</span>
                <i class="bx bx-server text-success fs-5"></i>
            </div>
            <div class="kpi-value text-success text-truncate" title="{{ $systemInfo['db_name'] }}">{{ $systemInfo['db_name'] }}</div>
            <div class="kpi-sub d-flex flex-column gap-1">
                <div class="d-flex justify-content-between">
                    <span>Tables:</span>
                    <span class="fw-semibold text-main">{{ $systemInfo['table_count'] }} Tables</span>
                </div>
                <div class="d-flex justify-content-between">
                    @if($systemInfo['db_driver'] === 'sqlite')
                        <span>Driver / Status:</span>
                        <span class="text-success fw-semibold font-monospace small"><i class="bx bxs-circle me-1" style="font-size: 8px;"></i> SQLite (Active)</span>
                    @else
                        <span>Host / Port:</span>
                        <span class="text-muted font-monospace">{{ $systemInfo['db_host'] }}:{{ $systemInfo['db_port'] ?? 3306 }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Migrations Status -->
    <div class="col">
        <div class="kpi-card h-100">
            <div class="kpi-title">
                <span>Migrations Status</span>
                <i class="bx bx-git-branch text-warning fs-5"></i>
            </div>
            <div class="kpi-value text-warning">{{ $systemInfo['migrations_ran'] }} <span class="fs-6 fw-normal text-muted">/ {{ $systemInfo['migrations_total'] }}</span></div>
            <div class="kpi-sub d-flex flex-column gap-1">
                <div class="d-flex justify-content-between">
                    <span>Pending:</span>
                    <span class="fw-bold {{ $systemInfo['migrations_pending'] > 0 ? 'text-warning' : 'text-success' }}">{{ $systemInfo['migrations_pending'] }}</span>
                </div>
                <div>
                    <span class="badge {{ $systemInfo['migrations_pending'] > 0 ? 'bg-warning text-dark' : 'bg-success text-white' }} px-1.5 py-0.5" style="font-size: 10px;">{{ $systemInfo['migrations_pending'] > 0 ? 'Action Needed' : 'All Up to Date' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Navigation Pills -->
<div class="dev-nav-tabs">
    <div class="dev-nav-tab active" data-tab="dashboard" onclick="switchTab('dashboard')">
        <i class="bx bx-grid-alt"></i> Dashboard
    </div>
    <div class="dev-nav-tab" data-tab="migrations" onclick="switchTab('migrations')">
        <i class="bx bx-layer"></i> Migrations 
        @if($systemInfo['migrations_pending'] > 0)
            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill" style="font-size: 10px;">{{ $systemInfo['migrations_pending'] }}</span>
        @endif
    </div>
    <div class="dev-nav-tab" data-tab="seeders" onclick="switchTab('seeders')">
        <i class="bx bx-spa"></i> Seeders
    </div>
    <div class="dev-nav-tab" data-tab="artisan" onclick="switchTab('artisan')">
        <i class="bx bx-command"></i> Safe Artisan
    </div>
    <div class="dev-nav-tab" data-tab="swagger" onclick="switchTab('swagger')">
        <i class="bx bx-book-open"></i> {{ !empty($apiDocInfo) ? $apiDocInfo['primary_label'] : 'API Docs' }}
    </div>
    <div class="dev-nav-tab" data-tab="debug" onclick="switchTab('debug')">
        <i class="bx bx-bug"></i> Debugging
    </div>
    <div class="dev-nav-tab" data-tab="backup" onclick="switchTab('backup')">
        <i class="bx bx-download"></i> Database Backup
    </div>
    <div class="dev-nav-tab" data-tab="logs" onclick="switchTab('logs')">
        <i class="bx bx-history"></i> Activity Logs
    </div>
</div>

<!-- ============================================================== -->
<!-- TAB 1: DASHBOARD -->
<!-- ============================================================== -->
<div class="tab-content-pane" id="tab-dashboard">
    <div class="row g-4">
        <!-- Quick Action Launcher -->
        <div class="col-lg-8">
            <div class="dev-card">
                <div class="dev-card-header">
                    <div>
                        <h6 class="fw-bold mb-0 text-main d-flex align-items-center gap-2">
                            <i class="bx bx-rocket text-primary"></i> Quick Maintenance Launcher
                        </h6>
                        <small class="text-muted">Instant single-click developer maintenance operations</small>
                    </div>
                </div>
                <div class="dev-card-body">
                    <div class="row g-3">
                        <!-- Quick 1: Optimize Clear -->
                        <div class="col-md-6">
                            <div class="command-subcard p-3 d-flex flex-column justify-content-between h-100">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-trash text-warning fs-4"></i>
                                        <h6 class="fw-bold mb-0 text-main">Full Optimize Clear</h6>
                                    </div>
                                    <div class="command-code-box mb-2">
                                        <span class="command-prefix">$</span>
                                        <code class="command-code-text">php artisan optimize:clear</code>
                                    </div>
                                    <p class="small text-muted mb-3">Clears compiled bootstrap caches, configs, routes, views, and events.</p>
                                </div>
                                <button class="btn btn-action-primary w-100 justify-content-center" onclick="executeArtisanCommand('optimize:clear')">
                                    <i class="bx bx-play"></i> Run optimize:clear
                                </button>
                            </div>
                        </div>

                        <!-- Quick 2: Database Backup -->
                        <div class="col-md-6">
                            <div class="command-subcard p-3 d-flex flex-column justify-content-between h-100">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-download text-success fs-4"></i>
                                        <h6 class="fw-bold mb-0 text-main">Instant Database Backup</h6>
                                    </div>
                                    <div class="command-code-box mb-2">
                                        <span class="command-prefix">$</span>
                                        <code class="command-code-text">mysqldump {{ $systemInfo['db_name'] }} > backup.sql</code>
                                    </div>
                                    <p class="small text-muted mb-3">Generates an instant SQL dump of database <code>{{ $systemInfo['db_name'] }}</code>.</p>
                                </div>
                                <button class="btn btn-action-primary w-100 justify-content-center" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);" onclick="createDatabaseBackupAction()">
                                    <i class="bx bx-save"></i> Generate Backup Now
                                </button>
                            </div>
                        </div>

                        <!-- Quick 3: Migrate Pending -->
                        <div class="col-md-6">
                            <div class="command-subcard p-3 d-flex flex-column justify-content-between h-100">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-layer text-info fs-4"></i>
                                        <h6 class="fw-bold mb-0 text-main">Run Pending Migrations</h6>
                                    </div>
                                    <div class="command-code-box mb-2">
                                        <span class="command-prefix">$</span>
                                        <code class="command-code-text">php artisan migrate --force</code>
                                    </div>
                                    <p class="small text-muted mb-3">Applies pending database schema migrations safely (No destructive wiping).</p>
                                </div>
                                <button class="btn btn-action-primary w-100 justify-content-center" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);" onclick="runAllMigrationsAction()" {{ $systemInfo['migrations_pending'] == 0 ? 'disabled' : '' }}>
                                    <i class="bx bx-check-double"></i> Migrate Pending ({{ $systemInfo['migrations_pending'] }})
                                </button>
                            </div>
                        </div>

                        <!-- Quick 4: Dynamic API Docs Generator (Scribe / Swagger) -->
                        <div class="col-md-6">
                            <div class="command-subcard p-3 d-flex flex-column justify-content-between h-100">
                                <div>
                                    @php
                                        $isScribeActive = (!empty($apiDocInfo) && $apiDocInfo['active_type'] === 'scribe');
                                        $docCmdName = $isScribeActive ? 'scribe:generate' : 'l5-swagger:generate';
                                        $docToolName = $isScribeActive ? 'Scribe' : 'Swagger';
                                    @endphp
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-book-open text-primary fs-4"></i>
                                        <h6 class="fw-bold mb-0 text-main">Generate {{ $docToolName }} Docs</h6>
                                    </div>
                                    <div class="command-code-box mb-2">
                                        <span class="command-prefix">$</span>
                                        <code class="command-code-text">php artisan {{ $docCmdName }}</code>
                                    </div>
                                    <p class="small text-muted mb-3">Re-scans API annotations and compiles fresh interactive API documentation.</p>
                                </div>
                                <button class="btn btn-action-primary w-100 justify-content-center" onclick="generateDocAction('{{ $isScribeActive ? 'scribe' : 'swagger' }}')">
                                    <i class="bx bx-refresh"></i> Generate {{ $docToolName }} Docs
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Details Sidebar (Fully Theme Adaptive for Dark and Light Modes) -->
        <div class="col-lg-4">
            <div class="dev-card">
                <div class="dev-card-header">
                    <h6 class="fw-bold mb-0 text-main d-flex align-items-center gap-2">
                        <i class="bx bx-info-circle text-info"></i> Environment Details
                    </h6>
                </div>
                <div class="p-3">
                    <table class="env-details-table">
                        <tbody>
                            <tr>
                                <td class="env-label">Application:</td>
                                <td class="env-val">{{ $systemInfo['app_name'] }}</td>
                            </tr>
                            <tr>
                                <td class="env-label">Environment:</td>
                                <td class="env-val">
                                    <span class="badge bg-{{ $systemInfo['app_env'] === 'production' ? 'danger' : 'success' }} px-2 py-0.5">{{ strtoupper($systemInfo['app_env']) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="env-label">App Debug:</td>
                                <td class="env-val">
                                    <span class="badge bg-{{ $systemInfo['app_debug'] ? 'warning text-dark' : 'secondary' }} px-2 py-0.5">{{ $systemInfo['app_debug'] ? 'ENABLED' : 'DISABLED' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="env-label">PHP Version:</td>
                                <td class="env-val">{{ $systemInfo['php_version'] }}</td>
                            </tr>
                            <tr>
                                <td class="env-label">Operating System:</td>
                                <td class="env-val text-muted small">{{ $systemInfo['php_os'] }}</td>
                            </tr>
                            <tr>
                                <td class="env-label">Database Engine:</td>
                                <td class="env-val text-primary font-monospace">{{ $systemInfo['db_engine'] ?? 'Database' }}</td>
                            </tr>
                            <tr>
                                <td class="env-label">Database Version:</td>
                                <td class="env-val font-monospace small text-muted">{{ $systemInfo['db_version'] ?? 'N/A' }}</td>
                            </tr>
                            @if($systemInfo['db_driver'] !== 'sqlite')
                                <tr>
                                    <td class="env-label">Database Host:</td>
                                    <td class="env-val font-monospace small">{{ $systemInfo['db_host'] }}:{{ $systemInfo['db_port'] ?? 3306 }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="env-label">Database Name:</td>
                                <td class="env-val text-primary font-monospace">{{ $systemInfo['db_name'] }}</td>
                            </tr>
                            <tr>
                                <td class="env-label">Connection Status:</td>
                                <td class="env-val text-success small"><i class="bx bxs-circle me-1" style="font-size: 8px;"></i> Connected</td>
                            </tr>
                            <tr>
                                <td class="env-label">Database Tables:</td>
                                <td class="env-val">{{ $systemInfo['table_count'] }}</td>
                            </tr>
                            <tr>
                                <td class="env-label">Total DB Size:</td>
                                <td class="env-val">{{ $systemInfo['db_size_mb'] }} MB</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- TAB 2: MIGRATIONS MANAGER -->
<!-- ============================================================== -->
<div class="tab-content-pane" id="tab-migrations" style="display: none;">
    <div class="dev-card">
        <div class="dev-card-header">
            <div>
                <h6 class="fw-bold mb-0 text-main d-flex align-items-center gap-2">
                    <i class="bx bx-layer text-success"></i> Laravel Migration Files Directory
                </h6>
                <small class="text-muted">Core and Module database migrations (Execution status synced with <code>migrations</code> table)</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-action-primary" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);" onclick="runAllMigrationsAction()" {{ $systemInfo['migrations_pending'] == 0 ? 'disabled' : '' }}>
                    <i class="bx bx-check-double"></i> Run All Pending ({{ $systemInfo['migrations_pending'] }})
                </button>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="p-3 border-bottom border-secondary border-opacity-10 d-flex flex-wrap align-items-center justify-content-between gap-3" style="background: var(--bg-card-sub);">
            <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 420px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text dev-input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" class="form-control dev-form-control" id="migrationSearchInput" placeholder="Search migration filename..." onkeyup="filterMigrationsTable()">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-action-secondary active" id="btnFilterAll" onclick="setMigrationFilter('all')">All ({{ count($migrations) }})</button>
                <button class="btn btn-sm btn-action-secondary text-warning" id="btnFilterPending" onclick="setMigrationFilter('pending')">Pending ({{ $systemInfo['migrations_pending'] }})</button>
                <button class="btn btn-sm btn-action-secondary text-success" id="btnFilterRan" onclick="setMigrationFilter('ran')">Executed ({{ $systemInfo['migrations_ran'] }})</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="dev-table" id="migrationsTable">
                <thead>
                    <tr>
                        <th style="width: 140px;">Status</th>
                        <th>Migration File</th>
                        <th style="width: 140px;">Module</th>
                        <th>Relative Path</th>
                        <th style="width: 100px;">Batch</th>
                        <th style="width: 130px;" class="text-end text-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($migrations as $m)
                        <tr class="migration-row" data-status="{{ $m['is_executed'] ? 'ran' : 'pending' }}">
                            <td>
                                @if($m['is_executed'])
                                    <span class="badge-status-ran">
                                        <i class="bx bx-check-circle"></i> Ran
                                    </span>
                                @else
                                    <span class="badge-status-pending">
                                        <i class="bx bx-time-five"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold text-main d-block text-truncate" style="max-width: 380px;" title="{{ $m['filename'] }}">
                                    {{ $m['filename'] }}
                                </span>
                                <small class="text-muted">{{ $m['modified_at'] }} • {{ $m['file_size'] }}</small>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--badge-neutral-bg); color: var(--badge-neutral-text); border: 1px solid var(--border-color);">
                                    {{ $m['module'] }}
                                </span>
                            </td>
                            <td>
                                <code class="small text-muted font-monospace" style="font-size: 11.5px;">{{ $m['relative_path'] }}</code>
                            </td>
                            <td>
                                @if($m['batch'])
                                    <span class="badge" style="background: var(--badge-neutral-bg); color: var(--primary); border: 1px solid var(--border-color);">#{{ $m['batch'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm {{ $m['is_executed'] ? 'btn-action-secondary' : 'btn-action-primary' }} text-nowrap" onclick="runSingleMigrationAction('{{ $m['relative_path'] }}', '{{ $m['filename'] }}')">
                                    <i class="bx bx-play"></i> {{ $m['is_executed'] ? 'Re-run' : 'Migrate' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No migration files found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- TAB 3: SEEDERS MANAGER -->
<!-- ============================================================== -->
<div class="tab-content-pane" id="tab-seeders" style="display: none;">
    <div class="dev-card">
        <div class="dev-card-header">
            <div>
                <h6 class="fw-bold mb-0 text-main d-flex align-items-center gap-2">
                    <i class="bx bx-spa text-success"></i> Laravel Database Seeders
                </h6>
                <small class="text-muted">Execute individual seeders safely on demand</small>
            </div>
            <div class="input-group input-group-sm" style="max-width: 320px;">
                <span class="input-group-text dev-input-group-text"><i class="bx bx-search"></i></span>
                <input type="text" class="form-control dev-form-control" id="seederSearchInput" placeholder="Filter seeders..." onkeyup="filterSeedersTable()">
            </div>
        </div>

        <div class="table-responsive">
            <table class="dev-table" id="seedersTable">
                <thead>
                    <tr>
                        <th>Seeder Class Name</th>
                        <th style="width: 140px;">Module / Scope</th>
                        <th>Relative Path</th>
                        <th style="width: 120px;">Size</th>
                        <th style="width: 150px; min-width: 150px;" class="text-end text-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($seeders as $s)
                        <tr class="seeder-row">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bx bx-code-curly text-primary fs-5"></i>
                                    <div>
                                        <span class="fw-bold text-main font-monospace">{{ $s['class_name'] }}</span>
                                        <div class="text-muted small" style="font-size: 11px;">Updated: {{ $s['modified_at'] ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--badge-neutral-bg); color: var(--badge-neutral-text); border: 1px solid var(--border-color);">
                                    {{ $s['module'] }}
                                </span>
                            </td>
                            <td>
                                <code class="small text-muted font-monospace" style="font-size: 11.5px;">{{ $s['relative_path'] }}</code>
                            </td>
                            <td class="text-muted small">{{ $s['file_size'] }}</td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-action-primary text-nowrap" onclick="runSeederAction('{{ $s['class_name'] }}')">
                                    <i class="bx bx-play"></i> Run Seeder
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No seeder files found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- TAB 4: SAFE ARTISAN COMMANDS -->
<!-- ============================================================== -->
<div class="tab-content-pane" id="tab-artisan" style="display: none;">
    <div class="dev-card">
        <div class="dev-card-header">
            <div>
                <h6 class="fw-bold mb-0 text-main d-flex align-items-center gap-2">
                    <i class="bx bx-command text-primary"></i> Safe Artisan Execution Center
                </h6>
                <small class="text-muted">Strict whitelist of safe application optimization, caching, and maintenance operations</small>
            </div>
        </div>
        <div class="dev-card-body">
            <div class="row g-3">
                @foreach($artisanCommands as $cmdKey => $cmd)
                    @php
                        $catSlug = strtolower(preg_replace('/[^a-z0-9]/', '', $cmd['category']));
                    @endphp
                    <div class="col-xl-4 col-md-6">
                        <div class="command-subcard p-3 d-flex flex-column justify-content-between h-100">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bx {{ $cmd['icon'] }} text-primary fs-5"></i>
                                        <span class="fw-bold text-main">{{ $cmd['badge'] }}</span>
                                    </div>
                                    <!-- High-Contrast Category Badge with vibrant background & pure white text -->
                                    <span class="artisan-cat-badge cat-{{ $catSlug }}">{{ $cmd['category'] }}</span>
                                </div>

                                <!-- Exact Command Code Preview Box -->
                                <div class="command-code-box mb-2">
                                    <span class="command-prefix">$</span>
                                    <code class="command-code-text">php artisan {{ $cmdKey }}</code>
                                </div>

                                <p class="text-muted small mb-3">{{ $cmd['description'] }}</p>
                            </div>
                            <button class="btn btn-action-primary w-100 justify-content-center text-nowrap" onclick="executeArtisanCommand('{{ $cmdKey }}')">
                                <i class="bx bx-play"></i> Run php artisan {{ $cmdKey }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- TAB 5: DYNAMIC API DOCUMENTATION (SWAGGER / SCRIBE) -->
<!-- ============================================================== -->
<div class="tab-content-pane" id="tab-swagger" style="display: none;">
    <div class="dev-card">
        <div class="dev-card-header">
            <div>
                <h6 class="fw-bold mb-0 text-main d-flex align-items-center gap-2">
                    <i class="bx bx-book-open text-primary"></i> {{ $apiDocInfo['title'] ?? 'API Documentation Manager' }}
                </h6>
                <small class="text-muted">Generate, compile, and preview interactive REST API documentation</small>
            </div>
            @if(!empty($apiDocInfo) && $apiDocInfo['primary_url'] !== '#')
                <a href="{{ $apiDocInfo['primary_url'] }}" target="_blank" class="btn btn-action-primary">
                    <i class="bx bx-link-external"></i> View Documentation Portal
                </a>
            @endif
        </div>

        <div class="dev-card-body">
            @php
                $hasSwagger = $apiDocInfo['has_swagger'] ?? true;
                $hasScribe = $apiDocInfo['has_scribe'] ?? false;
            @endphp

            @if($hasSwagger && $hasScribe)
                <!-- Both Swagger and Scribe Are Installed -->
                <div class="row g-4">
                    <!-- Scribe Column -->
                    <div class="col-lg-6">
                        <div class="command-subcard p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="fw-bold text-main mb-0 d-flex align-items-center gap-2">
                                        <i class="bx bx-file-find text-primary fs-4"></i> Knuckles Scribe
                                    </h5>
                                    <span class="badge bg-primary text-white">Active</span>
                                </div>
                                <div class="command-code-box mb-3">
                                    <span class="command-prefix">$</span>
                                    <code class="command-code-text">php artisan scribe:generate</code>
                                </div>
                                <p class="text-muted small mb-3">
                                    Generates responsive HTML/Markdown documentation and Postman collections from route annotations.
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-action-primary flex-grow-1" onclick="generateDocAction('scribe')">
                                    <i class="bx bx-refresh"></i> Run scribe:generate
                                </button>
                                <a href="{{ url('docs') }}" target="_blank" class="btn btn-action-secondary">
                                    <i class="bx bx-link-external"></i> Open /docs
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Swagger Column -->
                    <div class="col-lg-6">
                        <div class="command-subcard p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="fw-bold text-main mb-0 d-flex align-items-center gap-2">
                                        <i class="bx bx-code-block text-success fs-4"></i> L5-Swagger (OpenAPI 3.0)
                                    </h5>
                                    <span class="badge bg-success text-white">Active</span>
                                </div>
                                <div class="command-code-box mb-3">
                                    <span class="command-prefix">$</span>
                                    <code class="command-code-text">php artisan l5-swagger:generate</code>
                                </div>
                                <p class="text-muted small mb-3">
                                    Compiles Swagger/OpenAPI annotations into <code>storage/api-docs/api-docs.json</code>.
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-action-primary flex-grow-1" onclick="generateDocAction('swagger')">
                                    <i class="bx bx-refresh"></i> Run l5-swagger:generate
                                </button>
                                <a href="{{ url('api/documentation') }}" target="_blank" class="btn btn-action-secondary">
                                    <i class="bx bx-link-external"></i> Open /api/documentation
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($hasScribe)
                <!-- Only Scribe Installed -->
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <h5 class="fw-bold text-main mb-2">Knuckles Scribe API Generator</h5>
                        <div class="command-code-box mb-3" style="max-width: 380px;">
                            <span class="command-prefix">$</span>
                            <code class="command-code-text">php artisan scribe:generate</code>
                        </div>
                        <p class="text-muted small mb-4">
                            Scribe automatically extracts route docblocks, request validation rules, and response shapes into interactive HTML documentation and a Postman collection.
                        </p>
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-action-primary" onclick="generateDocAction('scribe')">
                                <i class="bx bx-refresh"></i> Run scribe:generate
                            </button>
                            <a href="{{ url('docs') }}" target="_blank" class="btn btn-action-secondary">
                                <i class="bx bx-window-open"></i> Open /docs
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="command-subcard p-3 text-sm">
                            <div class="text-muted small mb-1">Generated Portal:</div>
                            <div class="text-main fw-bold mb-3 font-monospace small">public/docs/index.html</div>
                            <div class="text-muted small mb-1">Interactive URL:</div>
                            <code class="text-info small d-block font-monospace">/docs</code>
                        </div>
                    </div>
                </div>

            @elseif($hasSwagger)
                <!-- Only Swagger Installed -->
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <h5 class="fw-bold text-main mb-2">L5-Swagger Generator (OpenAPI 3.0)</h5>
                        <div class="command-code-box mb-3" style="max-width: 380px;">
                            <span class="command-prefix">$</span>
                            <code class="command-code-text">php artisan l5-swagger:generate</code>
                        </div>
                        <p class="text-muted small mb-4">
                            Scans API controllers under <code>app/Http/Controllers/Api</code>, parses OpenAPI docstrings, and compiles specifications to <code>storage/api-docs/api-docs.json</code>.
                        </p>
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-action-primary" onclick="generateDocAction('swagger')">
                                <i class="bx bx-refresh"></i> Run l5-swagger:generate
                            </button>
                            <a href="{{ url('api/documentation') }}" target="_blank" class="btn btn-action-secondary">
                                <i class="bx bx-window-open"></i> Open /api/documentation
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="command-subcard p-3 text-sm">
                            <div class="text-muted small mb-1">Documentation File:</div>
                            <div class="text-main fw-bold mb-3 font-monospace small">storage/api-docs/api-docs.json</div>
                            <div class="text-muted small mb-1">Active Spec URL:</div>
                            <code class="text-info small mb-3 d-block font-monospace">/docs/api-docs.json</code>
                            <div class="text-muted small mb-1">Interactive UI URL:</div>
                            <code class="text-info small d-block font-monospace">/api/documentation</code>
                        </div>
                    </div>
                </div>

            @else
                <!-- Neither Installed -->
                <div class="p-4 text-center">
                    <i class="bx bx-info-circle text-warning fs-1 mb-2"></i>
                    <h5 class="fw-bold text-main mb-2">No API Documentation Package Detected</h5>
                    <p class="text-muted small mb-3">Install either Knuckles Scribe or L5-Swagger to generate automated API documentation.</p>
                    <div class="d-inline-flex gap-3">
                        <code class="p-2 border rounded">composer require knuckleswtf/scribe</code>
                        <code class="p-2 border rounded">composer require darkaonline/l5-swagger</code>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- TAB 6: DEBUGGING SETTINGS -->
<!-- ============================================================== -->
<div class="tab-content-pane" id="tab-debug" style="display: none;">
    <div class="dev-card">
        <div class="dev-card-header">
            <div>
                <h6 class="fw-bold mb-0 text-main d-flex align-items-center gap-2">
                    <i class="bx bx-bug text-warning"></i> Application Debugging Configuration
                </h6>
                <small class="text-muted">Safely toggle Laravel <code>APP_DEBUG</code> configuration in <code>.env</code></small>
            </div>
        </div>
        <div class="dev-card-body">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <h5 class="fw-bold text-main mb-2">Laravel Debugging Mode</h5>
                    <p class="text-muted small mb-4">
                        Enabling <code>APP_DEBUG</code> outputs verbose error details and stack traces to help developers trace bugs. 
                        In production environments, debugging should remain <strong>DISABLED</strong> to prevent leaking sensitive credentials.
                    </p>

                    <div class="d-flex align-items-center gap-3">
                        @if($systemInfo['app_debug'])
                            <button class="btn btn-danger d-inline-flex align-items-center gap-2 fw-bold px-3 py-2" onclick="toggleDebugAction(false)">
                                <i class="bx bx-power-off"></i> Disable APP_DEBUG (Set to false)
                            </button>
                        @else
                            <button class="btn btn-warning text-dark d-inline-flex align-items-center gap-2 fw-bold px-3 py-2" onclick="toggleDebugAction(true)">
                                <i class="bx bx-bug"></i> Enable APP_DEBUG (Set to true)
                            </button>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="command-subcard p-4 text-center">
                        <div class="fs-1 text-{{ $systemInfo['app_debug'] ? 'warning' : 'secondary' }} mb-2">
                            <i class="bx {{ $systemInfo['app_debug'] ? 'bx-bug' : 'bx-shield-quarter' }}"></i>
                        </div>
                        <h6 class="fw-bold text-main mb-2">Current Status</h6>
                        <span class="badge bg-{{ $systemInfo['app_debug'] ? 'warning text-dark' : 'secondary' }} fs-6 px-3 py-1.5">
                            APP_DEBUG = {{ $systemInfo['app_debug'] ? 'true' : 'false' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- TAB 7: DATABASE BACKUP & DOWNLOAD -->
<!-- ============================================================== -->
<div class="tab-content-pane" id="tab-backup" style="display: none;">
    <div class="dev-card">
        <div class="dev-card-header">
            <div>
                <h6 class="fw-bold mb-0 text-main d-flex align-items-center gap-2">
                    <i class="bx bx-download text-success"></i> Database Backups & SQL Export
                </h6>
                <small class="text-muted">Generate complete SQL dumps and download securely (Stored in <code>storage/app/backup</code>)</small>
            </div>
            <button class="btn btn-action-primary" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);" onclick="createDatabaseBackupAction()">
                <i class="bx bx-save"></i> Create Database Backup Now
            </button>
        </div>

        <div class="table-responsive">
            <table class="dev-table" id="backupsTable">
                <thead>
                    <tr>
                        <th>Backup Filename</th>
                        <th>File Size</th>
                        <th>Created At</th>
                        <th>Age</th>
                        <th class="text-end text-nowrap" style="width: 170px; min-width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $b)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bx bxs-file-archive text-warning fs-5"></i>
                                    <span class="fw-bold text-main font-monospace">{{ $b['filename'] }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--badge-neutral-bg); color: var(--badge-neutral-text); border: 1px solid var(--border-color);">
                                    {{ $b['formatted_size'] }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $b['created_at'] }}</td>
                            <td class="text-muted small">{{ $b['age'] }}</td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex align-items-center justify-content-end gap-1 flex-nowrap">
                                    <a href="{{ route('devzone.backup.download', ['filename' => $b['filename']]) }}" class="btn btn-sm btn-action-primary text-nowrap px-2.5 py-1" title="Download SQL Dump">
                                        <i class="bx bx-download"></i> Download
                                    </a>
                                    <button class="btn btn-sm btn-action-secondary text-danger border-danger-subtle px-2 py-1" onclick="deleteBackupAction('{{ $b['filename'] }}')" title="Delete File">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No database backup files found. Click "Create Database Backup Now" above to generate a dump.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- TAB 8: ACTIVITY LOGS -->
<!-- ============================================================== -->
<div class="tab-content-pane" id="tab-logs" style="display: none;">
    <div class="dev-card">
        <div class="dev-card-header">
            <div>
                <h6 class="fw-bold mb-0 text-main d-flex align-items-center gap-2">
                    <i class="bx bx-history text-info"></i> Developer Utility Activity Audit Trail
                </h6>
                <small class="text-muted">Security logging of all administrative actions executed through this panel</small>
            </div>
            <button class="btn btn-sm btn-action-secondary text-danger border-danger-subtle" onclick="clearLogsAction()">
                <i class="bx bx-trash"></i> Clear Logs
            </button>
        </div>

        <div class="table-responsive">
            <table class="dev-table">
                <thead>
                    <tr>
                        <th style="width: 180px; min-width: 170px;">Timestamp</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 220px;">Action</th>
                        <th>Details</th>
                        <th style="width: 140px;">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activityLogs as $log)
                        <tr>
                            <td class="text-nowrap">
                                <span class="activity-time">
                                    {{ !empty($log['timestamp']) ? date('d-M-Y h:i A', strtotime($log['timestamp'])) : 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @if($log['status'] === 'success')
                                    <span class="badge-log-success"><i class="bx bx-check-circle"></i> Success</span>
                                @elseif($log['status'] === 'error')
                                    <span class="badge-log-error"><i class="bx bx-x-circle"></i> Error</span>
                                @else
                                    <span class="badge-log-notice"><i class="bx bx-info-circle"></i> Notice</span>
                                @endif
                            </td>
                            <td>
                                <span class="activity-action">{{ $log['action'] }}</span>
                            </td>
                            <td>
                                <div class="activity-details">{{ $log['details'] }}</div>
                            </td>
                            <td>
                                <code class="activity-ip">{{ $log['ip'] }}</code>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No activity logs recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // -------------------------------------------------------------
    // Migration Table Filters
    // -------------------------------------------------------------
    let currentMigrationFilter = 'all';

    function setMigrationFilter(filter) {
        currentMigrationFilter = filter;
        document.querySelectorAll('#btnFilterAll, #btnFilterPending, #btnFilterRan').forEach(b => b.classList.remove('active'));
        if (filter === 'all') document.getElementById('btnFilterAll').classList.add('active');
        if (filter === 'pending') document.getElementById('btnFilterPending').classList.add('active');
        if (filter === 'ran') document.getElementById('btnFilterRan').classList.add('active');
        filterMigrationsTable();
    }

    function filterMigrationsTable() {
        const search = document.getElementById('migrationSearchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#migrationsTable .migration-row');

        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            const text = row.textContent.toLowerCase();
            const matchesSearch = text.includes(search);
            const matchesFilter = (currentMigrationFilter === 'all') || (status === currentMigrationFilter);

            row.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
        });
    }

    function filterSeedersTable() {
        const search = document.getElementById('seederSearchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#seedersTable .seeder-row');
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(search) ? '' : 'none';
        });
    }

    // -------------------------------------------------------------
    // AJAX Action Helpers
    // -------------------------------------------------------------
    function executeArtisanCommand(command) {
        Swal.fire({
            title: 'Execute Command?',
            html: `Run <code>php artisan ${command}</code>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Run Command',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Executing...', text: `Running php artisan ${command}`, allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch('{{ route("devzone.artisan.run") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ command: command })
                })
                .then(r => r.json())
                .then(res => {
                    Swal.close();
                    showTerminalModal(`php artisan ${command}`, res.output, res.message);
                })
                .catch(err => {
                    Swal.fire('Error', err.message || 'Execution failed', 'error');
                });
            }
        });
    }

    function runSingleMigrationAction(path, filename) {
        Swal.fire({
            title: 'Run Migration?',
            html: `Execute migration:<br><code>${filename}</code>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Execute Migration',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Migrating...', text: `Applying ${filename}`, allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch('{{ route("devzone.migration.run") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ mode: 'single', path: path })
                })
                .then(r => r.json())
                .then(res => {
                    Swal.close();
                    showTerminalModal(`Migrate: ${filename}`, res.output, res.message);
                    if (res.success) {
                        setTimeout(() => window.location.reload(), 2500);
                    }
                })
                .catch(err => Swal.fire('Error', err.message, 'error'));
            }
        });
    }

    function runAllMigrationsAction() {
        Swal.fire({
            title: 'Run All Pending Migrations?',
            text: 'This will execute all outstanding database schema migrations safely.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Run All Pending',
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Migrating...', text: 'Executing pending migrations...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch('{{ route("devzone.migration.run") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ mode: 'all' })
                })
                .then(r => r.json())
                .then(res => {
                    Swal.close();
                    showTerminalModal('All Pending Migrations', res.output, res.message);
                    if (res.success) {
                        setTimeout(() => window.location.reload(), 2500);
                    }
                })
                .catch(err => Swal.fire('Error', err.message, 'error'));
            }
        });
    }

    function runSeederAction(seederClass) {
        Swal.fire({
            title: 'Execute Seeder?',
            html: `Run seeder class <code>${seederClass}</code>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Execute Seeder',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Seeding...', text: `Executing ${seederClass}`, allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch('{{ route("devzone.seeder.run") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ class: seederClass })
                })
                .then(r => r.json())
                .then(res => {
                    Swal.close();
                    showTerminalModal(`Seeder: ${seederClass}`, res.output, res.message);
                })
                .catch(err => Swal.fire('Error', err.message, 'error'));
            }
        });
    }

    function generateDocAction(driver = 'auto') {
        const docLabel = (driver === 'scribe') ? 'Scribe' : 'Swagger';
        Swal.fire({ 
            title: `Generating ${docLabel}...`, 
            text: 'Scanning API controllers and compiling API specifications...', 
            allowOutsideClick: false, 
            didOpen: () => Swal.showLoading() 
        });

        fetch('{{ route("devzone.docs.generate") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ driver: driver })
        })
        .then(r => r.json())
        .then(res => {
            Swal.close();
            showTerminalModal(`${docLabel} Generation Output`, res.output, res.message);
        })
        .catch(err => Swal.fire('Error', err.message || 'Generation failed', 'error'));
    }

    function toggleDebugAction(enable) {
        const actionText = enable ? 'Enable' : 'Disable';
        Swal.fire({
            title: `${actionText} APP_DEBUG?`,
            text: enable ? 'Enabling debug displays error details and stack traces.' : 'Disabling debug hides verbose error details for security.',
            icon: enable ? 'warning' : 'info',
            showCancelButton: true,
            confirmButtonText: `Yes, ${actionText}`,
            confirmButtonColor: enable ? '#f59e0b' : '#3b82f6',
            cancelButtonColor: '#64748b',
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Updating...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch('{{ route("devzone.debug.toggle") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ enable: enable })
                })
                .then(r => r.json())
                .then(res => {
                    Swal.fire('Success', res.message, 'success').then(() => window.location.reload());
                })
                .catch(err => Swal.fire('Error', err.message, 'error'));
            }
        });
    }

    function createDatabaseBackupAction() {
        Swal.fire({
            title: 'Create Database Backup?',
            text: 'This will export a full SQL dump of the database into storage/app/backup.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Start Backup',
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Creating Backup...', text: 'Dumping database tables... Please wait...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch('{{ route("devzone.backup.create") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire('Backup Created!', `${res.message} (${res.formatted_size})`, 'success').then(() => {
                            switchTab('backup');
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Backup Failed', res.message, 'error');
                    }
                })
                .catch(err => Swal.fire('Error', err.message, 'error'));
            }
        });
    }

    function deleteBackupAction(filename) {
        Swal.fire({
            title: 'Delete Backup?',
            html: `Permanently delete <code>${filename}</code>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`{{ url(config('devzone.path', 'developer-panel') . '/backup/delete') }}/${encodeURIComponent(filename)}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                })
                .then(r => r.json())
                .then(res => {
                    Swal.fire('Deleted', res.message, 'success').then(() => window.location.reload());
                })
                .catch(err => Swal.fire('Error', err.message, 'error'));
            }
        });
    }

    function clearLogsAction() {
        Swal.fire({
            title: 'Clear Activity Logs?',
            text: 'This will permanently wipe the developer audit trail history.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Clear All',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
        }).then(result => {
            if (result.isConfirmed) {
                fetch('{{ route("devzone.logs.clear") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                })
                .then(r => r.json())
                .then(res => {
                    Swal.fire('Cleared', res.message, 'success').then(() => window.location.reload());
                })
                .catch(err => Swal.fire('Error', err.message, 'error'));
            }
        });
    }
</script>
@endpush
