<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Observations\ObservationResource;
use App\Filament\Resources\Observations\Pages\ViewObservation;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;


class LatestOngoing extends TableWidget
{
        use InteractsWithPageFilters;

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected ?string $title = 'Latest ongoing audits';
    // protected ?string $description = 'Most recent ongoing audits within the selected date range.';

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth();
        $endDate  = $this->pageFilters['endDate'] ?? now()->endOfMonth();

        return $table
            ->query(fn (): Builder => $this->getWidgetScopedObservationQuery()
                ->whereIn('status', ['pending', 'ongoing', 'for further discussion'])
                ->where(function (Builder $query) use ($startDate, $endDate) {
                    $query
                        ->where('status', 'pending')
                        ->orWhere(function (Builder $q) use ($startDate, $endDate) {
                            $q->whereIn('status', ['ongoing', 'for further discussion'])
                                ->whereBetween('created_at', [$startDate, $endDate]);
                        });
                })
                ->when(auth()->user()->hasRole('remediator'), function (Builder $query) {
                    $query->where('pic_id', auth()->user()->getKey());
                })
                ->when(auth()->user()->hasRole('contributor'), function (Builder $query) {
                    $query->where('auditor_id', auth()->user()->getKey());
                }))
                    ->emptyStateHeading('No ongoing audits')
                    ->emptyStateDescription('Try adjusting the date range or check back later.')
                    ->columns([
                        TextColumn::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ucwords(strtolower($state)))
                            ->icon(fn (string $state) => match (strtolower($state)) {
                                'pending' => LucideIcon::ClipboardClock,
                                'ongoing' => LucideIcon::ClipboardPenLine,
                                'for further discussion' => LucideIcon::MessageSquareMore,
                                'resolved' => LucideIcon::ClipboardCheck,
                                default => LucideIcon::CircleHelp,
                            })
                            ->color(fn (string $state) => match (strtolower($state)) {
                                'pending' => 'gray',
                                'ongoing' => 'warning',
                                'for further discussion' => 'info',
                                'resolved' => 'success',
                                default => 'secondary',
                            })
                            ->sortable(),
                        TextColumn::make('pic.department.name')
                            ->label('Department')
                            ->sortable()
                            ->toggleable(),
                        TextColumn::make('pic.name')
                            ->label('PIC')
                            ->searchable()
                            ->sortable()
                            ->limit(24)
                            ->tooltip(fn (?string $state) => $state)
                            ->toggleable(),
                        TextColumn::make('area')
                            ->label('Audit Area')
                            ->searchable()
                            ->sortable()
                            ->limit(28)
                            ->tooltip(fn (?string $state) => $state)
                            ->toggleable(),
                        TextColumn::make('auditor.name')
                            ->label('Auditor')
                            ->sortable()
                            ->limit(24)
                            ->tooltip(fn (?string $state) => $state)
                            ->toggleable(),
                        ImageColumn::make('capture_concern')->label('Concern Proof')->circular()->stacked()
                            ->imageGallery()
                            ->stacked()
                            ->ring(5)->limit(5)
                            ->toggleable(),
                        TextColumn::make('target_date')
                            ->label('Target Date')
                            ->date('M j, Y')
                            ->color(function ($record) {
                                if (!$record->target_date) {
                                    return 'secondary';
                                }
                                $isOverdue = Carbon::parse($record->target_date)->isPast()
                                    && strtolower((string) $record->status) !== 'resolved';
                                return $isOverdue ? 'danger' : 'secondary';
                            })
                            ->sortable()
                            ->toggleable(),
                    ])
                    ->defaultSort('target_date', 'asc')
                    ->filters([
                        //
                    ])
                    ->headerActions([
                        //
                    ])
                    ->recordUrl(fn ($record) =>
                    ViewObservation::getUrl(['record' => $record->id])
                    )
                    ->toolbarActions([
                        BulkActionGroup::make([
                            //
                        ]),
                    ]);
            }

    protected function getWidgetScopedObservationQuery(): Builder
    {
        $query = ObservationResource::getScopedObservationQuery();
        $user = auth()->user();
        $dealerIds = $this->getVisibleDealerIds();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('developer')) {
            return $query;
        }

        if ($dealerIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('dealer_id', $dealerIds->all());
    }

    protected function getVisibleDealerIds(): Collection
    {
        return auth()->user()?->dealers()->pluck('dealers.id') ?? collect();
    }
}
