<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->getStateUsing(fn (User $record): string => (string) $record->getFilamentAvatarUrl()),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('email_verified_at')
                    ->label('Email Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => filled($state) ? 'Verified' : 'Unverified')
                    ->color(fn ($state): string => filled($state) ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('team.name')
                    ->label('Team')
                    ->placeholder('No team')
                    ->toggleable(),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->placeholder('No department')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('team_id')
                    ->label('Team')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (User $record): bool => ! UserResource::canManageUsers() || auth()->id() === $record->getKey()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->hidden(fn (): bool => ! UserResource::canManageUsers())
                        ->action(function ($records): void {
                            $records
                                ->reject(fn (User $record): bool => auth()->id() === $record->getKey())
                                ->each
                                ->delete();
                        }),
                ]),
            ]);
    }
}
