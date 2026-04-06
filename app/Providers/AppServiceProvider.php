<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use App\Models\Dealer;
use App\Models\Observation;
use App\Observers\DealerObserver;
use App\Observers\ObservationObserver;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use App\Observers\CommentObserver;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Kirschbaum\Commentions\Comment;

class AppServiceProvider extends ServiceProvider
{
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

        $this->configureDefaults();
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
