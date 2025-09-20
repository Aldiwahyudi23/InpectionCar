<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\LatestInspections;
use App\Filament\Widgets\StatsOverview;
use App\Http\Middleware\EnsureIsAdmin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Modal\Actions\Action as ActionsAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\UserMenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                LatestInspections::class,     // tabel custom
                Widgets\AccountWidget::class,
                StatsOverview::class,         // statistik angka
                // Widgets\FilamentInfoWidget::class,
            ])
            ->navigationItems([
                NavigationItem::make('App')
                    ->url('/dashboard') // arahkan ke frontend
                    ->icon('heroicon-o-device-phone-mobile') // bebas pilih icon
                    ->sort(1)
                    ->openUrlInNewTab(false), // kalau mau di tab baru → true
            ])
          ->userMenuItems([
                'frontend' => UserMenuItem::make()
                    ->label('App')
                    ->url('/dashboard')
                    ->icon('heroicon-o-device-phone-mobile'),
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
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureIsAdmin::class,
            ]);
    }
}
