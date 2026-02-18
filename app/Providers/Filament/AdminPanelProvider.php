<?php

namespace App\Providers\Filament;

use App\Filament\Pages\NewDashboard;
use App\Filament\Pages\NewLogin;
use App\Filament\Pages\NewRegistration;
use App\Filament\Widgets\LatestOngoing;
use App\Filament\Widgets\StatsOverview;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Openplain\FilamentShadcnTheme\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Colors\Color as FilamentColor;
use App\Filament\Pages\Profile;
use Illuminate\Support\Facades\Storage;
use Moataz01\FilamentNotificationSound\FilamentNotificationSoundPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->registration()
            ->brandName('ESH AUDIT')
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->profile(Profile::class)
            ->login(NewLogin::class)
            ->topbar(false)
            ->registration(NewRegistration::class)
            ->colors([
                'primary' =>Color::adaptive(
                    lightColor: FilamentColor::Indigo,
                    darkColor: FilamentColor::Sky
                ),
            ])
            ->databaseNotifications()
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
                EasyFooterPlugin::make()
                    ->withLogo(Storage::url('Images/Logo_desktop.png'))
                    ->withLinks([
                        ['title' => 'About', 'url' => 'https://example.com/about'],
                        ['title' => 'CGV', 'url' => 'https://example.com/cgv'],
                        ['title' => 'Privacy Policy', 'url' => 'https://example.com/privacy-policy']
                    ]),
                FilamentNotificationSoundPlugin::make()
                    ->soundPath(Storage::url('Sound/messenger_style_notification.wav'))
                    ->volume(1.0)
                    ->showAnimation(true)
                    ->enabled(true)
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
