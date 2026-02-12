<?php

namespace App\Livewire;

use App\Models\Observation;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class PerDepartment extends ChartWidget
{
    use InteractsWithPageFilters;


    protected ?string $heading = 'Findings by Department';

    protected ?string $description = 'Distribution of observations assigned to each department.';

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        $counts = Observation::query()
            ->selectRaw('departments.name as department, count(*) as total')
            ->when($startDate, fn (Builder $query) => $query->whereDate('observations.created_at', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('observations.created_at', '<=', $endDate))
            ->join('users', 'observations.pic_id', '=', 'users.id')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->groupBy('departments.name')
            ->orderBy('departments.name')
            ->get();

        $labels = $counts->map(
            fn ($row) => sprintf('%s (%d)', $row->department, (int) $row->total)
        )->all();
        $data = $counts->pluck('total')->all();

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
