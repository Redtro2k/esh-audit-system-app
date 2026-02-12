<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Observations\Pages\ViewObservation;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
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
            ->query(fn (): Builder => \App\Models\Observation::query()
            ->whereIn('status', ['pending', 'ongoing', 'for further discussion'])
            ->where(function (Builder $query) use ($startDate, $endDate) {
                $query
                    // Always include pending, regardless of date
                    ->where('status', 'pending')

                    // Other statuses must follow the date filter
                    ->orWhere(function (Builder $q) use ($startDate, $endDate) {
                        $q->whereIn('status', ['ongoing', 'for further discussion'])
                        ->whereBetween('created_at', [$startDate, $endDate]);
                    });
            })
            ->with(['pic.department', 'auditor'])
            ->when(auth()->user()->hasRole('remediator'), function (Builder $query) {
                $query->where('pic_id', auth()->id());
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
}
