<?php

namespace App\Filament\Resources\Observations\Schemas;

use App\Models\ConcernCategory;
use App\Models\Department;
use App\Models\User;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;

class ObservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Auditor')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Audit')
                            ->icon(LucideIcon::ClipboardCheck)
                            ->hidden(fn($q) => auth()->user()->hasRole('remediator'))
                            ->schema([
                                Grid::make(3)->schema([
                                    Select::make('department')
                                        ->label('Department')
                                        ->placeholder('Select a department')
                                        ->native(false)
                                        ->options(Department::pluck('name', 'id'))
                                        ->reactive()
                                        ->dehydrated(false)
                                        ->afterStateHydrated(function (Set $set, Get $get, $record) {
                                            if ($record?->pic?->department_id) {
                                                $set('department', $record->pic->department_id);
                                            }
                                        })
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $set('pic_id', null);

                                            if (blank($state)) {
                                                return;
                                            }

                                            $firstPicId = User::query()
                                                ->where('department_id', $state)
                                                ->role('remediator')
                                                ->orderBy('name')
                                                ->value('id');

                                            if ($firstPicId) {
                                                $set('pic_id', $firstPicId);
                                            }
                                        }),
                                    Select::make('pic_id')
                                        ->label('PIC')
                                        ->placeholder('Select PIC')
                                        ->relationship(
                                            'pic',
                                            'name',
                                            modifyQueryUsing: fn ($query, Get $get) =>
                                            $query
                                                ->when(
                                                    $get('department'),
                                                    fn ($q) => $q->where('department_id', $get('department'))
                                                )
                                                ->role('remediator')
                                                ->orderBy('name')
                                        )
                                        ->preload()
                                        ->native(false)
                                        ->searchable(['name'])
                                        ->required()
                                        ->disabled(fn (Get $get) => blank($get('department'))),
                                    TextInput::make('area')
                                        ->nullable(false)
                                        ->label('Audit Area')
                                        ->placeholder('e.g. Warehouse Receiving'),
                                ]),
                                Grid::make()
                                    ->schema([
                                        Select::make('concern_type')
                                            ->label('Category of Concern')
                                            ->placeholder('Select concern category')
                                            ->options(ConcernCategory::query()->whereNull('parent_id')->pluck('name', 'id'))
                                            ->live()
                                            ->helperText(fn(Get $get) => $get('concern_type')
                                                ? implode(', ', ConcernCategory::query()->where('parent_id', $get('concern_type'))->pluck('name')->toArray())
                                                : 'Select a category to view available concerns.')
                                            ->nullable(false),
                                        TextInput::make('concern')
                                            ->label('Concern / Remarks')
                                            ->placeholder('Describe the concern'),
                                    ]),
                                Radio::make('status')
                                    ->hiddenOn('create')
                                    ->inlineLabel(false)
                                    ->inline()
                                    ->nullable(false)
                                    ->options([
                                        'pending' => 'Pending',
                                        'ongoing' => 'Ongoing',
                                        'for further discussion' => 'For Further Discussion',
                                        'resolved' => 'Resolved',
                                    ]),
                                DateTimePicker::make('target_date')
                                    ->placeholder('Select target date and time')
                                    ->nullable(false),
                                FileUpload::make('capture_concern')
                                    ->multiple()
                                    ->nullable(false)
                                    ->image()
                                    ->maxSize(1024)
                                    ->imageEditor()
                                    ->imageEditorMode(2)
                                    ->helperText('Upload one or more concern proof images.')
                            ]),
                        Tab::make('Counter Measure')
                            ->hidden(fn($q) => auth()->user()->hasRole('auditor'))
                            ->icon(LucideIcon::ClipboardPen)
                            ->schema([
                                FileUpload::make('capture_solved')
                                    ->label('Upload Solved')
                                    ->multiple()
                                    ->nullable(false)
                                    ->image()
                                    ->imageEditor()
                                    ->helperText('Upload one or more solved proof images.')
                                    ->required(auth()->user()->hasRole('remediator')),
                               Grid::make(2)
                                    ->schema([
                                        Textarea::make('counter_measure')
                                            ->label('Counter Measure')
                                            ->placeholder('Describe corrective action taken')
                                            ->required(auth()->user()->hasRole('remediator')),
                                        Textarea::make('remarks')
                                            ->label('Remarks')
                                            ->placeholder('Add supporting notes')
                                            ->required(auth()->user()->hasRole('remediator'))
                                    ])

                            ])
                    ])
            ]);
    }
    //pending, ongoing,
}
