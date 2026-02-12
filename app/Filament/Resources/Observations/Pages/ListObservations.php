<?php

namespace App\Filament\Resources\Observations\Pages;

use App\Filament\Resources\Observations\ObservationResource;
use App\Models\Observation;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListObservations extends ListRecords
{
    protected static string $resource = ObservationResource::class;
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(LucideIcon::Plus)
                ->hidden(auth()->user()->hasAnyRole(['remediator', 'gm'])),
        ];
    }

    public function getTabs(): array
    {
        $baseQuery = Observation::query()
            ->when(
                auth()->user()->hasRole('remediator'),
                fn (Builder $query) => $query->where('pic_id', auth()->id())
            );

        return[
            'all' => Tab::make('All Observations')
                ->icon(LucideIcon::ClipboardList)
                ->badge((string) (clone $baseQuery)->count()),
            'pending' => Tab::make('Pending')
                ->icon(LucideIcon::ClipboardClock)
                ->badge((string) (clone $baseQuery)->where('status', 'pending')->count())
                ->modifyQueryUsing(fn($query) => $query->where('status', 'pending')),
            'ongoing' => Tab::make('Ongoing')
                ->icon(LucideIcon::ClipboardPenLine)
                ->badge((string) (clone $baseQuery)->where('status', 'ongoing')->count())
                ->modifyQueryUsing(fn($query) => $query->where('status', 'ongoing')),
            'for_further_discussion' => Tab::make('For Further Discussion')
                ->icon(LucideIcon::BellRing)
                ->badge((string) (clone $baseQuery)->where('status', 'for further discussion')->count())
                ->modifyQueryUsing(fn($query) => $query->where('status', 'for further discussion')),
            'resolved' => Tab::make('Resolved')
                ->icon(LucideIcon::ClipboardCheck)
                ->badge((string) (clone $baseQuery)->where('status', 'resolved')->count())
                ->modifyQueryUsing(fn($query) => $query->where('status', 'resolved')),
        ];
    }
}

