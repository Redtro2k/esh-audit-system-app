<?php

namespace App\Filament\Resources\Dealers\Pages;

use App\Filament\Resources\Dealers\DealerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDealers extends ManageRecords
{
    protected static string $resource = DealerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
