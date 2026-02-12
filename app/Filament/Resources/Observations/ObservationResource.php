<?php

namespace App\Filament\Resources\Observations;

use App\Filament\Resources\Observations\Pages\CreateObservation;
use App\Filament\Resources\Observations\Pages\EditObservation;
use App\Filament\Resources\Observations\Pages\ListObservations;
use App\Filament\Resources\Observations\Pages\ViewObservation;
use App\Filament\Resources\Observations\Schemas\ObservationForm;
use App\Filament\Resources\Observations\Schemas\ObservationInfolist;
use App\Filament\Resources\Observations\Tables\ObservationsTable;
use App\Models\Observation;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Enum\NavigationGroup;
use UnitEnum;

class ObservationResource extends Resource
{

    protected static ?string $model = Observation::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::AuditManagement;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Clipboard;

    protected static ?string $navigationLabel = 'Observations';

    protected static ?string $recordTitleAttribute = 'area';

    protected static ?string $pluralLabel = 'Observations';

    public static function getNavigationBadge(): ?string
    {
        if(auth()->user()->hasRole('remediator')) {
            return static::getModel()::where('status', 'ongoing')->where('pic_id', auth()->id())->count() > 0 ? (string)static::getModel()::where('status', 'pending')->where('pic_id', auth()->id())->count() . ' Pending': null;
        }
        return static::getModel()::where('status', 'ongoing')->count() > 0 ? (string)static::getModel()::where('status', 'pending')->count() . ' Pending': null;
    }
    public static function getEloquentQuery(): Builder
    {
         return parent::getEloquentQuery()->when(auth()->user()->hasRole('remediator'), function (Builder $query) {
             $query->where('pic_id', auth()->id());
         });
    }

    public static function form(Schema $schema): Schema
    {
        return ObservationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ObservationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ObservationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListObservations::route('/'),
            'create' => CreateObservation::route('/create'),
            'view' => ViewObservation::route('/{record}'),
            'edit' => EditObservation::route('/{record}/edit'),
        ];
    }
}
