<?php

namespace App\Filament\Widgets;

use App\Models\Observation;
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
        return [
            Stat::make('Pending', $this->getStatus('pending'))
                ->color('gray')
                ->icon(LucideIcon::ClipboardList)
                ->url($this->getTabUrl('pending')),
            Stat::make('Ongoing', $this->getStatus('ongoing'))
                ->color('warning')
                ->icon(LucideIcon::ClipboardClock)
                ->url($this->getTabUrl('ongoing')),
            Stat::make('For Further Discussion', $this->getStatus('for further discussion'))
                ->color('info')
                ->icon(LucideIcon::ClipboardPenLine)
                ->url($this->getTabUrl('for_further_discussion')),
            Stat::make('Resolved', $this->getStatus('resolved'))
                ->color('success')
                ->icon(LucideIcon::ClipboardCheck)
                ->url($this->getTabUrl('resolved')),

        ];
    }

    public function getStatus($status): int
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth();
        $endDate  = $this->pageFilters['endDate'] ?? now()->endOfMonth();

        $query = Observation::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', $status)
            ->when(auth()->user()->hasRole('remediator'), function (Builder $query) {
                $query->where('pic_id', auth()->id());
            });
        return $query->count();
    }

    protected function getTabUrl(string $tab): string
    {
        return url('/admin/observations?tab=' . $tab);
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
