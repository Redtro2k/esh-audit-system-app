<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Observations\ObservationResource;
use App\Models\Observation;
use App\Models\User;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class UsersInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->icon(LucideIcon::UserRound)
                    ->iconColor('primary')
                    ->columns(4)
                    ->schema([
                        ImageEntry::make('avatar_url')
                            ->label('Avatar')
                            ->state(fn (User $record): string => $record->getFilamentAvatarUrl())
                            ->circular()
                            ->size(72),
                        Grid::make(2)
                            ->columnSpan(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Name')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                                TextEntry::make('username')
                                    ->label('Username')
                                    ->placeholder('No username'),
                                TextEntry::make('email')
                                    ->label('Email')
                                    ->copyable(),
                                TextEntry::make('email_verified_at')
                                    ->label('Email Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => filled($state) ? 'Verified' : 'Unverified')
                                    ->color(fn ($state): string => filled($state) ? 'success' : 'gray'),
                                TextEntry::make('department.name')
                                    ->label('Department')
                                    ->placeholder('No department'),
                                TextEntry::make('team.name')
                                    ->label('Team')
                                    ->placeholder('No team'),
                                TextEntry::make('dealers.acronym')
                                    ->label('Dealers')
                                    ->badge()
                                    ->separator(',')
                                    ->placeholder('No dealers'),
                                TextEntry::make('roles.name')
                                    ->label('Roles')
                                    ->badge()
                                    ->separator(',')
                                    ->placeholder('No roles'),
                            ]),
                    ]),
                Section::make('Observation Summary')
                    ->icon(LucideIcon::ClipboardList)
                    ->iconColor('primary')
                    ->columns(4)
                    ->schema([
                        self::countEntry('audited_observations_count', 'Created as Auditor', fn (User $record): int => self::visibleObservationsFor($record)
                            ->where('auditor_id', $record->getKey())
                            ->count()),
                        self::countEntry('pic_observations_count', 'Assigned as PIC', fn (User $record): int => self::visibleObservationsFor($record)
                            ->where('pic_id', $record->getKey())
                            ->count()),
                        self::countEntry('pending_observations_count', 'Pending', fn (User $record): int => self::visibleObservationsFor($record)
                            ->where(fn (Builder $query) => $query
                                ->where('auditor_id', $record->getKey())
                                ->orWhere('pic_id', $record->getKey()))
                            ->where('status', 'pending')
                            ->count()),
                        self::countEntry('resolved_observations_count', 'Resolved', fn (User $record): int => self::visibleObservationsFor($record)
                            ->where(fn (Builder $query) => $query
                                ->where('auditor_id', $record->getKey())
                                ->orWhere('pic_id', $record->getKey()))
                            ->where('status', 'resolved')
                            ->count()),
                    ]),
                Section::make('Observation Concerns')
                    ->icon(LucideIcon::ClipboardPenLine)
                    ->iconColor('primary')
                    ->description('Latest visible observations where this user is the auditor or the person in charge.')
                    ->schema([
                        TextEntry::make('observation_concerns')
                            ->hiddenLabel()
                            ->state(fn (User $record): HtmlString => new HtmlString(self::renderObservationConcerns($record)))
                            ->html(),
                    ]),
            ]);
    }

    protected static function countEntry(string $name, string $label, callable $count): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->state(fn (User $record): int => $count($record))
            ->badge()
            ->color('primary');
    }

    protected static function visibleObservationsFor(User $record): Builder
    {
        return ObservationResource::applyObservationVisibility(
            Observation::query()->with(['dealer', 'pic.department', 'auditor']),
            auth()->user(),
            includeSubscriptions: true,
        );
    }

    protected static function renderObservationConcerns(User $record): string
    {
        $observations = self::visibleObservationsFor($record)
            ->where(fn (Builder $query) => $query
                ->where('auditor_id', $record->getKey())
                ->orWhere('pic_id', $record->getKey()))
            ->latest()
            ->limit(10)
            ->get();

        if ($observations->isEmpty()) {
            return '<p class="text-sm text-gray-500 dark:text-gray-400">No visible observation concerns found for this user.</p>';
        }

        $items = $observations
            ->map(function (Observation $observation): string {
                $url = e(ObservationResource::getUrl('view', ['record' => $observation]));
                $area = e($observation->area ?: 'No audit area');
                $concern = e(str($observation->concern ?: 'No concern details')->squish()->limit(180));
                $status = e(ucwords(strtolower((string) $observation->status)));
                $dealer = e($observation->dealer?->name ?? 'No dealer');
                $pic = e($observation->pic?->name ?? 'No PIC');
                $auditor = e($observation->auditor?->name ?? 'No auditor');
                $date = e(optional($observation->created_at)->format('M j, Y g:i A') ?? 'No date');

                return <<<HTML
                    <li class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <a href="{$url}" class="text-sm font-semibold text-primary-600 hover:underline dark:text-primary-400">{$area}</a>
                            <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">{$status}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{$concern}</p>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{$dealer} | PIC: {$pic} | Auditor: {$auditor} | {$date}</p>
                    </li>
                HTML;
            })
            ->implode('');

        return '<ul class="space-y-2">'.$items.'</ul>';
    }
}
