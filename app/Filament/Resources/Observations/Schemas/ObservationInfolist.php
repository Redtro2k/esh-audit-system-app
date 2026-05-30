<?php

namespace App\Filament\Resources\Observations\Schemas;

use App\Filament\Infolists\Components\HoverImageEntry;
use App\Models\ConcernCategory;
use App\Models\Observation;
use App\Models\User;
use Carbon\CarbonInterface;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\HtmlString;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;

class ObservationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::auditSection(),
                self::responseSection(),
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        self::commentsSection()->columnSpan(2),
                        self::timelineSection()->columnSpan(1),
                    ]),
            ]);
    }

    public static function configureForEdit(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::auditSection(),
            ]);
    }

    protected static function auditSection(): Section
    {
        return Section::make('Audit')
            ->icon(LucideIcon::ClipboardPen)
            ->iconColor('primary')
            ->columnSpanFull()
            ->description('Record into the system, including audit details, classification, and current status for monitoring and resolution.')
            ->columns(2)
            ->schema([
                TextEntry::make('area')
                    ->size(TextSize::Large)
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->label('Audit Area'),
                Grid::make(2)
                    ->schema([
                        TextEntry::make('concernType.name')
                            ->label('Concern of Category')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->helperText(fn ($record) => $record->concern_type
                                ? implode(', ', ConcernCategory::query()->where('parent_id', $record->concern_type)->pluck('name')->toArray())
                                : 'Select a category to view available concerns.')
                            ->color('primary'),
                        TextEntry::make('concern')
                            ->label('Concern / Remarks')
                            ->formatStateUsing(fn (?string $state): HtmlString => self::formatMultilineText($state))
                            ->html()
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->color('primary'),
                    ]),
                TextEntry::make('auditor.name')->label('Auditor Name'),
                TextEntry::make('dealer.name')->label('Dealer'),
                TextEntry::make('date_captured')
                    ->label('Date Captured')
                    ->dateTime('l, F d, Y h:i A')
                    ->placeholder('No Date Captured'),
                TextEntry::make('target_date')
                    ->label('Target Date')
                    ->dateTime('l, F d, Y h:i A')
                    ->placeholder('No target date'),
                TextEntry::make('status')
                    ->size(TextSize::Large)
                    ->weight(FontWeight::Bold)
                    ->badge(),
                HoverImageEntry::make('capture_concern')
                    ->extraAttributes([
                        'alt' => 'Logo',
                        'loading' => 'lazy',
                    ])
                    ->imageGallery()
                    ->previewSize(420)
                    ->defaultImageUrl(asset('favicon.svg'))
                    ->ring(5)
                    ->label('Proof Concern'),
            ]);
    }

    protected static function responseSection(): Section
    {
        return Section::make('Response')
            ->icon(LucideIcon::ClipboardSignature)
            ->iconColor('primary')
            ->columnSpanFull()
            ->description('Provides management’s formal response to the audit finding, outlining corrective actions, timelines, and accountability.')
            ->columns()
            ->schema([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('pic.department.name')->label('Department Name')
                            ->icon(LucideIcon::Building)
                            ->iconColor('primary')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->color('primary'),
                        TextEntry::make('pic.name')->label('PIC')
                            ->icon(LucideIcon::User)
                            ->iconColor('primary')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->color('primary'),
                        TextEntry::make('area')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->color('primary')
                            ->label('Audit Area'),
                    ]),
                TextEntry::make('remarks')
                    ->placeholder('No Remarks')
                    ->label('Remarks')
                    ->formatStateUsing(fn (?string $state): HtmlString => self::formatMultilineText($state))
                    ->html(),
                TextEntry::make('counter_measure')
                    ->placeholder('No Counter Measure')
                    ->label('Counter Measure')
                    ->html(),
                TextEntry::make('counter_measure_date')
                    ->label('Counter Measure Date')
                    ->dateTime('l, F d, Y h:i A')
                    ->icon(LucideIcon::ClipboardPen)
                    ->iconColor('primary')
                    ->size(TextSize::Large)
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->placeholder('No Counter Measure Date Captured'),
                TextEntry::make('date_resolved')
                    ->label('Date Resolved')
                    ->dateTime('l, F d, Y h:i A')
                    ->icon(LucideIcon::CheckCheck)
                    ->iconColor('primary')
                    ->size(TextSize::Large)
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->placeholder('No Date Resolved Captured'),
                HoverImageEntry::make('capture_solved')
                    ->extraAttributes([
                        'alt' => 'Logo',
                        'loading' => 'lazy',
                    ])
                    ->placeholder('No Photo')
                    ->imageGallery()
                    ->previewSize(420)
                    ->defaultImageUrl(asset('favicon.svg'))
                    ->ring(5)
                    ->label('Proof Solved'),
            ]);
    }

    protected static function timelineSection(): Section
    {
        return Section::make('Timeline')
            ->icon(LucideIcon::ClipboardCheck)
            ->iconColor('primary')
            ->description('Shows the observation milestone dates together with the lead time from capture.')
            ->schema([
                TextEntry::make('timeline_view')
                    ->hiddenLabel()
                    ->state(fn (Observation $record): HtmlString => new HtmlString(self::renderTimelineHtml($record)))
                    ->html(),
            ]);
    }

    protected static function commentsSection(): Section
    {
        return Section::make('Comments')
            ->components([
                CommentsEntry::make('comments')
                    ->label('Comments')
                    ->mentionables(User::all())
                    ->placeholder('No Comments')
                    ->loadMoreIncrementsBy(8)
                    ->loadMoreLabel('Show older')
                    ->perPage(10)
                    ->tipTapCssClasses('prose max-w-none focus:outline-none p-4')
                    ->extraAttributes([
                        'class' => 'max-h-96 overflow-y-auto',
                    ]),
            ]);
    }

    protected static function renderTimelineHtml(Observation $record): string
    {
        $events = [
            [
                'title' => 'Date Captured',
                'date' => self::formatTimelineDate($record->date_captured),
                'lead' => 'Lead Time baseline',
            ],
            [
                'title' => 'Date Pending',
                'date' => self::formatTimelineDate($record->date_pending),
                'lead' => 'Lead Time: '.($record->formatLeadTime('date_pending') ?? 'No lead time'),
            ],
            [
                'title' => 'Date Ongoing',
                'date' => self::formatTimelineDate($record->date_ongoing),
                'lead' => 'Lead Time: '.($record->formatLeadTime('date_ongoing') ?? 'No lead time'),
            ],
            [
                'title' => 'Date For Further Discussion',
                'date' => self::formatTimelineDate($record->date_for_further_discussion),
                'lead' => 'Lead Time: '.($record->formatLeadTime('date_for_further_discussion') ?? 'No lead time'),
            ],
            [
                'title' => 'Counter Measure Date',
                'date' => self::formatTimelineDate($record->counter_measure_date),
                'lead' => 'Lead Time: '.($record->formatLeadTime('counter_measure_date') ?? 'No lead time'),
            ],
            [
                'title' => 'Date Resolved',
                'date' => self::formatTimelineDate($record->date_resolved),
                'lead' => 'Lead Time: '.($record->formatLeadTime('date_resolved') ?? 'No lead time'),
            ],
        ];

        $items = collect($events)
            ->filter(fn (array $event): bool => $event['date'] !== 'No date')
            ->values();

        if ($items->isEmpty()) {
            return '<p class="text-sm text-gray-500 dark:text-gray-400">No timeline milestones yet.</p>';
        }

        $html = '<ol class="esh-timeline-view">';

        foreach ($items as $item) {
            $html .= '<li class="esh-timeline-item">';
            $html .= '<div class="esh-timeline-dot" aria-hidden="true"></div>';
            $html .= '<div class="esh-timeline-card">';
            $html .= '<p class="esh-timeline-title">'.e($item['title']).'</p>';
            $html .= '<p class="esh-timeline-date">'.e($item['date']).'</p>';
            $html .= '<p class="esh-timeline-lead">'.e($item['lead']).'</p>';
            $html .= '</div>';
            $html .= '</li>';
        }

        $html .= '</ol>';

        return $html;
    }

    protected static function formatTimelineDate(mixed $value): string
    {
        if (! $value) {
            return 'No date';
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('l, F d, Y h:i A');
        }

        return (string) $value;
    }

    protected static function timelineEntry(string $name, string $label, ?string $leadTimeAttribute = null): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->dateTime('l, F d, Y h:i A')
            ->placeholder("No {$label}")
            ->helperText(fn ($record): string => $leadTimeAttribute
                ? ('Lead Time: '.($record?->formatLeadTime($leadTimeAttribute) ?? 'No lead time'))
                : 'Lead Time baseline')
            ->color('secondary');
    }

    protected static function combinedTimelineEntry(): TextEntry
    {
        return TextEntry::make('ongoing_counter_measure_timeline')
            ->label('Date Ongoing / Counter Measure Date')
            ->state(function (Observation $record): ?string {
                $timestamps = collect([
                    $record->date_ongoing
                        ? 'Ongoing: '.$record->date_ongoing->format('l, F d, Y h:i A')
                        : null,
                    $record->counter_measure_date
                        ? 'Counter Measure: '.$record->counter_measure_date->format('l, F d, Y h:i A')
                        : null,
                ])->filter()->values();

                if ($timestamps->isEmpty()) {
                    return null;
                }

                return $timestamps->implode('<br>');
            })
            ->html()
            ->placeholder('No Date Ongoing / Counter Measure Date')
            ->helperText(function (Observation $record): string {
                $leadTimes = collect([
                    $record->formatLeadTime('date_ongoing')
                        ? 'Ongoing Lead Time: '.$record->formatLeadTime('date_ongoing')
                        : null,
                    $record->formatLeadTime('counter_measure_date')
                        ? 'Counter Measure Lead Time: '.$record->formatLeadTime('counter_measure_date')
                        : null,
                ])->filter()->values();

                if ($leadTimes->isEmpty()) {
                    return 'No lead time';
                }

                return $leadTimes->implode(' | ');
            })
            ->color('secondary');
    }

    protected static function formatMultilineText(?string $text): HtmlString
    {
        if (blank($text)) {
            return new HtmlString('');
        }

        $normalized = preg_replace("/\r\n|\r|\n/", "\n", $text);
        $normalized = str_replace(['\\r\\n', '\\n', '\\r'], "\n", $normalized);
        $normalized = str_replace('\\/', '/', $normalized);
        $normalized = trim($normalized ?? '');

        return new HtmlString(nl2br(e($normalized)));
    }

}
