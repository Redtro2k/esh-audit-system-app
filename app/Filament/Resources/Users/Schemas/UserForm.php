<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\UserResource;
use App\Models\Department;
use App\Models\Team;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Details')
                    ->columnSpanFull()
                    ->description('Manage account identity, department assignment, and role access.')
                    ->schema([
                        Grid::make(2)
                            ->columnSpanFull()
                            ->schema([
                                FileUpload::make('avatar_url')
                                    ->label('Profile Picture')
                                    ->disabled(fn (): bool => UserResource::canAssignTeams() && ! UserResource::canManageUsers())
                                    ->disk('public')
                                    ->directory('avatars')
                                    ->image()
                                    ->avatar()
                                    ->imageEditor()
                                    ->nullable()
                                    ->helperText('Upload an optional profile picture for this user.'),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->disabled(fn (): bool => UserResource::canAssignTeams() && ! UserResource::canManageUsers())
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Enter full name')
                                            ->helperText('This name will be shown throughout the app.'),
                                        TextInput::make('username')
                                            ->disabled(fn (): bool => UserResource::canAssignTeams() && ! UserResource::canManageUsers())
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('Enter username')
                                            ->helperText('Use a unique username for login and identification.'),
                                        TextInput::make('email')
                                            ->disabled(fn (): bool => UserResource::canAssignTeams() && ! UserResource::canManageUsers())
                                            ->email()
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('name@example.com')
                                            ->helperText('Use the user\'s active company email address.'),
                                        Toggle::make('is_email_verified')
                                            ->label('Email Verified')
                                            ->disabled(fn (): bool => UserResource::canAssignTeams() && ! UserResource::canManageUsers())
                                            ->helperText('Enable this to mark the user email as verified.')
                                            ->default(false)
                                            ->dehydrated(false)
                                            ->afterStateHydrated(function (Toggle $component, $state, $record): void {
                                                $component->state(filled($record?->email_verified_at));
                                            }),
                                        Select::make('department_id')
                                            ->label('Department')
                                            ->disabled(fn (): bool => UserResource::canAssignTeams() && ! UserResource::canManageUsers())
                                            ->options(Department::query()->orderBy('name')->pluck('name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->placeholder('Select a department')
                                            ->helperText('Assign the user to a department if applicable.')
                                            ->nullable(),
                                        Select::make('dealers')
                                            ->label('Dealers')
                                            ->relationship(
                                                'dealers',
                                                'name',
                                                modifyQueryUsing: fn ($query) => $query->visibleTo(auth()->user())->orderBy('name')
                                            )
                                            ->disabled(fn (): bool => UserResource::canAssignTeams() && ! UserResource::canManageUsers())
                                            ->multiple()
                                            ->maxItems(fn (Get $get, ?User $record): ?int => self::shouldLimitDealersToOne($get('roles'), $record) ? 1 : null)
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->placeholder(fn (Get $get, ?User $record): string => self::shouldLimitDealersToOne($get('roles'), $record)
                                                ? 'Select one dealer'
                                                : 'Select one or more dealers')
                                            ->helperText(fn (Get $get, ?User $record): string => self::shouldLimitDealersToOne($get('roles'), $record)
                                                ? 'Contributors can only be assigned to one dealer.'
                                                : 'Assign one or more dealers based on access coverage.')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                                $selectedDealerIds = collect(is_array($state) ? $state : [$state])
                                                    ->filter(fn ($dealerId) => filled($dealerId))
                                                    ->map(fn ($dealerId) => (int) $dealerId)
                                                    ->values();

                                                if ($selectedDealerIds->isEmpty()) {
                                                    $set('team_id', null);

                                                    return;
                                                }

                                                $selectedTeamId = $get('team_id');

                                                if (blank($selectedTeamId)) {
                                                    return;
                                                }

                                                $teamMatchesDealer = Team::query()
                                                    ->whereKey($selectedTeamId)
                                                    ->whereIn('dealer_id', $selectedDealerIds->all())
                                                    ->exists();

                                                if (! $teamMatchesDealer) {
                                                    $set('team_id', null);
                                                }
                                            })
                                            ->nullable(),
                                        Select::make('roles')
                                            ->relationship('roles', 'name')
                                            ->disabled(fn (): bool => UserResource::canAssignTeams() && ! UserResource::canManageUsers())
                                            ->multiple()
                                            ->preload()
                                            ->searchable()
                                            ->native(false)
                                            ->options(Role::query()->orderBy('name')->pluck('name', 'id'))
                                            ->placeholder('Select one or more roles')
                                            ->helperText('Choose the access role(s) this user should have.')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, Get $get, ?array $state): void {
                                                if (! self::rolesContainContributor($state)) {
                                                    $set('team_id', null);
                                                    return;
                                                }

                                                $selectedDealers = collect($get('dealers'))
                                                    ->filter(fn ($dealer) => filled($dealer))
                                                    ->values()
                                                    ->all();

                                                if (count($selectedDealers) > 1) {
                                                    $set('dealers', [reset($selectedDealers)]);
                                                }
                                            })
                                            ->required(),
                                        Select::make('team_id')
                                            ->label('Team')
                                            ->options(function (Get $get, ?User $record): array {
                                                $dealerIds = collect($get('dealers') ?? $record?->dealers->modelKeys() ?? [])
                                                    ->filter(fn ($dealerId) => filled($dealerId))
                                                    ->map(fn ($dealerId) => (int) $dealerId)
                                                    ->values()
                                                    ->all();

                                                return Team::query()
                                                    ->when(
                                                        count($dealerIds) > 0,
                                                        fn ($query) => $query->whereIn('dealer_id', $dealerIds)
                                                    )
                                                    ->orderBy('name')
                                                    ->pluck('name', 'id')
                                                    ->all();
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->placeholder('Select a team')
                                            ->helperText('Assign the contributor to an existing team for the selected dealer.')
                                            ->live()
                                            ->hidden(fn (Get $get, ?User $record): bool => ! self::shouldUseTeamSelection($get('roles'), $record))
                                            ->required(fn (Get $get, ?User $record): bool => self::shouldUseTeamSelection($get('roles'), $record)),
                                        TextInput::make('password')
                                            ->label('Password')
                                            ->disabled(fn (): bool => UserResource::canAssignTeams() && ! UserResource::canManageUsers())
                                            ->password()
                                            ->revealable()
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (?string $state): bool => filled($state))
                                            ->minLength(8)
                                            ->placeholder('Enter password')
                                            ->helperText('Leave blank during edit to keep the current password.'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    protected static function rolesContainContributor(array | null $roles): bool
    {
        if (blank($roles)) {
            return false;
        }

        $normalizedRoles = collect($roles)
            ->filter(fn ($role) => filled($role))
            ->map(fn ($role) => is_string($role) ? strtolower(trim($role)) : $role)
            ->values();

        if ($normalizedRoles->contains('contributor')) {
            return true;
        }

        $contributorRoleId = Role::query()
            ->where('name', 'contributor')
            ->value('id');

        if (! $contributorRoleId) {
            return false;
        }

        return $normalizedRoles
            ->map(fn ($role) => (int) $role)
            ->contains((int) $contributorRoleId);
    }

    protected static function shouldUseTeamSelection(array | null $roles, ?User $record): bool
    {
        return self::rolesContainContributor($roles)
            || $record?->hasRole('contributor')
            || filled($record?->team_id);
    }

    protected static function shouldLimitDealersToOne(array | null $roles, ?User $record): bool
    {
        return self::rolesContainContributor($roles) || $record?->hasRole('contributor');
    }
}
