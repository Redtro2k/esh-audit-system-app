<?php

namespace App\Filament\Widgets;

use App\Models\Observation;
use App\Support\AnalyticsObservationScope;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOngoing extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 2;

    protected ?string $title = 'Latest ongoing audits';

    protected ?string $description = 'Timeline feed for active audit observations.';

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->pageFilters['endDate'] ?? now()->endOfMonth();
        $dealerId = $this->pageFilters['dealerId'] ?? null;

        return $table
            ->query(fn (): Builder => AnalyticsObservationScope::query($dealerId)
                ->with(['dealer', 'pic.department', 'auditor'])
                ->whereIn('status', ['pending', 'ongoing', 'for further discussion'])
                ->where(function (Builder $query) use ($startDate, $endDate) {
                    $query
                        ->where('status', 'pending')
                        ->orWhere(function (Builder $q) use ($startDate, $endDate) {
                            $q->whereIn('status', ['ongoing', 'for further discussion'])
                                ->whereBetween('created_at', [$startDate, $endDate]);
                        });
                }))
            ->defaultSort('created_at', 'desc')
            ->defaultGroup(
                Group::make('created_at')
                    ->date()
                    ->orderQueryUsing(fn (Builder $query) => $query->orderByDesc('created_at'))
            )
            ->emptyStateHeading('No ongoing audits')
            ->emptyStateDescription('Try adjusting the date range or check back later.')
            ->columns([
                Split::make([
                    ImageColumn::make('capture_concern')
                        ->label('')
                        ->square()
                        ->size(84)
                        ->defaultImageUrl(asset('favicon.svg'))
                        ->extraImgAttributes(['class' => 'object-cover rounded-lg border border-gray-200 dark:border-gray-700'])
                        ->grow(false),
                    Stack::make([
                        TextColumn::make('area')
                            ->label('')
                            ->weight(FontWeight::Bold)
                            ->placeholder('Untitled audit area')
                            ->wrap(),
                        TextColumn::make('status')
                            ->label('')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ucwords(strtolower($state)))
                            ->color(fn (string $state) => match (strtolower($state)) {
                                'pending' => 'gray',
                                'ongoing' => 'warning',
                                'for further discussion' => 'info',
                                'resolved' => 'success',
                                default => 'secondary',
                            }),
                        TextColumn::make('timeline_meta')
                            ->label('')
                            ->state(fn (Observation $record): string => sprintf(
                                '%s / %s',
                                $record->dealer?->name ?? 'No dealer',
                                $record->pic?->department?->name ?? 'No department'
                            ))
                            ->color('gray'),
                        TextColumn::make('timeline_lead')
                            ->label('')
                            ->state(fn (Observation $record): string => 'Lead Time: '.($this->resolveLeadTimeByStatus($record) ?? 'No lead time'))
                            ->color('gray'),
                        TextColumn::make('auditor.name')
                            ->label('')
                            ->prefix('Auditor: ')
                            ->color('gray')
                            ->placeholder('No auditor'),
                    ]),
                ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->tooltip('View details'),
            ])
            ->paginated([5])
            ->asDoubleSidedTimeline();
    }

    protected function resolveLeadTimeByStatus(Observation $record): ?string
    {
        $attribute = match (strtolower((string) $record->status)) {
            'pending' => 'date_pending',
            'ongoing' => 'date_ongoing',
            'for further discussion' => 'date_for_further_discussion',
            'resolved' => 'date_resolved',
            default => null,
        };

        if (! $attribute) {
            return null;
        }

        return $record->formatLeadTime($attribute);
    }
}
