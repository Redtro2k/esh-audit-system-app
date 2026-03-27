<?php

namespace App\Filament\Resources\Observations\Pages;

use App\Filament\Resources\Observations\ObservationResource;
use App\Mail\ForFutherDiscussion;
use App\Mail\SendObservation;
use App\Models\Observation;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class ViewObservation extends ViewRecord
{
    protected static string $resource = ObservationResource::class;

    public function getSubheading(): ?string
    {
        /** @var Observation $record */
        $record = $this->record;

        $status = ucfirst((string) $record->status);
        $target = $record->target_date
            ? Carbon::parse($record->target_date)->toFormattedDateString()
            : 'No target date';

        return 'Status: ' . $status . ' • Target: ' . $target;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to list')
                ->icon(LucideIcon::ArrowLeft)
                ->url(ObservationResource::getUrl('index')),
            Action::make('nudge')
                ->hidden(fn ($record) => !auth()->user()->hasRole('auditor') || strtolower($record->status) === 'resolved')
                ->icon(LucideIcon::BellRing)
                ->label('Nudge')
                ->requiresConfirmation()
                ->hidden(fn (): bool => !auth()->user()->hasRole('auditor') || strtolower((string) $this->record->status) === 'resolved')
                ->modalHeading('Send reminder?')
                ->modalDescription('This will email the PIC with the observation details.')
                ->action(function (): void {
                    /** @var Observation $observation */
                    $observation = Observation::with('pic', 'auditor', 'pic.department')->findOrFail($this->record->id);

                    switch (strtolower((string) $observation->status)) {
                        case 'for further discussion':
                            Mail::to($observation->pic->email)->send(new ForFutherDiscussion($observation));
                            break;
                        case 'pending':
                            Mail::to($observation->pic->email)->send(new SendObservation($observation));
                            break;
                    }
                }),
            EditAction::make()
                ->label('Update')
                ->icon(LucideIcon::ClipboardPen)
                ->hidden(fn ($record) => !auth()->user()->hasRole('auditor')
                    && (auth()->user()->hasRole('gm')
                        || strtolower((string) $record->status) === 'resolved'
                        || (int) $record->pic_id !== (int) auth()->id())),
//            Action::make('update-status')
//                ->hidden(fn(Observation $observation) => auth()->user()->hasRole('remediator') && $observation->status === 'ongoing')
//                ->modal()
//                ->modalIcon(LucideIcon::Clipboard)
//                ->color(Color::Amber)
//                ->modalIconColor(Color::Stone)
//                ->modalDescription('Select the appropriate status to indicate whether this audit requires further discussion or has been resolved.')
//                ->modalSubmitAction(false)
//                ->extraModalFooterActions([
//                   Action::make('resolved')
//                       ->color('success')
//                       ->icon(LucideIcon::Check)
//                        ->requiresConfirmation()
//                        ->action(fn(Observation $observation) => $observation->update(['status'=> 'resolved'])),
//                   Action::make('for-further-discussion')
//                    ->color('warning')
//                    ->icon(LucideIcon::ClipboardClock)
//                    ->action(fn(Observation $observation) => $observation->update(['status'=> 'for further discussion'])),
//                ])
        ];
    }
}
