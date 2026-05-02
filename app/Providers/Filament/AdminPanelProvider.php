<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
            ->brandName('memorybook')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                // Blue-grey primary: #8FA7B0 at 600, #5F737B at 700
                'primary' => [
                    50  => '242, 247, 249',
                    100 => '229, 238, 242',
                    200 => '208, 222, 228',
                    300 => '186, 207, 215',
                    400 => '165, 191, 201',
                    500 => '151, 179, 189',
                    600 => '143, 167, 176',
                    700 => '95, 115, 123',
                    800 => '71, 87, 93',
                    900 => '48, 58, 63',
                    950 => '24, 29, 31',
                ],
                // Warm cream neutral scale — drives bg-gray-50 → #F8F5ED, etc.
                'gray' => [
                    50  => '248, 245, 237',
                    100 => '239, 234, 225',
                    200 => '229, 223, 218',
                    300 => '221, 214, 202',
                    400 => '197, 189, 179',
                    500 => '160, 151, 140',
                    600 => '112, 107, 98',
                    700 => '89, 80, 72',
                    800 => '61, 54, 48',
                    900 => '47, 46, 42',
                    950 => '30, 27, 24',
                ],
                'danger'  => Color::hex('#9A4F3F'),
                'success' => Color::hex('#6F7F68'),
                'warning' => Color::hex('#B08A4A'),
                'info'    => Color::hex('#8FA7B0'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
