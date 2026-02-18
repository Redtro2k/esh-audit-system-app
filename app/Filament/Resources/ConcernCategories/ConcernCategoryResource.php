<?php

namespace App\Filament\Resources\ConcernCategories;

use App\Enum\NavigationGroup;
use App\Filament\Resources\ConcernCategories\Pages\ManageConcernCategories;
use App\Models\ConcernCategory;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use UnitEnum;


class ConcernCategoryResource extends Resource
{
    protected static ?string $model = ConcernCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Administration;

    protected static ?string $navigationLabel = 'Concern Categories';

    protected static ?string $recordTitleAttribute = 'name';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('auditor') ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('auditor') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('parent_id')
                    ->label('Parent Category')
                    ->options(ConcernCategory::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->groups([
                Group::make('parent.name')
                    ->label('Parent'),
            ])
            ->columns([
                TextColumn::make('name')
                    ->weight(fn (ConcernCategory $record): FontWeight => blank($record->parent_id) ? FontWeight::Bold : FontWeight::Medium)
                    ->color(fn (ConcernCategory $record): string => blank($record->parent_id) ? 'primary' : 'gray')
                    ->searchable(),
                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('No parent')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => ManageConcernCategories::route('/'),
        ];
    }
}
