<?php

namespace App\Filament\Resources\Observations\Pages;

use App\Filament\Resources\Observations\ObservationResource;
use App\Models\Dealer;
use App\Models\Department;
use App\Models\Observation;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;

class PresentObservations extends Page
{
    protected static string $resource = ObservationResource::class;

    protected string $view = 'filament.resources.observations.pages.present-observations';

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Presentation;

    #[Url(as: 'dealer', except: '')]
    public ?string $dealerId = '';

    #[Url(as: 'department', except: '')]
    public ?string $departmentId = '';

    #[Url(as: 'status', except: '')]
    public ?string $status = '';

    #[Url(as: 'from', except: '')]
    public ?string $capturedFrom = '';

    #[Url(as: 'until', except: '')]
    public ?string $capturedUntil = '';

    #[Url(as: 'slide', except: 0)]
    public int $slide = 0;

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->clampSlide();
    }

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->hasAnyRole(['auditor', 'gm', 'developer']) ?? false;
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Observation Presentation';
    }

    public function getBreadcrumb(): ?string
    {
        return 'Presentation';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToObservations')
                ->label('Back to Observations')
                ->icon(LucideIcon::ArrowLeft)
                ->url(ObservationResource::getUrl('index')),
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'md' => 2,
                'xl' => 5,
            ])
            ->live()
            ->components([
                Select::make('dealerId')
                    ->label('Dealer')
                    ->placeholder('All dealers')
                    ->options(fn (): array => $this->getDealerOptions()->all())
                    ->searchable()
                    ->native(false)
                    ->afterStateUpdated(function (): void {
                        $this->resetSlide();
                    }),

                Select::make('departmentId')
                    ->label('Department')
                    ->placeholder('All departments')
                    ->options(fn (): array => $this->getDepartmentOptions()->all())
                    ->searchable()
                    ->native(false)
                    ->afterStateUpdated(function (): void {
                        $this->resetSlide();
                    }),

                Select::make('status')
                    ->label('Status')
                    ->placeholder('All statuses')
                    ->options($this->getStatusOptions())
                    ->native(false)
                    ->afterStateUpdated(function (): void {
                        $this->resetSlide();
                    }),

                DatePicker::make('capturedFrom')
                    ->label('Captured from')
                    ->native(false)
                    ->afterStateUpdated(function (): void {
                        $this->resetSlide();
                    }),

                DatePicker::make('capturedUntil')
                    ->label('Captured until')
                    ->native(false)
                    ->afterStateUpdated(function (): void {
                        $this->resetSlide();
                    }),
            ]);
    }

    public function updatedDealerId(): void
    {
        $this->resetSlide();
    }

    public function updatedDepartmentId(): void
    {
        $this->resetSlide();
    }

    public function updatedStatus(): void
    {
        $this->resetSlide();
    }

    public function updatedCapturedFrom(): void
    {
        $this->resetSlide();
    }

    public function updatedCapturedUntil(): void
    {
        $this->resetSlide();
    }

    public function previousSlide(): void
    {
        $this->slide = max(0, $this->slide - 1);
    }

    public function nextSlide(): void
    {
        $this->slide = min(max(0, $this->getPresentationObservations()->count() - 1), $this->slide + 1);
    }

    public function resetFilters(): void
    {
        $this->dealerId = '';
        $this->departmentId = '';
        $this->status = '';
        $this->capturedFrom = '';
        $this->capturedUntil = '';
        $this->resetSlide();
    }

    public function resetSlide(): void
    {
        $this->slide = 0;
    }

    public function getPresentationObservations(): Collection
    {
        return $this->baseQuery()
            ->get()
            ->values();
    }

    public function getCurrentObservation(): ?Observation
    {
        $observations = $this->getPresentationObservations();

        if ($observations->isEmpty()) {
            $this->slide = 0;

            return null;
        }

        $this->clampSlide($observations->count());

        return $observations->get($this->slide);
    }

    public function getDealerOptions(): Collection
    {
        return Dealer::query()
            ->visibleTo(auth()->user())
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    public function getDepartmentOptions(): Collection
    {
        return Department::query()
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    public function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'ongoing' => 'Ongoing',
            'for further discussion' => 'For Further Discussion',
            'resolved' => 'Resolved',
        ];
    }

    public function imageUrls(mixed $paths): array
    {
        return collect($paths)
            ->flatten()
            ->filter(fn ($path): bool => filled($path) && is_string($path))
            ->map(fn (string $path): string => Str::startsWith($path, ['http://', 'https://', '/storage/'])
                ? $path
                : Storage::disk('public')->url($path))
            ->values()
            ->all();
    }

    public function plainText(mixed $value): string
    {
        return trim(html_entity_decode(strip_tags(Str::markdown((string) $value)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    public function statusLabel(?string $status): string
    {
        return Str::headline((string) $status);
    }

    public function statusClass(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'pending' => 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
            'ongoing' => 'bg-amber-100 text-amber-800 ring-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:ring-amber-700/60',
            'for further discussion' => 'bg-sky-100 text-sky-800 ring-sky-200 dark:bg-sky-900/40 dark:text-sky-200 dark:ring-sky-700/60',
            'resolved' => 'bg-emerald-100 text-emerald-800 ring-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-200 dark:ring-emerald-700/60',
            default => 'bg-gray-100 text-gray-700 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700',
        };
    }

    public function targetDateLabel(?Observation $observation): string
    {
        if (! $observation?->target_date) {
            return 'No target date';
        }

        return Carbon::parse($observation->target_date)->format('M j, Y g:i A');
    }

    public function isOverdue(?Observation $observation): bool
    {
        return filled($observation?->target_date)
            && Carbon::parse($observation->target_date)->isPast()
            && strtolower((string) $observation->status) !== 'resolved';
    }

    protected function baseQuery(): Builder
    {
        return ObservationResource::getEloquentQuery()
            ->with(['concernType', 'dealer', 'pic.department', 'auditor'])
            ->whereIn('status', array_keys($this->getStatusOptions()))
            ->when(filled($this->dealerId), fn (Builder $query) => $query->where('dealer_id', $this->dealerId))
            ->when(filled($this->departmentId), fn (Builder $query) => $query->whereHas(
                'pic',
                fn (Builder $picQuery) => $picQuery->where('department_id', $this->departmentId),
            ))
            ->when(filled($this->status), fn (Builder $query) => $query->where('status', $this->status))
            ->when(filled($this->capturedFrom), fn (Builder $query) => $query->whereDate('date_captured', '>=', $this->capturedFrom))
            ->when(filled($this->capturedUntil), fn (Builder $query) => $query->whereDate('date_captured', '<=', $this->capturedUntil))
            ->orderByRaw("case when status != 'resolved' and target_date is not null and target_date < ? then 0 else 1 end", [now()])
            ->orderByRaw('case when target_date is null then 1 else 0 end')
            ->orderBy('target_date')
            ->orderByRaw('coalesce(date_captured, created_at) desc');
    }

    protected function clampSlide(?int $count = null): void
    {
        $count ??= $this->getPresentationObservations()->count();

        if ($count <= 0) {
            $this->slide = 0;

            return;
        }

        $this->slide = min(max(0, $this->slide), $count - 1);
    }
}
