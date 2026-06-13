@php
    $isMultiFactorChallenge = filled($this->userUndertakingMultiFactorAuthentication);
@endphp

<section class="esh-login-page">
    <div class="esh-login-shell" aria-label="ESH Safety Audit login">
        <aside class="esh-login-visual" aria-label="Safety audit overview">
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

        <main @class([
            'esh-login-panel',
            'esh-login-panel-welcome' => $this->hasRememberedLoginProfile(),
        ]) aria-label="Sign in form">
            <div class="esh-login-logo" aria-label="ESH Audit">
                <img
                    class="esh-login-logo-light"
                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url('logo/esh-logo-black.png') }}"
                    alt="ESH Audit logo"
                />
                <img
                    class="esh-login-logo-dark"
                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url('logo/esh-logo-white.png') }}"
                    alt="ESH Audit logo"
                />
            </div>

            @if ($this->hasRememberedLoginProfile())
                <div class="esh-login-welcome-card" aria-label="Remembered login profile">
                    <img
                        src="{{ $this->rememberedLoginProfile['avatar_url'] ?? asset('favicon.svg') }}"
                        alt=""
                        class="esh-login-welcome-avatar"
                    />

                    <div class="esh-login-welcome-copy">
                        <span>Welcome back</span>
                        <strong>{{ $this->rememberedLoginProfile['name'] }}</strong>

                        @if (filled($this->rememberedLoginProfile['last_login_at']))
                            <p>
                                Last signed in
                                {{ \Illuminate\Support\Carbon::parse($this->rememberedLoginProfile['last_login_at'])->diffForHumans() }}
                            </p>
                        @endif
                    </div>

                    <button type="button" wire:click="useAnotherProfile" class="esh-login-profile-switch">
                        Use another profile
                    </button>
                </div>
            @endif

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
