<?php

namespace App\Providers\Filament;

use App\Filament\Pages\NewDashboard;
use App\Filament\Pages\NewLogin;
use App\Filament\Pages\Profile;
use App\Filament\Resources\Observations\ObservationResource;
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
use Hammadzafar05\FilamentMobilePreset\FilamentMobilePresetPlugin;
use Hammadzafar05\MobileBottomNav\MobileBottomNav;
use Hammadzafar05\MobileBottomNav\MobileBottomNavItem;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Martin6363\FilamentClickSpark\FilamentClickSparkPlugin;
use Openplain\FilamentShadcnTheme\Color;
use YousefAman\FilamentAutosave\AutosavePlugin;
use Ysfkaya\ShipLog\ShipLogPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->brandName('ESH AUDIT')
            ->brandLogo(fn (): ?string => Storage::disk('public')->exists('logo/esh-logo-black.png')
                ? Storage::disk('public')->url('logo/esh-logo-black.png') : null)
            ->darkModeBrandLogo(fn (): ?string => Storage::disk('public')->exists('logo/esh-logo-white.png')
                ? Storage::disk('public')->url('logo/esh-logo-white.png') : null)
            ->brandLogoHeight('2.75rem')
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
            ->font('Manrope')
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
                FilamentMobilePresetPlugin::make()
                    ->bottomNav(MobileBottomNav::make()->items([
                        MobileBottomNavItem::make('Dashboard')
                            ->icon('heroicon-o-home')
                            ->url(fn (): string => NewDashboard::getUrl())
                            ->isActive(fn (): bool => request()->routeIs('filament.admin.pages.dashboard')),
                        MobileBottomNavItem::make('Observations')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->url(fn (): string => ObservationResource::getUrl())
                            ->visible(fn (): bool => ObservationResource::canViewAny())
                            ->isActive(fn (): bool => request()->routeIs('filament.admin.resources.observations.*')),
                    ])),
                FilamentClickSparkPlugin::make()
                    ->clickAnywhere(true),
                AutosavePlugin::make()->debounce(2000),
                ShipLogPlugin::make()
                    ->usingMarkdown(base_path('CHANGELOG.md'))
                    ->navigationLabel('What’s new')
                    ->navigationSort(100)
                    ->fab(enabled: false)
                    ->authorizeView(fn (): bool => auth()->check())
                    ->authorizeManage(fn (): bool => auth()->user()?->hasRole('developer') ?? false),
                GlobalSearchModalPlugin::make(),
                EasyFooterPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
