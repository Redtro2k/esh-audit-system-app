<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use App\Models\Dealer;
use App\Models\Observation;
use App\Observers\DealerObserver;
use App\Observers\ObservationObserver;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use App\Observers\CommentObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Kirschbaum\Commentions\Comment;

class AppServiceProvider extends ServiceProvider
{
    private const LAST_LOGIN_PROFILE_COOKIE = 'esh_last_login_profile';

    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Comment::observe(CommentObserver::class);
        Dealer::observe(DealerObserver::class);
        Observation::observe(ObservationObserver::class);

        Event::listen(Login::class, function (Login $event): void {
            $this->rememberSuccessfulLogin($event->user);
        });

        $this->configureDefaults();
    }

    protected function rememberSuccessfulLogin(Authenticatable $authenticatable): void
    {
        if (! $authenticatable instanceof User) {
            return;
        }

        $loggedInAt = now();

        $authenticatable
            ->forceFill(['last_login_at' => $loggedInAt])
            ->saveQuietly();

        Cookie::queue(
            self::LAST_LOGIN_PROFILE_COOKIE,
            json_encode([
                'name' => $authenticatable->name,
                'username' => $authenticatable->username,
                'avatar_url' => $authenticatable->getFilamentAvatarUrl(),
                'last_login_at' => $loggedInAt->toIso8601String(),
            ]),
            60 * 24 * 365,
            null,
            null,
            null,
            true,
            false,
            'lax',
        );
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
