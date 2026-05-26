@php
    $isMultiFactorChallenge = filled($this->userUndertakingMultiFactorAuthentication);
@endphp

<section class="esh-login-page">
    <div class="esh-login-shell" aria-label="ESH Safety Audit login">
        <aside class="esh-login-visual" aria-label="Safety audit overview">
            <div class="esh-login-brand">
                <span class="esh-login-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="img">
                        <path d="M12 2.75 19 5.4v5.15c0 4.32-2.76 8.18-7 9.82-4.24-1.64-7-5.5-7-9.82V5.4l7-2.65Z" />
                        <path d="m8.65 11.7 2.15 2.15 4.75-5.05" />
                    </svg>
                </span>
                <div>
                    <p>ESH AUDIT</p>
                    <span>Safety Audit Portal</span>
                </div>
            </div>

            <div class="esh-login-hero">
                <span class="esh-login-kicker">HRAD System</span>
                <p>
                    A secure workspace for HR, auditors, and site leaders to review observations,
                    assign ownership, and keep employee safety actions moving.
                </p>
            </div>

            <div class="esh-login-safety-card" aria-label="Audit readiness checklist">
                <div class="esh-login-safety-card-header">
                    <span class="esh-login-icon-chip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" role="img">
                            <path d="M9 6.75h10" />
                            <path d="M9 12h10" />
                            <path d="M9 17.25h10" />
                            <path d="m4.75 6.75.85.85 1.65-1.85" />
                            <path d="m4.75 12 .85.85L7.25 11" />
                            <path d="m4.75 17.25.85.85 1.65-1.85" />
                        </svg>
                    </span>
                    <div>
                        <strong>Audit readiness</strong>
                        <span>Daily controls and HR compliance checkpoints</span>
                    </div>
                </div>

                <div class="esh-login-metrics">
                    <div>
                        <strong>24/7</strong>
                        <span>Safety access</span>
                    </div>
                    <div>
                        <strong>HRAD</strong>
                        <span>Review flow</span>
                    </div>
                    <div>
                        <strong>ESH</strong>
                        <span>Finding control</span>
                    </div>
                </div>
            </div>

            <div class="esh-login-accent-row" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </aside>

        <main class="esh-login-panel" aria-label="Sign in form">
            <div class="esh-login-form-card">
                {{ $this->content }}
            </div>

            <div class="esh-login-panel-footer">
                <span>Protected employee safety workspace</span>
                <span>Audit trail enabled</span>
            </div>
        </main>
    </div>
</section>
