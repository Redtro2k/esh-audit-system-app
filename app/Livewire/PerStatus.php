<?php

namespace App\Livewire;

use App\Models\Observation;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class PerStatus extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Per Status';

    protected ?string $description = 'Distribution of observations across current statuses.';

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        $statusOrder = ['pending', 'ongoing', 'for further discussion', 'resolved'];

        $counts = Observation::query()
            ->when($startDate, fn (Builder $query) => $query->whereDate('observations.created_at', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('observations.created_at', '<=', $endDate))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $labels = [];
        $data = [];

        foreach ($statusOrder as $status) {
            $total = (int) ($counts[$status]->total ?? 0);
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
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Observations',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
