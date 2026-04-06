<?php

namespace App\Filament\Resources\Users;

use App\Enum\NavigationGroup;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Administration;

    protected static ?string $navigationLabel = 'User Management';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isGloballySearchable = false;

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewUsers();
    }

    public static function canAccess(): bool
    {
        return static::canViewUsers();
    }

    public static function canViewAny(): bool
    {
        return static::canViewUsers();
    }

    public static function canCreate(): bool
    {
        return static::canManageUsers();
    }

    public static function canEdit($record): bool
    {
        return static::canManageUsers() || static::canAssignTeams();
    }

    public static function canDelete($record): bool
    {
        return static::canManageUsers() && auth()->id() !== $record->getKey();
    }

    public static function canDeleteAny(): bool
    {
        return static::canManageUsers();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['department', 'dealers', 'team', 'roles'])
            ->when(static::canAssignTeams() && ! static::canManageUsers(), function (Builder $query): void {
                $query->role('contributor');
            });
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function canManageUsers(): bool
    {
        return auth()->user()?->hasRole('developer') ?? false;
    }

    public static function canViewUsers(): bool
    {
        return static::canManageUsers() || static::canAssignTeams();
    }

    public static function canAssignTeams(): bool
    {
        return auth()->user()?->hasRole('auditor') ?? false;
    }
}
