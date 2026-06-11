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
use Illuminate\Database\Eloquent\Model;
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

    public static function canEdit($record): bool
    {
        return static::canUpdateObservation($record);
    }

    public static function canUpdateObservation(?Observation $record): bool
    {
        $user = auth()->user();

        if (! $user || ! $record) {
            return false;
        }

        if ($user->hasRole('gm') || strtolower((string) $record->status) === 'resolved') {
            return false;
        }

        return static::canManageAuditFields($record, $user)
            || static::canRespondToObservation($record, $user);
    }

    public static function canManageAuditFields(?Observation $record = null, ?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('developer') && ! $record) {
            return true;
        }

        if ($user->hasRole('auditor')) {
            return true;
        }

        if (! $user->hasRole('contributor')) {
            return false;
        }

        return ! $record || (int) $record->auditor_id === (int) $user->getKey();
    }

    public static function canRespondToObservation(?Observation $record, ?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user || ! $record) {
            return false;
        }

        if ($user->hasRole('representative') && (int) $record->pic_id === (int) $user->getKey()) {
            return true;
        }

        if (! $user->hasRole('remediator')) {
            return false;
        }

        return (int) $record->pic_id === (int) $user->getKey()
            || (
                (int) $record->pic?->department_id === (int) $user->department_id
                && $record->pic?->hasRole('representative')
                && $user->dealers()->whereKey($record->dealer_id)->exists()
            );
    }

    public static function isMentionOnlyObservation(Observation $record, ?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user || ! static::isUserMentionedInObservation($record, $user)) {
            return false;
        }

        return ! static::canViewObservationWithoutMention($record, $user);
    }

    public static function getNavigationBadge(): ?string
    {
        $query = static::getBaseObservationQuery();
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        static::applyObservationVisibility($query, $user, includeSubscriptions: true);

        $pendingCount = $query->where('status', 'pending')->count();

        return $pendingCount > 0
            ? (string) $pendingCount.' Pending'
            : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return static::applyObservationVisibility(
            static::getBaseObservationQuery(),
            auth()->user(),
            includeSubscriptions: true,
        );
    }

    public static function getScopedObservationQuery(): Builder
    {
        $query = static::getBaseObservationQuery();
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

    protected static function getBaseObservationQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['dealer', 'pic.department', 'pic', 'auditor']);
    }

    public static function applyObservationVisibility(Builder $query, ?User $user = null, bool $includeSubscriptions = false): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasAnyRole(['developer', 'gm'])) {
            return $query;
        }

        $hasVisibilityRule = false;

        $query->where(function (Builder $visibility) use ($user, $includeSubscriptions, &$hasVisibilityRule) {
            if ($user->hasRole('auditor')) {
                $hasVisibilityRule = true;
                $visibility->orWhere(fn (Builder $auditorQuery) => static::applyDealerVisibility(
                    $auditorQuery,
                    $user,
                ));
            }

            if ($user->hasRole('contributor')) {
                $hasVisibilityRule = true;
                $visibility->orWhere(fn (Builder $contributorQuery) => static::applyDealerVisibility(
                    $contributorQuery,
                    $user,
                )->where('auditor_id', $user->getKey()));
            }

            if ($user->hasRole('representative')) {
                $hasVisibilityRule = true;
                $visibility->orWhere(fn (Builder $representativeQuery) => static::applyDealerVisibility(
                    $representativeQuery,
                    $user,
                )->where('pic_id', $user->getKey()));
            }

            if ($user->hasRole('remediator')) {
                $hasVisibilityRule = true;
                $visibility->orWhere(fn (Builder $remediatorQuery) => static::applyRemediatorVisibility(
                    $remediatorQuery,
                    $user,
                ));
            }

            if ($includeSubscriptions) {
                $hasVisibilityRule = true;
                $visibility->orWhere(fn (Builder $subscriptionQuery) => static::applySubscriptionVisibility(
                    $subscriptionQuery,
                    $user,
                ));
            }
        });

        return $hasVisibilityRule
            ? $query
            : $query->whereRaw('1 = 0');
    }

    protected static function applyDealerVisibility(Builder $query, User $user): Builder
    {
        $dealerIds = $user->dealers()->pluck('dealers.id');

        if ($dealerIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('dealer_id', $dealerIds);
    }

    protected static function canViewObservationWithoutMention(Observation $record, User $user): bool
    {
        if ($user->hasAnyRole(['developer', 'gm'])) {
            return true;
        }

        if ($user->hasRole('auditor') && static::canAccessObservationDealer($record, $user)) {
            return true;
        }

        if (
            $user->hasRole('contributor')
            && static::canAccessObservationDealer($record, $user)
            && (int) $record->auditor_id === (int) $user->getKey()
        ) {
            return true;
        }

        if (
            $user->hasRole('representative')
            && static::canAccessObservationDealer($record, $user)
            && (int) $record->pic_id === (int) $user->getKey()
        ) {
            return true;
        }

        return $user->hasRole('remediator')
            && static::canRespondToObservation($record, $user);
    }

    protected static function canAccessObservationDealer(Observation $record, User $user): bool
    {
        return $user->dealers()->whereKey($record->dealer_id)->exists();
    }

    protected static function isUserMentionedInObservation(Observation $record, User $user): bool
    {
        return $record->comments()
            ->where('body', 'like', '%data-type="mention"%')
            ->where('body', 'like', '%data-id="'.$user->getKey().'"%')
            ->exists();
    }

    protected static function applyRemediatorVisibility(Builder $query, User $user): Builder
    {
        $dealerIds = $user->dealers()->pluck('dealers.id');

        if ($dealerIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->whereIn('dealer_id', $dealerIds)
            ->where(function (Builder $scoped) use ($user) {
                $scoped
                    ->where('pic_id', $user->getKey())
                    ->orWhereHas('pic', fn (Builder $picQuery) => $picQuery
                        ->where('department_id', $user->department_id)
                        ->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', 'representative')));
            });
    }

    protected static function applySubscriptionVisibility(Builder $query, User $user): Builder
    {
        $subscriptionsTable = (new CommentSubscription)->getTable();
        $observationMorphClass = (new Observation)->getMorphClass();

        return $query->whereExists(function ($subQuery) use ($user, $subscriptionsTable, $observationMorphClass) {
            $subQuery
                ->selectRaw('1')
                ->from($subscriptionsTable)
                ->whereColumn("{$subscriptionsTable}.subscribable_id", 'observations.id')
                ->where("{$subscriptionsTable}.subscribable_type", $observationMorphClass)
                ->where("{$subscriptionsTable}.subscriber_id", $user->getKey())
                ->where("{$subscriptionsTable}.subscriber_type", $user->getMorphClass());
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

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'area',
            'concern',
            'status',
            'dealer.name',
            'pic.name',
            'auditor.name',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Status' => ucwords(strtolower((string) $record->status)),
            'Dealer' => $record->dealer?->name ?? 'No dealer',
            'PIC' => $record->pic?->name ?? 'No PIC',
            'Auditor' => $record->auditor?->name ?? 'No auditor',
            'Target' => $record->target_date?->format('M j, Y g:i A') ?? 'No target date',
            'Concern' => static::formatGlobalSearchText($record->concern ?: 'No concern details', 180),
        ];
    }

    public static function modifyGlobalSearchQuery(Builder $query, string $search): void
    {
        $query->with(['dealer', 'pic', 'auditor']);
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl('view', ['record' => $record]);
    }

    protected static function formatGlobalSearchText(?string $text, int $limit = 140): string
    {
        $text = (string) $text;
        $text = str_replace(['\\r\\n', '\\n', '\\r'], ' ', $text);
        $text = preg_replace('/\s*[*#>`_-]+\s*/', ' ', $text) ?: $text;

        return str($text)->squish()->limit($limit)->toString();
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
