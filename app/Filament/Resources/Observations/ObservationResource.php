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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Enum\NavigationGroup;
use Kirschbaum\Commentions\CommentSubscription;
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
        $query = static::getScopedObservationQuery();

        if (auth()->user()->hasRole('remediator')) {
            $query->where('pic_id', auth()->id());
        } elseif (auth()->user()->hasRole('contributor')) {
            $query->where('auditor_id', auth()->user()->getKey());
        }

        return $query->where('status', 'pending')->count() > 0
            ? (string) $query->where('status', 'pending')->count() . ' Pending'
            : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getScopedObservationQuery()
            ->when(auth()->user()->hasRole('remediator'), function (Builder $query) {
                $user = auth()->user();
                $subscriptionsTable = (new CommentSubscription())->getTable();

                $query->where(function (Builder $scoped) use ($user, $subscriptionsTable) {
                    $scoped
                        ->where('pic_id', $user->getKey())
                        ->orWhereExists(function ($subQuery) use ($user, $subscriptionsTable) {
                            $subQuery
                                ->selectRaw('1')
                                ->from($subscriptionsTable)
                                ->whereColumn("{$subscriptionsTable}.subscribable_id", 'observations.id')
                                ->where("{$subscriptionsTable}.subscribable_type", Observation::class)
                                ->where("{$subscriptionsTable}.subscriber_id", $user->getKey())
                                ->where("{$subscriptionsTable}.subscriber_type", $user->getMorphClass());
                        });
                });
            })
            ->when(auth()->user()->hasRole('contributor'), function (Builder $query) {
                $query->where('auditor_id', auth()->user()->getKey());
            });
    }

    public static function getScopedObservationQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['dealer', 'pic.department', 'pic', 'auditor']);
        $user = auth()->user();

        if (! $user || $user->hasAnyRole(['developer', 'remediator', 'gm'])) {
            return $query;
        }

        $dealerIds = $user->dealers()->pluck('dealers.id');

        if ($dealerIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('dealer_id', $dealerIds);
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
