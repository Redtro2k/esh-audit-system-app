<?php

namespace App\Filament\Pages;

use App\Livewire\AnalyticsOverview;
use App\Livewire\PerDepartment;
use App\Livewire\PerStatus;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Page;

class FindingOverview extends Page
{
    use HasFiltersAction;

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->schema([
                    DatePicker::make('startDate')
                    ->native(false),
                    DatePicker::make('endDate')
                    ->native(false),
                    // ...
                ]),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasAnyRole('auditor', 'gm');
    }
    protected static string | BackedEnum | null $navigationIcon = LucideIcon::ChartLine;

    protected static ?string $title = "Analytics";

    protected ?string $heading = "What's happening right now?";

    protected function getHeaderWidgets(): array
    {
        return [
            AnalyticsOverview::class,
            PerDepartment::class,
            PerStatus::class,
        ];
    }
}
