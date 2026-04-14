<?php

namespace App\Filament\Resources\Observations\Tables;

use App\Models\Observation;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

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
                TextColumn::make('dealer.name')
                    ->label('Dealer')
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
                    ->placeholder('No target date')
                    ->color(function ($record) {
                        if (! $record->target_date) {
                            return 'gray';
                        }
                        $isOverdue = Carbon::parse($record->target_date)->isPast()
                            && strtolower((string) $record->status) !== 'resolved';

                        return $isOverdue ? 'danger' : 'secondary';
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('date_captured')
                    ->label('Date Captured')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('No date captured')
                    ->color('secondary')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_pending')
                    ->label('Date Pending')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('No pending date')
                    ->color('secondary')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_ongoing')
                    ->label('Date Ongoing')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('No ongoing date')
                    ->color('secondary')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_for_further_discussion')
                    ->label('Date For Further Discussion')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('No discussion date')
                    ->color('secondary')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_resolved')
                    ->label('Date Resolved')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('No resolved date')
                    ->color('secondary')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Filter::make('pending')
                    ->label('Pending')
                    ->query(fn ($query) => $query->where('status', 'pending')),
                Filter::make('ongoing')
                    ->label('Ongoing')
                    ->query(fn ($query) => $query->where('status', 'ongoing')),
                Filter::make('for_further_discussion')
                    ->label('For Further Discussion')
                    ->query(fn ($query) => $query->where('status', 'for further discussion')),
                Filter::make('resolved')
                    ->label('Resolved')
                    ->query(fn ($query) => $query->where('status', 'resolved')),
                SelectFilter::make('department')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('dealer_id')
                    ->label('Dealer')
                    ->relationship(
                        'dealer',
                        'name',
                        modifyQueryUsing: fn ($query) => $query->visibleTo(auth()->user())->orderBy('name')
                    )
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->label('Created Date')
                    ->form([
                        DatePicker::make('from')
                            ->native(false),
                        DatePicker::make('until')
                            ->native(false),
                    ])
                    ->query(fn ($query, $data) => $query
                        ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (! empty($data['from'])) {
                            $indicators[] = 'From '.Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if (! empty($data['until'])) {
                            $indicators[] = 'Until '.Carbon::parse($data['until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
                Filter::make('overdue')
                    ->label('Overdue')
                    ->query(function ($query) {
                        $query->whereDate('target_date', '<', now())
                            ->where('status', '!=', 'resolved');
                    }),
            ])
            ->recordActions([
                Action::make('Nudge')
                    ->icon(LucideIcon::BellRing)
                    ->label('Nudge')
                    ->iconButton()
                    ->tooltip('Send reminder to PIC')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => ! auth()->user()->hasRole('auditor') || strtolower($record->status) === 'resolved')
                    ->modalHeading('Send reminder?')
                    ->modalDescription('This will email the PIC with the observation details.')
                    ->action(function ($record) {
                        $observation = Observation::with('pic', 'auditor', 'pic.department')->find($record->id);
                        switch (strtolower($record->status)) {
                            case 'for further discussion':
                                Mail::to($observation->pic->email)->send(new \App\Mail\ForFutherDiscussion($observation));
                                break;
                            case 'pending':
                                Mail::to($observation->pic->email)->send(new \App\Mail\SendObservation($observation));
                                break;
                        }
                    }),
                Action::make('addPromiseDate')
                    ->label('Promise Date')
                    ->icon(LucideIcon::ClipboardPen)
                    ->iconButton()
                    ->tooltip('Set promise date')
                    ->hidden(fn ($record) => ! auth()->user()->hasAnyRole(['remediator', 'representative'])
                        || (int) $record->pic_id !== (int) auth()->id()
                        || filled($record->target_date)
                        || strtolower((string) $record->status) === 'resolved')
                    ->modalHeading('Set promise date')
                    ->modalDescription('Add the promised completion date for this observation.')
                    ->form([
                        DateTimePicker::make('target_date')
                            ->label('Promise Date')
                            ->placeholder('Select promise date and time')
                            ->native(false)
                            ->required(),
                    ])
                    ->action(function (array $data, Observation $record): void {
                        $record->update([
                            'target_date' => $data['target_date'],
                        ]);

                        Notification::make()
                            ->title('Promise date saved')
                            ->success()
                            ->send();
                    }),
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View details'),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit observation')
                    ->hidden(fn ($record) => auth()->user()->hasRole('gm')
                        || strtolower((string) $record->status) === 'resolved'
                        || (int) $record->pic_id !== (int) auth()->id()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorize(auth()->user()?->hasRole('developer') ?? false)
                        ->hidden(! auth()->user()->hasRole('developer')),
                    ExportBulkAction::make()
                        ->label('Export')
                        ->icon(LucideIcon::FileSpreadsheet)
                        ->columnMapping(false)
                        ->exporter(\App\Filament\Exports\ObservationExporter::class),
                ]),
            ]);
    }
}
