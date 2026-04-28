<?php

namespace App\Filament\Resources\Observations\Pages;

use App\Filament\Resources\Observations\ObservationResource;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListObservations extends ListRecords
{
    protected static string $resource = ObservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('presentation')
                ->label('Presentation Mode')
                ->icon(LucideIcon::Presentation)
                ->url(ObservationResource::getUrl('presentation'))
                ->hidden(fn (): bool => ! auth()->user()->hasAnyRole(['auditor', 'gm', 'developer'])),

            CreateAction::make()
                ->icon(LucideIcon::Plus)
                ->hidden(auth()->user()->hasAnyRole(['remediator', 'representative', 'gm'])),
        ];
    }
}
