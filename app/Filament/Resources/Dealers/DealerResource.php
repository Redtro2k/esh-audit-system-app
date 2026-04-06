<?php

namespace App\Filament\Resources\Dealers;

use App\Enum\NavigationGroup;
use App\Filament\Resources\Dealers\Pages\ManageDealers;
use App\Models\Dealer;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DealerResource extends Resource
{
    protected static ?string $model = Dealer::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string | UnitEnum | null $navigationGroup = NavigationGroup::MasterData;

    protected static ?string $navigationLabel = 'Dealers';

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isGloballySearchable = false;

    public static function shouldRegisterNavigation(): bool
    {
        return static::canManageDealers();
    }

    public static function canAccess(): bool
    {
        return static::canManageDealers();
    }

    public static function canViewAny(): bool
    {
        return static::canManageDealers();
    }

    public static function canCreate(): bool
    {
        return static::canManageDealers();
    }

    public static function canEdit($record): bool
    {
        return static::canManageDealers();
    }

    public static function canDelete($record): bool
    {
        return static::canManageDealers();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('creator')
            ->visibleTo(auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('acronym')
                    ->label('Acronym')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('Enter dealer acronym'),
                TextInput::make('name')
                    ->label('Dealer Name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('Enter dealer name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('acronym')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('System')
                    ->toggleable(),
                TextColumn::make('users_count')
                    ->label('Users')
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
            'index' => ManageDealers::route('/'),
        ];
    }

    protected static function canManageDealers(): bool
    {
        return auth()->user()?->hasRole('developer') ?? false;
    }
}
