<?php

namespace App\Livewire;

use App\Models\Observation;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use App\Enum\NavigationGroup;

class AnalyticsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::AuditManagement;

    protected function getHeading(): ?string
    {
        return 'Audit Findings Overview';
    }

    protected function getDescription(): ?string
    {
        return 'A quick snapshot of open, pending, and overdue items.';
    }

    protected function getStats(): array
    {
        return [
            Stat::make(
                'Open Findings',
               $this->getObservationQueue()->whereNot('status', 'resolved')->count()
            )
                ->description('All unresolved items')
                ->color('primary')
                ->icon('heroicon-m-flag'),
            Stat::make(
                'Pending Review',
               $this->getObservationQueue()->where('status', 'pending')->count()
            )
                ->description('Awaiting triage or action')
                ->color('warning')
                ->icon('heroicon-m-clock'),
            Stat::make(
                'Resolved',
                $this->getObservationQueue()->where('status', 'resolved')->count()
            )
                ->description('Closed and verified')
                ->color('success')
                ->icon('heroicon-m-check-circle'),
            Stat::make(
                'Overdue Targets',
                $this->getObservationQueue()
                    ->whereNotNull('target_date')
                    ->whereDate('target_date', '<', now())
                    ->where('status', '!=', 'resolved')
                    ->count()
            )
                ->description('Past target date')
                ->color('danger')
                ->icon('heroicon-m-exclamation-triangle'),
        ];
    }
    public function getObservationQueue()
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        return Observation::query()
            ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate));
    }
}
