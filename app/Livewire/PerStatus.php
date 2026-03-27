<?php

namespace App\Livewire;

use App\Models\Observation;
use App\Support\ObservationAnalyticsCache;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;


class PerStatus extends ApexChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $chartId = 'perStatusChart';

    protected static ?string $heading = 'Per Status';

    protected static ?string $subheading = 'Distribution of observations across current statuses.';

    protected function getOptions(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        $statusOrder = ['pending', 'ongoing', 'for further discussion', 'resolved'];

        $counts = ObservationAnalyticsCache::remember(
            'findings-per-status',
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ],
            now()->addMinutes(10),
            fn () => Observation::query()
                ->when($startDate, fn (Builder $query) => $query->whereDate('observations.created_at', '>=', $startDate))
                ->when($endDate, fn (Builder $query) => $query->whereDate('observations.created_at', '<=', $endDate))
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->map(fn ($total) => (int) $total)
                ->all()
        );

        $labels = [];
        $data = [];

        foreach ($statusOrder as $status) {
            $total = (int) ($counts[$status] ?? 0);
            $labels[] = sprintf('%s (%d)', ucwords($status), $total);
            $data[] = $total;
        }

        $colors = [
            '#F59E0B', // pending
            '#3B82F6', // ongoing
            '#8B5CF6', // for further discussion
            '#10B981', // resolved
        ];

        return [
            'chart' => [
                'type' => 'pie',
                'height' => 320,
            ],
            'series' => $data,
            'labels' => $labels,
            'colors' => $colors,
            'legend' => [
                'position' => 'bottom',
                'fontSize' => '13px',
            ],
            'stroke' => [
                'width' => 1,
                'colors' => ['#ffffff'],
            ],
            'dataLabels' => [
                'enabled' => true,
            ],
            'noData' => [
                'text' => 'No observations found for the selected period.',
            ],
        ];
    }
}
