<?php

namespace App\Filament\Resources\ConcernCategories\Pages;

use App\Filament\Resources\ConcernCategories\ConcernCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageConcernCategories extends ManageRecords
{
    protected static string $resource = ConcernCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
