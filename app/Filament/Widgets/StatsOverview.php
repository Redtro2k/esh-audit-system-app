<?php

namespace App\Filament\Widgets;

use App\Models\Observation;
use App\Support\ObservationAnalyticsCache;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;


class StatsOverview extends StatsOverviewWidget
{

    use InteractsWithPageFilters;

    protected ?string $heading = 'Audit statistics';

    public function persistsFiltersInSession(): bool
    {
        return false;
    }
    protected function getStats(): array
    {
        $counts = $this->getStatusCounts();

        return [
            Stat::make('Pending', $counts['pending'])
                ->color('gray')
                ->icon(LucideIcon::ClipboardList)
                ->url($this->getTabUrl('pending')),
            Stat::make('Ongoing', $counts['ongoing'])
                ->color('warning')
                ->icon(LucideIcon::ClipboardClock)
                ->url($this->getTabUrl('ongoing')),
            Stat::make('For Further Discussion', $counts['for further discussion'])
                ->color('info')
                ->icon(LucideIcon::ClipboardPenLine)
                ->url($this->getTabUrl('for_further_discussion')),
            Stat::make('Resolved', $counts['resolved'])
                ->color('success')
                ->icon(LucideIcon::ClipboardCheck)
                ->url($this->getTabUrl('resolved')),

        ];
    }

    public function getStatusCounts(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth();
        $endDate  = $this->pageFilters['endDate'] ?? now()->endOfMonth();

        return ObservationAnalyticsCache::remember(
            'dashboard-status-counts',
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'userId' => auth()->id(),
                'isRemediator' => auth()->user()?->hasRole('remediator') ?? false,
                'isContributor' => auth()->user()?->hasRole('contributor') ?? false,
            ],
            now()->addMinutes(10),
            function () use ($startDate, $endDate): array {
                $counts = Observation::query()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->when(auth()->user()->hasRole('remediator'), function (Builder $query) {
                        $query->where('pic_id', auth()->id());
                    })
                    ->when(auth()->user()->hasRole('contributor'), function (Builder $query) {
                        $query->where('auditor_id', auth()->id());
                    })
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                return [
                    'pending' => (int) ($counts['pending'] ?? 0),
                    'ongoing' => (int) ($counts['ongoing'] ?? 0),
                    'for further discussion' => (int) ($counts['for further discussion'] ?? 0),
                    'resolved' => (int) ($counts['resolved'] ?? 0),
                ];
            }
        );
    }

    protected function getTabUrl(string $tab): string
    {
        return url('/admin/observations?' . http_build_query([
            'filters' => [
                $tab => [
                    'isActive' => true,
                ],
            ],
        ]));
    }

    protected function getHeading(): string
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth();
        $endDate  = $this->pageFilters['endDate'] ?? now()->endOfMonth();

        return 'Audit statistics (' . Carbon::parse($startDate)->toFormattedDateString()
            . ' – ' . Carbon::parse($endDate)->toFormattedDateString() . ')';
    }

    protected function getDescription(): ?string
    {
        return 'Snapshot of audit performance by status.';
    }
}
