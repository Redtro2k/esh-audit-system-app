<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;

class NewDashboard extends BaseDashboard
{

    use HasFiltersAction;

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->schema([
                    DatePicker::make('startDate')
                    ->default(now()->startOfMonth())
                    ->native(false),
                    DatePicker::make('endDate')
                        ->default(now()->endOfMonth())
                    ->native(false),
                ])
        ];
    }
}
