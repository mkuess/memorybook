<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\MemoryPageResource;
use App\Filament\Resources\PlaqueOrderResource;
use App\Filament\Resources\ReportResource;
use App\Models\MemoryPage;
use App\Models\PlaqueOrder;
use App\Models\Report;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingWorkOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make(
                'Offene Meldungen',
                Report::where('status', 'open')->count()
            )
                ->description('Meldungen mit Status "Offen"')
                ->icon('heroicon-o-flag')
                ->color('danger')
                ->url(ReportResource::getUrl()),

            Stat::make(
                'Plaketten offen',
                PlaqueOrder::whereIn('status', ['requested', 'in_review'])->count()
            )
                ->description('Angefragt oder in Bearbeitung')
                ->icon('heroicon-o-square-2-stack')
                ->color('warning')
                ->url(PlaqueOrderResource::getUrl()),

            Stat::make(
                'Neue Erinnerungsseiten',
                MemoryPage::where('created_at', '>=', now()->subDays(7))->count()
            )
                ->description('Erstellt in den letzten 7 Tagen')
                ->icon('heroicon-o-book-open')
                ->color('info')
                ->url(MemoryPageResource::getUrl()),

            Stat::make(
                'Gesperrte Seiten',
                MemoryPage::where('is_locked', true)->count()
            )
                ->description('Durch die Verwaltung gesperrt')
                ->icon('heroicon-o-lock-closed')
                ->color('gray')
                ->url(MemoryPageResource::getUrl()),
        ];
    }
}
