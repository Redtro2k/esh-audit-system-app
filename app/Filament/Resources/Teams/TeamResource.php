<?php

namespace App\Filament\Resources\Teams;

use App\Enum\NavigationGroup;
use App\Filament\Resources\Teams\Pages\ManageTeams;
use App\Models\Dealer;
use App\Models\Team;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Administration;

    protected static ?string $navigationLabel = 'Teams';

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isGloballySearchable = false;

    public static function shouldRegisterNavigation(): bool
    {
        return static::canManageTeams();
    }

    public static function canAccess(): bool
    {
        return static::canManageTeams();
    }

    public static function canViewAny(): bool
    {
        return static::canManageTeams();
    }

    public static function canCreate(): bool
    {
        return static::canManageTeams();
    }

    public static function canEdit($record): bool
    {
        return static::canManageTeams();
    }

    public static function canDelete($record): bool
    {
        return static::canManageTeams();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['dealer', 'users']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Team Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Enter team name')
                    ->helperText('Create a team that contributor users can join.'),
                Select::make('dealer_id')
                    ->label('Assigned Dealer')
                    ->relationship(
                        'dealer',
                        'name',
                        modifyQueryUsing: fn ($query) => $query
                            ->visibleTo(auth()->user())
                            ->orderBy('name')
                    )
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required()
                    ->placeholder('Select a dealer')
                    ->helperText('Assign this team to the dealer it belongs to.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('dealer.name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dealer.name')
                    ->label('Dealer')
                    ->placeholder('No dealer')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('members')
                    ->label('Members')
                    ->circular()
                    ->stacked()
                    ->ring(5)
                    ->limit(5)
                    ->getStateUsing(fn (Team $record): array => $record->users
                        ->map(fn (User $user): ?string => $user->getFilamentAvatarUrl())
                        ->filter()
                        ->values()
                        ->all()),
                TextColumn::make('users_count')
                    ->label('Total Members')
                    ->counts('users')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTeams::route('/'),
        ];
    }

    protected static function canManageTeams(): bool
    {
        return auth()->user()?->hasRole('developer') ?? false;
    }
}
