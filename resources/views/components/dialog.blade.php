<!-- DevZone Interactive Dialog & Terminal Console System (Bootstrap 5 Powered, Zero CDN) -->
<div class="modal fade devzone-dialog-modal" id="devzoneDialogModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" id="devzoneDialogContainer" style="max-width: 460px; transition: max-width 0.28s ease;">
        <div class="modal-content devzone-dialog-card border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <!-- Status Icon Wrap -->
                <div id="devzoneDialogIconWrap" class="devzone-dialog-icon-wrap mb-3 mx-auto">
                    <i id="devzoneDialogIcon" class="bx"></i>
                </div>

                <!-- Custom Uncommon Animated Loader -->
                <div id="devzoneDialogLoadingWrap" class="devzone-dialog-loading-wrap d-none mb-3">
                    <div class="devzone-uncommon-loader">
                        <div class="devzone-loader-outer-ring"></div>
                        <div class="devzone-loader-inner-ring"></div>
                        <div class="devzone-loader-core-orb"></div>
                        <i class="bx bx-terminal devzone-loader-icon"></i>
                    </div>
                    <div class="devzone-loader-pulse-dots d-flex align-items-center justify-content-center gap-1 mt-2">
                        <span></span><span></span><span></span>
                    </div>
                </div>

                <!-- Title -->
                <h5 id="devzoneDialogTitle" class="modal-title fw-bold mb-2"></h5>

                <!-- Message / HTML Content -->
                <div id="devzoneDialogBody" class="devzone-dialog-body small mb-3"></div>

                <!-- In-Modal Terminal Console Wrap -->
                <div id="devzoneDialogTerminalWrap" class="devzone-dialog-terminal-wrap d-none mt-3 text-start">
                    <div class="devzone-terminal-header d-flex align-items-center justify-content-between px-3 py-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="devzone-terminal-dot dot-red"></span>
                            <span class="devzone-terminal-dot dot-yellow"></span>
                            <span class="devzone-terminal-dot dot-green"></span>
                            <span class="small font-monospace ms-2 text-muted" id="devzoneDialogTerminalMeta">Output:</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-semibold" onclick="DevZoneAlert.copyTerminal()">
                            <i class="bx bx-copy"></i> Copy Output
                        </button>
                    </div>
                    <div class="terminal-box" id="devzoneDialogTerminalBox"></div>
                </div>

                <!-- Action Buttons -->
                <div id="devzoneDialogActions" class="d-flex align-items-center justify-content-center gap-2 mt-4">
                    <button type="button" class="btn btn-action-secondary px-3 py-2 fw-semibold" id="devzoneDialogCancelBtn">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-action-primary px-4 py-2 fw-semibold" id="devzoneDialogConfirmBtn">
                        Confirm
                    </button>
                    <button type="button" class="btn btn-action-primary px-4 py-2 fw-semibold d-none" id="devzoneDialogOkBtn">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DevZone Modern Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1099;">
    <div id="devzoneToast" class="toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2 py-3 px-3">
                <i id="devzoneToastIcon" class="bx fs-4"></i>
                <div>
                    <div id="devzoneToastTitle" class="fw-bold small"></div>
                    <div id="devzoneToastText" class="small text-muted" style="font-size: 12px;"></div>
                </div>
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<style>
    /* Smooth Minimal Modal Open & Close Animation */
    .modal.fade.devzone-dialog-modal .modal-dialog {
        transform: scale(0.93) translateY(-12px);
        opacity: 0;
        transition: transform 0.26s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.22s ease, max-width 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
    .modal.show.devzone-dialog-modal .modal-dialog {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
    .modal-backdrop.show {
        opacity: 0.62 !important;
        backdrop-filter: blur(6px) !important;
        -webkit-backdrop-filter: blur(6px) !important;
        background-color: #050914 !important;
        transition: opacity 0.22s ease !important;
    }

    /* DevZone Dialog Modal Theming */
    .devzone-dialog-card {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 16px !important;
        color: var(--text-main) !important;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.28) !important;
    }

    .devzone-dialog-icon-wrap {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        transition: transform 0.2s ease;
    }

    .devzone-dialog-icon-wrap.icon-success {
        background: rgba(5, 150, 105, 0.12);
        color: #059669;
        border: 1px solid rgba(5, 150, 105, 0.3);
    }
    .devzone-dialog-icon-wrap.icon-error,
    .devzone-dialog-icon-wrap.icon-danger {
        background: rgba(220, 38, 38, 0.12);
        color: #dc2626;
        border: 1px solid rgba(220, 38, 38, 0.3);
    }
    .devzone-dialog-icon-wrap.icon-warning {
        background: rgba(217, 119, 6, 0.12);
        color: #d97706;
        border: 1px solid rgba(217, 119, 6, 0.3);
    }
    .devzone-dialog-icon-wrap.icon-question {
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.3);
    }
    .devzone-dialog-icon-wrap.icon-info {
        background: rgba(2, 132, 199, 0.12);
        color: #0284c7;
        border: 1px solid rgba(2, 132, 199, 0.3);
    }

    .devzone-dialog-body {
        color: var(--text-muted);
        line-height: 1.5;
    }
    .devzone-dialog-body code {
        background: var(--command-code-bg);
        color: var(--command-code-text);
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
    }

    /* ============================================================== */
    /* UNCOMMON FUTURISTIC CYBER LOADER                                */
    /* ============================================================== */
    .devzone-uncommon-loader {
        position: relative;
        width: 82px;
        height: 82px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .devzone-loader-outer-ring {
        position: absolute;
        width: 82px;
        height: 82px;
        border-radius: 50%;
        border: 3px solid transparent;
        border-top-color: #2563eb;
        border-right-color: #06b6d4;
        filter: drop-shadow(0 0 10px rgba(37, 99, 235, 0.5));
        animation: devzone-spin-cw 1.25s cubic-bezier(0.55, 0.15, 0.45, 0.85) infinite;
    }
    .devzone-loader-inner-ring {
        position: absolute;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 2.5px solid transparent;
        border-bottom-color: #7c3aed;
        border-left-color: #38bdf8;
        filter: drop-shadow(0 0 8px rgba(124, 58, 237, 0.45));
        animation: devzone-spin-ccw 1s cubic-bezier(0.55, 0.15, 0.45, 0.85) infinite;
    }
    .devzone-loader-core-orb {
        position: absolute;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.32) 0%, rgba(6, 182, 212, 0.12) 65%, transparent 100%);
        animation: devzone-pulse-orb 1.5s ease-in-out infinite;
    }
    .devzone-loader-icon {
        position: relative;
        z-index: 3;
        font-size: 26px;
        color: #2563eb;
        animation: devzone-icon-breathe 1.5s ease-in-out infinite;
    }

    @keyframes devzone-spin-cw {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    @keyframes devzone-spin-ccw {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(-360deg); }
    }
    @keyframes devzone-pulse-orb {
        0%, 100% { transform: scale(0.88); opacity: 0.55; }
        50% { transform: scale(1.22); opacity: 1; }
    }
    @keyframes devzone-icon-breathe {
        0%, 100% { transform: scale(0.92); opacity: 0.8; }
        50% { transform: scale(1.1); opacity: 1; filter: drop-shadow(0 0 8px rgba(37, 99, 235, 0.7)); }
    }

    /* Wave Dots Below Loader */
    .devzone-loader-pulse-dots span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: var(--primary);
        display: inline-block;
        animation: devzone-pulse-dots 1.4s infinite ease-in-out both;
    }
    .devzone-loader-pulse-dots span:nth-child(1) { animation-delay: -0.32s; }
    .devzone-loader-pulse-dots span:nth-child(2) { animation-delay: -0.16s; }
    @keyframes devzone-pulse-dots {
        0%, 80%, 100% { transform: scale(0.4); opacity: 0.3; }
        40% { transform: scale(1); opacity: 1; }
    }

    /* In-Modal Terminal Console Box */
    .devzone-dialog-terminal-wrap {
        border: 1px solid var(--border-color);
        border-radius: 10px;
        overflow: hidden;
        background: var(--bg-card-sub);
    }
    .devzone-terminal-header {
        background: var(--bg-card-sub);
        border-bottom: 1px solid var(--border-color);
    }
    .devzone-terminal-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    .dot-red { background: #ef4444; }
    .dot-yellow { background: #f59e0b; }
    .dot-green { background: #10b981; }

    #devzoneDialogTerminalBox {
        border-radius: 0;
        border: none;
        max-height: 380px;
        margin-bottom: 0;
    }

    /* DevZone Toast Theming */
    .devzone-toast {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
        border-radius: 12px !important;
    }
    .devzone-toast-success { border-left: 4px solid #059669 !important; }
    .devzone-toast-error, .devzone-toast-danger { border-left: 4px solid #dc2626 !important; }
    .devzone-toast-warning { border-left: 4px solid #d97706 !important; }
    .devzone-toast-info { border-left: 4px solid #0284c7 !important; }
</style>

<script>
    /**
     * DevZoneAlert - Native Bootstrap 5 Alert, Confirm, Loading, Terminal & Toast System.
     * Everything stays and executes smoothly within the exact same modal.
     */
    const DevZoneAlert = (function() {
        let bsModalInstance = null;
        let bsToastInstance = null;
        let activeResolve = null;
        let currentOnOkCallback = null;

        function getModalEl() {
            return document.getElementById('devzoneDialogModal');
        }

        function getModalContainer() {
            return document.getElementById('devzoneDialogContainer');
        }

        function getModal() {
            const el = getModalEl();
            if (!el) return null;
            if (!bsModalInstance) {
                bsModalInstance = new bootstrap.Modal(el, {
                    backdrop: 'static',
                    keyboard: false
                });
            }
            return bsModalInstance;
        }

        function getToast() {
            const el = document.getElementById('devzoneToast');
            if (!el) return null;
            if (!bsToastInstance) {
                bsToastInstance = new bootstrap.Toast(el, { delay: 2500 });
            }
            return bsToastInstance;
        }

        function setModalWidth(width = '460px') {
            const container = getModalContainer();
            if (container) {
                container.style.maxWidth = width;
            }
        }

        /**
         * 1. Confirmation Modal Flow:
         * Stays OPEN upon confirmation so the in-modal loader takes over seamlessly.
         */
        function confirm(optionsOrTitle, text = '', confirmText = 'Confirm') {
            let opts = {};
            if (typeof optionsOrTitle === 'string') {
                opts = { title: optionsOrTitle, text: text, confirmButtonText: confirmText };
            } else {
                opts = optionsOrTitle || {};
            }

            return new Promise((resolve) => {
                activeResolve = resolve;
                currentOnOkCallback = null;
                const modalEl = getModalEl();

                if (!modalEl) {
                    const ok = window.confirm(opts.title + (opts.text ? "\n" + opts.text : ''));
                    resolve({ isConfirmed: ok, isDismissed: !ok });
                    return;
                }

                setModalWidth('460px');

                const iconWrap = document.getElementById('devzoneDialogIconWrap');
                const iconEl = document.getElementById('devzoneDialogIcon');
                const loadingWrap = document.getElementById('devzoneDialogLoadingWrap');
                const terminalWrap = document.getElementById('devzoneDialogTerminalWrap');
                const titleEl = document.getElementById('devzoneDialogTitle');
                const bodyEl = document.getElementById('devzoneDialogBody');
                const actionsEl = document.getElementById('devzoneDialogActions');
                const confirmBtn = document.getElementById('devzoneDialogConfirmBtn');
                const cancelBtn = document.getElementById('devzoneDialogCancelBtn');
                const okBtn = document.getElementById('devzoneDialogOkBtn');

                // State: Confirmation Prompt
                iconWrap.classList.remove('d-none');
                loadingWrap.classList.add('d-none');
                terminalWrap.classList.add('d-none');
                actionsEl.classList.remove('d-none');

                const iconType = opts.icon || 'question';
                iconWrap.className = 'devzone-dialog-icon-wrap mb-3 mx-auto icon-' + iconType;
                if (iconType === 'success') iconEl.className = 'bx bx-check';
                else if (iconType === 'error' || iconType === 'danger') iconEl.className = 'bx bx-x';
                else if (iconType === 'warning') iconEl.className = 'bx bx-alarm-exclamation';
                else if (iconType === 'question') iconEl.className = 'bx bx-help-circle';
                else iconEl.className = 'bx bx-info-circle';

                titleEl.textContent = opts.title || 'Please Confirm';
                if (opts.html) {
                    bodyEl.innerHTML = opts.html;
                } else {
                    bodyEl.textContent = opts.text || '';
                }

                cancelBtn.classList.remove('d-none');
                cancelBtn.textContent = opts.cancelButtonText || 'Cancel';

                confirmBtn.classList.remove('d-none');
                confirmBtn.textContent = opts.confirmButtonText || 'Confirm';
                confirmBtn.style.backgroundColor = opts.confirmButtonColor || '';
                confirmBtn.style.borderColor = opts.confirmButtonColor || '';

                okBtn.classList.add('d-none');

                // On Confirm: Do NOT close the modal! Stay open for in-modal loading
                confirmBtn.onclick = function() {
                    resolve({ isConfirmed: true, isDismissed: false });
                };

                // On Cancel: Close modal
                cancelBtn.onclick = function() {
                    close();
                    resolve({ isConfirmed: false, isDismissed: true });
                };

                const modal = getModal();
                modal.show();

                if (typeof opts.didOpen === 'function') {
                    opts.didOpen();
                }
            });
        }

        /**
         * 2. In-Modal Loading Transition:
         * Switches the current modal into the uncommon loader state with no flickering.
         */
        function loading(title = 'Processing...', text = 'Please wait while the operation completes...') {
            setModalWidth('460px');
            const iconWrap = document.getElementById('devzoneDialogIconWrap');
            const loadingWrap = document.getElementById('devzoneDialogLoadingWrap');
            const terminalWrap = document.getElementById('devzoneDialogTerminalWrap');
            const titleEl = document.getElementById('devzoneDialogTitle');
            const bodyEl = document.getElementById('devzoneDialogBody');
            const actionsEl = document.getElementById('devzoneDialogActions');

            if (iconWrap) iconWrap.classList.add('d-none');
            if (loadingWrap) loadingWrap.classList.remove('d-none');
            if (terminalWrap) terminalWrap.classList.add('d-none');
            if (actionsEl) actionsEl.classList.add('d-none');

            if (titleEl) titleEl.textContent = title;
            if (bodyEl) bodyEl.innerHTML = text;

            const modal = getModal();
            if (modal) modal.show();
        }

        /**
         * 3. In-Modal Terminal Response:
         * Displays console output, exit code, and an [OK] button in the same modal.
         */
        function terminal(opts = {}) {
            setModalWidth('760px');

            const iconWrap = document.getElementById('devzoneDialogIconWrap');
            const iconEl = document.getElementById('devzoneDialogIcon');
            const loadingWrap = document.getElementById('devzoneDialogLoadingWrap');
            const terminalWrap = document.getElementById('devzoneDialogTerminalWrap');
            const terminalMeta = document.getElementById('devzoneDialogTerminalMeta');
            const terminalBox = document.getElementById('devzoneDialogTerminalBox');
            const titleEl = document.getElementById('devzoneDialogTitle');
            const bodyEl = document.getElementById('devzoneDialogBody');
            const actionsEl = document.getElementById('devzoneDialogActions');
            const confirmBtn = document.getElementById('devzoneDialogConfirmBtn');
            const cancelBtn = document.getElementById('devzoneDialogCancelBtn');
            const okBtn = document.getElementById('devzoneDialogOkBtn');

            if (loadingWrap) loadingWrap.classList.add('d-none');

            const isSuccess = (opts.isSuccess !== false);
            if (iconWrap && iconEl) {
                iconWrap.classList.remove('d-none');
                iconWrap.className = 'devzone-dialog-icon-wrap mb-3 mx-auto ' + (isSuccess ? 'icon-success' : 'icon-error');
                iconEl.className = isSuccess ? 'bx bx-check' : 'bx bx-x';
            }

            if (titleEl) titleEl.textContent = opts.title || (isSuccess ? 'Execution Finished' : 'Execution Failed');
            if (bodyEl) {
                bodyEl.innerHTML = opts.message || (isSuccess ? 'Command completed successfully.' : 'An error occurred during execution.');
            }

            if (terminalWrap && terminalBox) {
                terminalWrap.classList.remove('d-none');
                terminalMeta.textContent = opts.meta || (isSuccess ? 'Status: Finished (Exit Code 0)' : 'Status: Failed');
                terminalBox.textContent = opts.output || 'Execution finished with no text output.';
            }

            if (actionsEl) actionsEl.classList.remove('d-none');
            if (confirmBtn) confirmBtn.classList.add('d-none');
            if (cancelBtn) cancelBtn.classList.add('d-none');
            if (okBtn) {
                okBtn.classList.remove('d-none');
                okBtn.focus();
            }

            currentOnOkCallback = (typeof opts.onOk === 'function') ? opts.onOk : null;

            okBtn.onclick = function() {
                close();
                if (currentOnOkCallback) {
                    currentOnOkCallback();
                    currentOnOkCallback = null;
                }
            };

            const modal = getModal();
            if (modal) modal.show();
        }

        /**
         * 4. In-Modal Result Message (Success, Error, Info):
         * Displays response message and an [OK] button in the same modal.
         */
        function result(opts = {}) {
            setModalWidth('460px');

            const iconWrap = document.getElementById('devzoneDialogIconWrap');
            const iconEl = document.getElementById('devzoneDialogIcon');
            const loadingWrap = document.getElementById('devzoneDialogLoadingWrap');
            const terminalWrap = document.getElementById('devzoneDialogTerminalWrap');
            const titleEl = document.getElementById('devzoneDialogTitle');
            const bodyEl = document.getElementById('devzoneDialogBody');
            const actionsEl = document.getElementById('devzoneDialogActions');
            const confirmBtn = document.getElementById('devzoneDialogConfirmBtn');
            const cancelBtn = document.getElementById('devzoneDialogCancelBtn');
            const okBtn = document.getElementById('devzoneDialogOkBtn');

            if (loadingWrap) loadingWrap.classList.add('d-none');
            if (terminalWrap) terminalWrap.classList.add('d-none');

            const iconType = opts.icon || 'success';
            if (iconWrap && iconEl) {
                iconWrap.classList.remove('d-none');
                iconWrap.className = 'devzone-dialog-icon-wrap mb-3 mx-auto icon-' + iconType;
                if (iconType === 'success') iconEl.className = 'bx bx-check';
                else if (iconType === 'error' || iconType === 'danger') iconEl.className = 'bx bx-x';
                else if (iconType === 'warning') iconEl.className = 'bx bx-alarm-exclamation';
                else iconEl.className = 'bx bx-info-circle';
            }

            if (titleEl) titleEl.textContent = opts.title || 'Done';
            if (bodyEl) {
                if (opts.html) bodyEl.innerHTML = opts.html;
                else bodyEl.textContent = opts.message || opts.text || '';
            }

            if (actionsEl) actionsEl.classList.remove('d-none');
            if (confirmBtn) confirmBtn.classList.add('d-none');
            if (cancelBtn) cancelBtn.classList.add('d-none');
            if (okBtn) {
                okBtn.classList.remove('d-none');
                okBtn.focus();
            }

            currentOnOkCallback = (typeof opts.onOk === 'function') ? opts.onOk : null;

            okBtn.onclick = function() {
                close();
                if (currentOnOkCallback) {
                    currentOnOkCallback();
                    currentOnOkCallback = null;
                }
            };

            const modal = getModal();
            if (modal) modal.show();
        }

        /**
         * 5. Toast Notification System
         */
        function showToast(opts) {
            return new Promise((resolve) => {
                const toastEl = document.getElementById('devzoneToast');
                const iconEl = document.getElementById('devzoneToastIcon');
                const titleEl = document.getElementById('devzoneToastTitle');
                const textEl = document.getElementById('devzoneToastText');

                const iconType = opts.icon || 'info';
                toastEl.className = `toast align-items-center border-0 shadow-lg devzone-toast devzone-toast-${iconType}`;
                if (iconType === 'success') iconEl.className = 'bx bx-check-circle text-success fs-4';
                else if (iconType === 'error' || iconType === 'danger') iconEl.className = 'bx bx-x-circle text-danger fs-4';
                else if (iconType === 'warning') iconEl.className = 'bx bx-alarm-exclamation text-warning fs-4';
                else iconEl.className = 'bx bx-info-circle text-primary fs-4';

                titleEl.textContent = opts.title || '';
                textEl.textContent = opts.text || '';
                if (!opts.text) textEl.style.display = 'none';
                else textEl.style.display = 'block';

                const toast = getToast();
                toast.show();
                setTimeout(() => {
                    resolve({ isConfirmed: true });
                }, opts.timer || 2500);
            });
        }

        /**
         * Copy Terminal output to clipboard
         */
        function copyTerminal() {
            const terminalBox = document.getElementById('devzoneDialogTerminalBox');
            if (!terminalBox) return;
            navigator.clipboard.writeText(terminalBox.textContent).then(() => {
                showToast({ icon: 'success', title: 'Output copied to clipboard!' });
            });
        }

        function close() {
            const modal = getModal();
            if (modal) {
                modal.hide();
            }
            if (activeResolve) {
                activeResolve({ isConfirmed: false, isDismissed: true });
                activeResolve = null;
            }
            setTimeout(() => setModalWidth('460px'), 200);
        }

        /**
         * Generic Fire adapter (for SweetAlert drop-in compatibility)
         */
        function fire(optionsOrTitle, text = '', icon = 'info') {
            let opts = {};
            if (typeof optionsOrTitle === 'string') {
                opts = { title: optionsOrTitle, text: text, icon: icon };
            } else {
                opts = optionsOrTitle || {};
            }

            if (opts.toast) {
                return showToast(opts);
            }

            if (opts.showCancelButton) {
                return confirm(opts);
            }

            if (opts.icon === 'loading' || opts.isLoading) {
                loading(opts.title, opts.text);
                return Promise.resolve({ isConfirmed: true });
            }

            return new Promise((resolve) => {
                result({
                    title: opts.title,
                    message: opts.text || opts.html || '',
                    html: opts.html,
                    icon: opts.icon || 'info',
                    onOk: () => resolve({ isConfirmed: true, isDismissed: false })
                });
            });
        }

        return {
            fire: fire,
            confirm: confirm,
            loading: loading,
            showLoading: loading,
            terminal: terminal,
            showTerminal: terminal,
            result: result,
            showResult: result,
            success: function(title, text, onOk = null) {
                return result({ title: title, message: text, icon: 'success', onOk: onOk });
            },
            error: function(title, text, onOk = null) {
                return result({ title: title, message: text, icon: 'error', onOk: onOk });
            },
            warning: function(title, text, onOk = null) {
                return result({ title: title, message: text, icon: 'warning', onOk: onOk });
            },
            info: function(title, text, onOk = null) {
                return result({ title: title, message: text, icon: 'info', onOk: onOk });
            },
            close: close,
            toast: showToast,
            copyTerminal: copyTerminal,
        };
    })();

    // Make DevZoneAlert globally accessible
    window.DevZoneAlert = DevZoneAlert;
    // Map Swal to DevZoneAlert for 100% SweetAlert drop-in replacement
    window.Swal = DevZoneAlert;
</script>
