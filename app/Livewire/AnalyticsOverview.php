<?php

namespace App\Livewire;

use App\Models\Observation;
use App\Support\ObservationAnalyticsCache;
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
        $counts = $this->getObservationCounts();

        return [
            Stat::make(
                'Open Findings',
               $counts['open']
            )
                ->description('All unresolved items')
                ->color('primary')
                ->icon('heroicon-m-flag'),
            Stat::make(
                'Pending Review',
               $counts['pending']
            )
                ->description('Awaiting triage or action')
                ->color('warning')
                ->icon('heroicon-m-clock'),
            Stat::make(
                'Resolved',
                $counts['resolved']
            )
                ->description('Closed and verified')
                ->color('success')
                ->icon('heroicon-m-check-circle'),
            Stat::make(
                'Overdue Targets',
                $counts['overdue']
            )
                ->description('Past target date')
                ->color('danger')
                ->icon('heroicon-m-exclamation-triangle'),
        ];
    }

    public function getObservationCounts(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        return ObservationAnalyticsCache::remember(
            'finding-overview-counts',
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'today' => now()->toDateString(),
            ],
            now()->addMinutes(10),
            function (): array {
                $counts = $this->getObservationQueue()
                    ->selectRaw("
                        SUM(CASE WHEN status != 'resolved' THEN 1 ELSE 0 END) as open_count,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count,
                        SUM(CASE WHEN target_date IS NOT NULL AND DATE(target_date) < ? AND status != 'resolved' THEN 1 ELSE 0 END) as overdue_count
                    ", [now()->toDateString()])
                    ->first();

                return [
                    'open' => (int) ($counts->open_count ?? 0),
                    'pending' => (int) ($counts->pending_count ?? 0),
                    'resolved' => (int) ($counts->resolved_count ?? 0),
                    'overdue' => (int) ($counts->overdue_count ?? 0),
                ];
            }
        );
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
