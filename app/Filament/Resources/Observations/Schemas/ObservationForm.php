<?php

namespace App\Filament\Resources\Observations\Schemas;

use App\Models\ConcernCategory;
use App\Models\Department;
use App\Models\User;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
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
                            ->hidden(fn ($q) => auth()->user()->hasAnyRole(['remediator', 'representative']))
                            ->schema([
                                Grid::make(3)->schema([
                                    Select::make('dealer_id')
                                        ->label('Dealer')
                                        ->placeholder('Select a dealer')
                                        ->helperText(fn (): string => self::currentUserMustUseAssignedDealer()
                                            ? 'This dealer is automatically assigned from your user account.'
                                            : 'Choose the dealer for this observation.')
                                        ->relationship(
                                            'dealer',
                                            'name',
                                            modifyQueryUsing: fn ($query) => $query
                                                ->visibleTo(auth()->user())
                                                ->orderBy('name')
                                        )
                                        ->native(false)
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->hidden(fn (): bool => self::currentUserMustUseAssignedDealer())
                                        ->disabled(fn (): bool => self::currentUserMustUseAssignedDealer())
                                        ->dehydrated()
                                        ->live()
                                        ->default(fn (): ?int => self::currentUserDealerId())
                                        ->afterStateHydrated(function (Set $set, Get $get, $state, $record): void {
                                            if (self::currentUserMustUseAssignedDealer() && blank($state)) {
                                                $set('dealer_id', self::currentUserDealerId());
                                            }

                                            if (! $record && filled($state ?: self::resolveDealerId($get))) {
                                                self::syncDepartmentAndPicSelection(
                                                    set: $set,
                                                    get: $get,
                                                    dealerId: $state ?: self::resolveDealerId($get),
                                                );
                                            }
                                        })
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $effectiveDealerId = filled($state)
                                                ? $state
                                                : self::resolveDealerId($get);

                                            if (blank($effectiveDealerId)) {
                                                $set('department', null);
                                                $set('pic_id', null);

                                                return;
                                            }

                                            self::syncDepartmentAndPicSelection(
                                                set: $set,
                                                get: $get,
                                                dealerId: $effectiveDealerId,
                                            );
                                        }),
                                    Select::make('department')
                                        ->label('Department')
                                        ->placeholder('Select a department')
                                        ->helperText('Choose the department that owns this observation. Dealer selection will narrow the available departments.')
                                        ->native(false)
                                        ->options(fn (Get $get): array => self::getDepartmentOptions($get))
                                        ->live()
                                        ->dehydrated(false)
                                        ->afterStateHydrated(function (Set $set, Get $get, $record) {
                                            if ($record?->pic?->department_id) {
                                                $set('department', $record->pic->department_id);
                                            }
                                        })
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            self::syncPicSelection(
                                                set: $set,
                                                get: $get,
                                                departmentId: $state,
                                            );
                                        })
                                        ->disabled(fn (Get $get): bool => blank(self::resolveDealerId($get))),
                                    Select::make('pic_id')
                                        ->label('PIC')
                                        ->placeholder('Select PIC')
                                        ->helperText('Pick the person in charge who will respond to this observation.')
                                        ->options(fn (Get $get): array => self::getPicOptions($get))
                                        ->preload()
                                        ->native(false)
                                        ->searchable(['name'])
                                        ->live()
                                        ->required()
                                        ->disabled(fn (Get $get) => blank($get('department'))),
                                    TextInput::make('area')
                                        ->nullable(false)
                                        ->label('Audit Area')
                                        ->helperText('Enter the process, location, or activity covered by the audit.')
                                        ->placeholder('e.g. Warehouse Receiving'),
                                ]),
                                Grid::make()
                                    ->schema([
                                        Select::make('concern_type')
                                            ->label('Category of Concern')
                                            ->placeholder('Select concern category')
                                            ->options(ConcernCategory::query()->whereNull('parent_id')->pluck('name', 'id'))
                                            ->live()
                                            ->helperText(fn (Get $get) => $get('concern_type')
                                                ? implode(', ', ConcernCategory::query()->where('parent_id', $get('concern_type'))->pluck('name')->toArray())
                                                : 'Select a category to view available concerns.')
                                            ->nullable(false),
                                        MarkdownEditor::make('concern')
                                            ->columnSpanFull()
                                            ->label('Concern / Remarks')
                                            ->helperText('Describe the issue clearly, including what was observed and why it matters.')
                                            ->placeholder('Describe the concern'),
                                    ]),
                                Grid::make()
                                    ->schema([
                                        DateTimePicker::make('date_captured')
                                            ->label('Date Captured')
                                            ->placeholder('Select capture date and time')
                                            ->helperText('Optional. Use this if you want to record when the observation was captured.')
                                            ->hidden(fn () => auth()->user()->hasAnyRole(['remediator', 'representative']))
                                            ->hiddenOn('edit')
                                            ->nullable()
                                            ->native(false),
                                        Radio::make('status')
                                            ->helperText('Set the current progress of this observation.')
                                            ->hiddenOn('create')
                                            ->inlineLabel(false)
                                            ->inline()
                                            ->nullable(false)
                                            ->options(self::statusOptions()),
                                    ]),
                                FileUpload::make('capture_concern')
                                    ->label('Proof Concern')
                                    ->multiple()
                                    ->nullable(false)
                                    ->image()
                                    ->disk('public') // 🔥 VERY IMPORTANT
                                    ->directory('concerns') // optional but recommended
                                    ->visibility('public') // 🔥 ensure accessible
                                    ->maxSize(10240)
                                    ->imageEditor()
                                    ->imageEditorMode(2)
                                    ->helperText('Upload one or more images that support the audit concern.'),

                            ]),
                        Tab::make('Counter Measure')
                            ->hidden(fn ($q) => auth()->user()->hasAnyRole(['auditor', 'contributor']))
                            ->icon(LucideIcon::ClipboardPen)
                            ->schema([
                                DateTimePicker::make('target_date')
                                    ->label('Target Date')
                                    ->placeholder('Select target date and time')
                                    ->helperText('PIC should set the expected completion date for this observation.')
                                    ->required(auth()->user()->hasAnyRole(['remediator', 'representative']))
                                    ->nullable(),
                                FileUpload::make('capture_solved')
                                    ->label('Upload Solved')
                                    ->placeholder('Upload solved proof images')
                                    ->multiple()
                                    ->nullable(false)
                                    ->image()
                                    ->disk('public')
                                    ->directory('solved')
                                    ->visibility('public')
                                    ->maxSize(10240)
                                    ->imageEditor()
                                    ->helperText('Upload one or more images showing the corrective action or completed fix.')
                                    ->required(auth()->user()->hasAnyRole(['remediator', 'representative'])),
                                Grid::make(2)
                                    ->schema([
                                        MarkdownEditor::make('counter_measure')
                                            ->label('Counter Measure')
                                            ->helperText('Explain the action taken or planned to address the concern.')
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
                                            ->required(auth()->user()->hasAnyRole(['remediator', 'representative'])),
                                        MarkdownEditor::make('remarks')
                                            ->label('Remarks')
                                            ->helperText('Add supporting notes, clarifications, or important follow-up details.')
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
                                            ->hint('Use AI to improve your wording while keeping the same meaning.'),
                                    ]),

                            ]),
                    ]),
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

    private static function statusOptions(): array
    {
        if (auth()->user()->hasRole('contributor')) {
            return [
                'pending' => 'Pending',
                'ongoing' => 'Ongoing',
            ];
        }

        return [
            'pending' => 'Pending',
            'ongoing' => 'Ongoing',
            'for further discussion' => 'For Further Discussion',
            'resolved' => 'Resolved',
        ];
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

    private static function currentUserMustUseAssignedDealer(): bool
    {
        return auth()->user()?->hasAnyRole(['auditor', 'contributor']) ?? false;
    }

    private static function currentUserDealerId(): ?int
    {
        return auth()->user()?->dealers()->orderBy('dealers.name')->value('dealers.id');
    }

    private static function syncDepartmentAndPicSelection(Set $set, Get $get, mixed $dealerId = null): void
    {
        $dealerId ??= self::resolveDealerId($get);
        $currentDepartmentId = $get('department');
        $departmentOptions = self::getDepartmentOptions($get, $dealerId);

        if ($departmentOptions === []) {
            $set('department', null);
            $set('pic_id', null);

            return;
        }

        $departmentId = array_key_exists((string) $currentDepartmentId, array_combine(
            array_map('strval', array_keys($departmentOptions)),
            array_keys($departmentOptions),
        ) ?: [])
            ? $currentDepartmentId
            : array_key_first($departmentOptions);

        $set('department', $departmentId);

        self::syncPicSelection(
            set: $set,
            get: $get,
            dealerId: $dealerId,
            departmentId: $departmentId,
        );
    }

    private static function getDepartmentOptions(Get $get, mixed $dealerId = null): array
    {
        $dealerId ??= self::resolveDealerId($get);

        return self::buildDepartmentQuery($dealerId)
            ->pluck('name', 'id')
            ->all();
    }

    private static function syncPicSelection(Set $set, Get $get, mixed $dealerId = null, mixed $departmentId = null): void
    {
        $dealerId ??= self::resolveDealerId($get);
        $departmentId ??= $get('department');
        $currentPicId = $get('pic_id');

        if (blank($departmentId)) {
            $set('pic_id', null);

            return;
        }

        if (filled($currentPicId)) {
            $picStillMatchesSelection = self::buildPicQuery($departmentId, $dealerId)
                ->whereKey($currentPicId)
                ->exists();

            if ($picStillMatchesSelection) {
                return;
            }
        }

        $firstPicId = self::buildPicQuery($departmentId, $dealerId)
            ->value('id');

        $set('pic_id', $firstPicId);
    }

    private static function getPicOptions(Get $get): array
    {
        $departmentId = $get('department');

        if (blank($departmentId)) {
            return [];
        }

        return self::buildPicQuery($departmentId, self::resolveDealerId($get))
            ->pluck('name', 'id')
            ->all();
    }

    private static function buildPicQuery(mixed $departmentId, mixed $dealerId = null): Builder
    {
        $baseQuery = User::query()
            ->where('department_id', $departmentId)
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', ['remediator', 'representative']))
            ->orderBy('name');

        if (blank($dealerId)) {
            return $baseQuery;
        }

        return $baseQuery
            ->whereHas('dealers', fn ($query) => $query->whereKey($dealerId));
    }

    private static function buildDepartmentQuery(mixed $dealerId = null): Builder
    {
        $query = Department::query()
            ->whereHas('users', fn (Builder $query) => $query
                ->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->whereIn('name', ['remediator', 'representative'])));

        if (blank($dealerId)) {
            return $query->orderBy('name');
        }

        return $query
            ->whereHas('users', fn (Builder $query) => $query
                ->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->whereIn('name', ['remediator', 'representative']))
                ->whereHas('dealers', fn ($dealerQuery) => $dealerQuery->whereKey($dealerId)))
            ->orderBy('name');
    }

    private static function resolveDealerId(Get $get): mixed
    {
        return $get('dealer_id') ?: self::currentUserDealerId();
    }
}
