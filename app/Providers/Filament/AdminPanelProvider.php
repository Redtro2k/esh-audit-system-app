<?php

namespace App\Providers\Filament;

use App\Filament\Pages\NewDashboard;
use App\Filament\Pages\NewLogin;
use App\Filament\Pages\NewRegistration;
use App\Filament\Pages\Profile;
use App\Filament\Resources\Teams\TeamResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\LatestOngoing;
use App\Filament\Widgets\StatsOverview;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color as FilamentColor;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Hammadzafar05\MobileBottomNav\MobileBottomNav;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Openplain\FilamentShadcnTheme\Color;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->brandName('ESH AUDIT')
            ->brandLogo(Storage::disk('public')->url('logo/esh-logo-black.png'))
            ->darkModeBrandLogo(Storage::disk('public')->url('logo/esh-logo-white.png'))
            ->brandLogoHeight('4.5rem')
            ->default()
            ->id('admin')
            ->path('admin')
            ->maxContentWidth(Width::Full)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->profile(Profile::class)
            ->login(NewLogin::class)
            ->globalSearchKeyBindings(['mod+k'])
            ->globalSearchFieldKeyBindingSuffix()
            ->topbar()
            // ->registration(NewRegistration::class)
            ->colors([
                'primary' => Color::adaptive(
                    lightColor: FilamentColor::Indigo,
                    darkColor: FilamentColor::Sky
                ),
            ])
            ->databaseNotifications()
            ->resources([
                TeamResource::class,
                UserResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->emailVerification()
            ->passwordReset()
            ->pages([
                NewDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                //                FilamentInfoWidget::class,
                StatsOverview::class,
                LatestOngoing::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentApexChartsPlugin::make(),
                MobileBottomNav::make(),
                GlobalSearchModalPlugin::make(),
                EasyFooterPlugin::make()
                    ->withLogo(
                        file_exists(public_path('logo/logo-esh.png'))
                            ? asset('logo/logo-esh.png')
                            : Storage::disk('public')->url('Images/Logo_desktop.png')
                    )
                    ->withLinks([
                        ['title' => 'About', 'url' => 'https://example.com/about'],
                        ['title' => 'CGV', 'url' => 'https://example.com/cgv'],
                        ['title' => 'Privacy Policy', 'url' => 'https://example.com/privacy-policy'],
                    ]),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
