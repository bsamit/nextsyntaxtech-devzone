@props([
    'type' => 'info',
    'title' => null,
    'message' => null,
    'icon' => null,
    'dismissible' => true,
    'session' => false,
])

@php
    $normalizedType = strtolower($type) === 'error' ? 'danger' : strtolower($type);

    $defaultIcons = [
        'success' => 'bx bx-check-circle',
        'danger'  => 'bx bx-error-circle',
        'warning' => 'bx bx-alarm-exclamation',
        'info'    => 'bx bx-info-circle',
        'primary' => 'bx bx-bell',
    ];

    $iconClass = $icon ?? ($defaultIcons[$normalizedType] ?? 'bx bx-info-circle');
@endphp

@if($session)
    {{-- Automatic Session Flash Messages Handler --}}
    @if(session('success'))
        <div class="alert devzone-alert devzone-alert-success {{ $dismissible ? 'alert-dismissible' : '' }} fade show d-flex align-items-start gap-3 mb-3" role="alert">
            <div class="devzone-alert-icon-wrap flex-shrink-0">
                <i class="bx bx-check-circle fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <div class="devzone-alert-message small">{{ session('success') }}</div>
            </div>
            @if($dismissible)
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="alert devzone-alert devzone-alert-danger {{ $dismissible ? 'alert-dismissible' : '' }} fade show d-flex align-items-start gap-3 mb-3" role="alert">
            <div class="devzone-alert-icon-wrap flex-shrink-0">
                <i class="bx bx-error-circle fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <div class="devzone-alert-message small">{{ session('error') }}</div>
            </div>
            @if($dismissible)
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            @endif
        </div>
    @endif

    @if(session('warning'))
        <div class="alert devzone-alert devzone-alert-warning {{ $dismissible ? 'alert-dismissible' : '' }} fade show d-flex align-items-start gap-3 mb-3" role="alert">
            <div class="devzone-alert-icon-wrap flex-shrink-0">
                <i class="bx bx-alarm-exclamation fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <div class="devzone-alert-message small">{{ session('warning') }}</div>
            </div>
            @if($dismissible)
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            @endif
        </div>
    @endif

    @if(session('info'))
        <div class="alert devzone-alert devzone-alert-info {{ $dismissible ? 'alert-dismissible' : '' }} fade show d-flex align-items-start gap-3 mb-3" role="alert">
            <div class="devzone-alert-icon-wrap flex-shrink-0">
                <i class="bx bx-info-circle fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <div class="devzone-alert-message small">{{ session('info') }}</div>
            </div>
            @if($dismissible)
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            @endif
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert devzone-alert devzone-alert-danger {{ $dismissible ? 'alert-dismissible' : '' }} fade show d-flex align-items-start gap-3 mb-3" role="alert">
            <div class="devzone-alert-icon-wrap flex-shrink-0">
                <i class="bx bx-error-circle fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <div class="devzone-alert-title fw-bold small mb-1">Please correct the following errors:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @if($dismissible)
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            @endif
        </div>
    @endif

@else
    {{-- Direct Explicit Alert Mode --}}
    <div {{ $attributes->merge(['class' => 'alert devzone-alert devzone-alert-' . $normalizedType . ($dismissible ? ' alert-dismissible' : '') . ' fade show d-flex align-items-start gap-3 mb-3']) }} role="alert">
        <div class="devzone-alert-icon-wrap flex-shrink-0">
            <i class="{{ $iconClass }} fs-5"></i>
        </div>
        <div class="flex-grow-1">
            @if(!empty($title))
                <div class="devzone-alert-title fw-bold small mb-1">{{ $title }}</div>
            @endif
            <div class="devzone-alert-message small">
                {{ $slot->isEmpty() ? $message : $slot }}
            </div>
        </div>
        @if($dismissible)
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        @endif
    </div>
@endif
