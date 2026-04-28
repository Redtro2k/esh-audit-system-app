<?php

namespace App\Filament\Resources\Observations;

use App\Enum\NavigationGroup;
use App\Filament\Resources\Observations\Pages\CreateObservation;
use App\Filament\Resources\Observations\Pages\EditObservation;
use App\Filament\Resources\Observations\Pages\ListObservations;
use App\Filament\Resources\Observations\Pages\PresentObservations;
use App\Filament\Resources\Observations\Pages\ViewObservation;
use App\Filament\Resources\Observations\Schemas\ObservationForm;
use App\Filament\Resources\Observations\Schemas\ObservationInfolist;
use App\Filament\Resources\Observations\Tables\ObservationsTable;
use App\Models\Observation;
use App\Models\User;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('developer') ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $query = static::getScopedObservationQuery();
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        static::applyObservationVisibility($query, $user);

        $pendingCount = $query->where('status', 'pending')->count();

        return $pendingCount > 0
            ? (string) $pendingCount.' Pending'
            : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return static::applyObservationVisibility(
            static::getScopedObservationQuery(),
            auth()->user(),
            includeSubscriptions: true,
        );
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

    public static function applyObservationVisibility(Builder $query, ?User $user = null, bool $includeSubscriptions = false): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('remediator')) {
            return static::applyRemediatorVisibility($query, $user, $includeSubscriptions);
        }

        if ($user->hasRole('representative')) {
            return $query->where('pic_id', $user->getKey());
        }

        if ($user->hasRole('contributor')) {
            return $query->where('auditor_id', $user->getKey());
        }

        return $query;
    }

    protected static function applyRemediatorVisibility(Builder $query, User $user, bool $includeSubscriptions = false): Builder
    {
        $subscriptionsTable = (new CommentSubscription)->getTable();

        return $query->where(function (Builder $scoped) use ($user, $includeSubscriptions, $subscriptionsTable) {
            $scoped
                ->where('pic_id', $user->getKey())
                ->orWhereHas('pic', fn (Builder $picQuery) => $picQuery
                    ->where('department_id', $user->department_id)
                    ->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', 'representative')));

            if (! $includeSubscriptions) {
                return;
            }

            $scoped->orWhereExists(function ($subQuery) use ($user, $subscriptionsTable) {
                $subQuery
                    ->selectRaw('1')
                    ->from($subscriptionsTable)
                    ->whereColumn("{$subscriptionsTable}.subscribable_id", 'observations.id')
                    ->where("{$subscriptionsTable}.subscribable_type", Observation::class)
                    ->where("{$subscriptionsTable}.subscriber_id", $user->getKey())
                    ->where("{$subscriptionsTable}.subscriber_type", $user->getMorphClass());
            });
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
            'presentation' => PresentObservations::route('/presentation'),
            'view' => ViewObservation::route('/{record}'),
            'edit' => EditObservation::route('/{record}/edit'),
        ];
    }
}
