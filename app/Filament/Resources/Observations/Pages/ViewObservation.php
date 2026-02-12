<?php

namespace App\Filament\Resources\Observations\Pages;

use App\Filament\Resources\Observations\ObservationResource;
use App\Models\Observation;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;

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
            EditAction::make()
                ->label('Update')
                ->icon(LucideIcon::ClipboardPen),
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
