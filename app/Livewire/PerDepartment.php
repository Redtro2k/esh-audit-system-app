<?php

namespace App\Livewire;

use App\Support\AnalyticsObservationScope;
use App\Support\ObservationAnalyticsCache;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PerDepartment extends ApexChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $chartId = 'perDepartmentChart';

    protected static ?string $heading = 'Findings by Department';

    protected static ?string $subheading = 'Distribution of observations assigned to each department.';

    protected function getOptions(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;
        $dealerIds = AnalyticsObservationScope::visibleDealerIds();

        $counts = ObservationAnalyticsCache::remember(
            'findings-per-department',
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'userId' => auth()->id(),
                'dealerIds' => $dealerIds->all(),
            ],
            now()->addMinutes(10),
            fn () => AnalyticsObservationScope::query()
                ->selectRaw('departments.name as department, count(*) as total')
                ->when($startDate, fn (Builder $query) => $query->whereDate('observations.created_at', '>=', $startDate))
                ->when($endDate, fn (Builder $query) => $query->whereDate('observations.created_at', '<=', $endDate))
                ->join('users', 'observations.pic_id', '=', 'users.id')
                ->join('departments', 'users.department_id', '=', 'departments.id')
                ->groupBy('departments.name')
                ->orderBy('departments.name')
                ->get()
                ->map(fn ($row) => [
                    'department' => $row->department,
                    'total' => (int) $row->total,
                ])
                ->all()
        );

        $labels = array_map(
            fn (array $row) => sprintf('%s (%d)', $row['department'], $row['total']),
            $counts
        );
        $data = array_map(fn (array $row) => $row['total'], $counts);

        $palette = [
            '#2563EB', '#F59E0B', '#10B981', '#EF4444', '#8B5CF6', '#14B8A6',
            '#0EA5E9', '#22C55E', '#EAB308', '#F97316', '#EC4899', '#6366F1',
            '#84CC16', '#06B6D4', '#A855F7', '#F43F5E',
        ];
        $colors = array_map(
            fn (int $i) => $palette[$i % count($palette)],
            array_keys($labels)
        );

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
