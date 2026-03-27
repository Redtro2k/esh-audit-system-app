<?php

namespace App\Filament\Resources\Observations\Schemas;

use App\Models\ConcernCategory;
use App\Models\Department;
use App\Models\User;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Media\Image;
use Prism\Prism\ValueObjects\Messages\UserMessage;

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
                                    ->disk('public') // 🔥 VERY IMPORTANT
                                    ->directory('concerns') // optional but recommended
                                    ->visibility('public') // 🔥 ensure accessible
                                    ->maxSize(1024)
                                    ->imageEditor()
                                    ->imageEditorMode(2)
                                    ->helperText('Upload one or more concern proof images.'),

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
                                    ->disk('public')
                                    ->directory('solved')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->helperText('Upload one or more solved proof images.')
                                    ->required(auth()->user()->hasRole('remediator')),
                               Grid::make(2)
                                    ->schema([
                                        Textarea::make('counter_measure')
                                            ->label('Counter Measure')
                                            ->placeholder('Describe corrective action taken')
                                            ->hintAction(
                                                Action::make('generateCounterMeasure')
                                                    ->label('Improve writing')
                                                    ->icon(LucideIcon::Sparkles)
                                                    ->color('gray')
                                                    ->action(function (Get $get, Set $set): void {
                                                        $currentCounterMeasure = trim((string) ($get('counter_measure') ?? ''));
                                                        $imagePaths = collect($get('capture_solved') ?? [])
                                                            ->filter(fn ($path) => filled($path))
                                                            ->values();

                                                        if ($currentCounterMeasure === '') {
                                                            Notification::make()
                                                                ->title('Write the counter measure first')
                                                                ->body('Add your draft sentence first, then use AI to improve the wording.')
                                                                ->warning()
                                                                ->send();

                                                            return;
                                                        }

                                                        try {
                                                            $images = $imagePaths
                                                                ->take(3)
                                                                ->map(fn (string $path) => Image::fromStoragePath($path, 'public'))
                                                                ->all();

                                                            $prompt = self::buildCounterMeasurePrompt($get, $currentCounterMeasure);

                                                            $response = Prism::text()
                                                                ->using(Provider::Gemini, 'gemini-2.5-flash-lite')
                                                                ->withMessages([
                                                                    new UserMessage($prompt, $images),
                                                                ])
                                                                ->generate();

                                                            $generatedText = trim($response->text);

                                                            if ($generatedText === '') {
                                                                throw new \RuntimeException('The AI returned an empty response.');
                                                            }

                                                            $set('counter_measure', $generatedText);

                                                            Notification::make()
                                                                ->title('Counter measure improved')
                                                                ->success()
                                                                ->send();
                                                        } catch (\Throwable $exception) {
                                                            Notification::make()
                                                                ->title('Generation failed')
                                                                ->body($exception->getMessage())
                                                                ->danger()
                                                                ->send();
                                                        }
                                                    })
                                            )
                                            ->hint('Use AI to improve your wording while keeping the same meaning.')
                                            ->required(auth()->user()->hasRole('remediator')),
                                        Textarea::make('remarks')
                                            ->label('Remarks')
                                            ->placeholder('Add supporting notes')
                                            ->hintAction(
                                                Action::make('improveRemarks')
                                                    ->label('Improve writing')
                                                    ->icon(LucideIcon::Sparkles)
                                                    ->color('gray')
                                                    ->action(function (Get $get, Set $set): void {
                                                        $currentRemarks = trim((string) ($get('remarks') ?? ''));
                                                        $imagePaths = collect($get('capture_solved') ?? [])
                                                            ->filter(fn ($path) => filled($path))
                                                            ->values();

                                                        if ($currentRemarks === '') {
                                                            Notification::make()
                                                                ->title('Write the remarks first')
                                                                ->body('Add your draft remarks first, then use AI to improve the wording.')
                                                                ->warning()
                                                                ->send();

                                                            return;
                                                        }

                                                        try {
                                                            $images = $imagePaths
                                                                ->take(3)
                                                                ->map(fn (string $path) => Image::fromStoragePath($path, 'public'))
                                                                ->all();

                                                            $prompt = self::buildWritingImprovementPrompt(
                                                                get: $get,
                                                                fieldLabel: 'Remarks',
                                                                draftText: $currentRemarks
                                                            );

                                                            $response = Prism::text()
                                                                ->using(Provider::Gemini, 'gemini-2.5-flash-lite')
                                                                ->withMessages([
                                                                    new UserMessage($prompt, $images),
                                                                ])
                                                                ->generate();

                                                            $generatedText = trim($response->text);

                                                            if ($generatedText === '') {
                                                                throw new \RuntimeException('The AI returned an empty response.');
                                                            }

                                                            $set('remarks', $generatedText);

                                                            Notification::make()
                                                                ->title('Remarks improved')
                                                                ->success()
                                                                ->send();
                                                        } catch (\Throwable $exception) {
                                                            Notification::make()
                                                                ->title('Generation failed')
                                                                ->body($exception->getMessage())
                                                                ->danger()
                                                                ->send();
                                                        }
                                                    })
                                            )
                                            ->hint('Use AI to improve your wording while keeping the same meaning.')
                                            ->required(auth()->user()->hasRole('remediator'))
                                    ])

                            ])
                    ])
            ]);
    }

    private static function buildCounterMeasurePrompt(Get $get, string $draftCounterMeasure): string
    {
        return self::buildWritingImprovementPrompt(
            get: $get,
            fieldLabel: 'Counter Measure',
            draftText: $draftCounterMeasure
        );
    }

    private static function buildWritingImprovementPrompt(Get $get, string $fieldLabel, string $draftText): string
    {
        $context = array_filter([
            'Audit Area' => $get('area'),
            'Concern Category' => self::resolveConcernCategoryName($get('concern_type')),
            'Concern' => $get('concern'),
            'Current Status' => $get('status'),
        ], fn ($value) => filled($value));

        $contextLines = collect($context)
            ->map(fn ($value, $label) => "{$label}: {$value}")
            ->implode("\n");

        return <<<PROMPT
You are assisting with an internal audit observation update.

Your task is to improve the wording of the user's {$fieldLabel} text.

Rules:
- Preserve the original meaning.
- Improve grammar, clarity, and professionalism.
- Keep the tone practical, concise, and audit-ready.
- Do not invent facts or actions that are not already implied by the user's draft.
- If images are available, use them only to keep the wording aligned with the evidence.
- Do not mention that the text was AI-generated.
- Do not include bullet points.
- Return only the improved {$fieldLabel} text.

Observation Context:
{$contextLines}

User Draft:
{$draftText}
PROMPT;
    }

    private static function resolveConcernCategoryName(mixed $concernTypeId): ?string
    {
        if (blank($concernTypeId)) {
            return null;
        }

        return ConcernCategory::query()->whereKey($concernTypeId)->value('name');
    }
}
