<?php

namespace App\Filament\Resources\Observations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\Observation;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class ObservationsTable
{

    public static function configure(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('pic.name')
                    ->label('PIC'),
            ])
            ->deferLoading()
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
//                SelectColumn::make('status')
//                    ->hidden(!auth()->user()->hasRole('auditor'))
//                    ->label('Status')
//                    ->options([
//                        'pending' => 'Pending',
//                        'ongoing' => 'Ongoing',
//                        'for further discussion' => 'For Further Discussion',
//                        'resolved' => 'Resolved',
//                    ])
//                ->native(false)
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'ongoing' => 'Ongoing',
                        'for further discussion' => 'For Further Discussion',
                        'resolved' => 'Resolved',
                    ]),
                Filter::make('created_at')
                    ->label('Created Date')
                    ->form([
                        DatePicker::make('from')
                        ->native(false),
                        DatePicker::make('until')
                        ->native(false),
                    ])
                    ->query(fn ($query, $data) =>
                    $query
                        ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (!empty($data['from'])) {
                            $indicators[] = 'From ' . Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if (!empty($data['until'])) {
                            $indicators[] = 'Until ' . Carbon::parse($data['until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
                Filter::make('overdue')
                    ->label('Overdue')
                    ->query(function ($query) {
                        $query->whereDate('target_date', '<', now())
                            ->where('status', '!=', 'resolved');
                    })
            ])
            ->recordActions([
                Action::make('Nudge')
                    ->icon(LucideIcon::BellRing)
                    ->label('Nudge')
                    ->iconButton()
                    ->tooltip('Send reminder to PIC')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => strtolower($record->status) === 'resolved')
                    ->modalHeading('Send reminder?')
                    ->modalDescription('This will email the PIC with the observation details.')
                    ->action(function ($record){
                           $observation = Observation::with('pic', 'auditor', 'pic.department')->find($record->id);
                           switch (strtolower($record->status)) {
                               case 'for further discussion':
                                    Mail::to($observation->pic->email)->queue(new \App\Mail\ForFutherDiscussion($observation));
                                    break;
                               case 'pending':
                                    Mail::to($observation->pic->email)->queue(new \App\Mail\SendObservation($observation));
                                    break;
                           }
                    }),
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View details'),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit observation')
                    ->hidden(fn($record) => auth()->user()->hasRole('gm') || strtolower($record->status) === 'resolved'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
