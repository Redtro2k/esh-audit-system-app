<?php

namespace App\Filament\Resources\Observations\Schemas;

use App\Models\ConcernCategory;
use App\Models\User;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;

class ObservationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::auditSection(),
                self::responseSection(),
                self::commentsSection(),
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
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->color('primary'),
                    ]),
                TextEntry::make('auditor.name')->label('Auditor Name'),
                TextEntry::make('dealer.name')->label('Dealer'),
                TextEntry::make('target_date')->label('Target Date')->dateTime('l, F d, Y h:i A'),
                TextEntry::make('status')
                    ->size(TextSize::Large)
                    ->weight(FontWeight::Bold)
                    ->badge(),
                ImageEntry::make('capture_concern')
                    ->extraAttributes([
                        'alt' => 'Logo',
                        'loading' => 'lazy',
                    ])
                    ->imageGallery()
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
                    ->html(),
                TextEntry::make('counter_measure')
                    ->placeholder('No Counter Measure')
                    ->label('Counter Measure')
                    ->html(),
                TextEntry::make('date_captured')
                    ->label('Date Captured')
                    ->dateTime('l, F d, Y h:i A')
                    ->placeholder('No Date Captured'),
                TextEntry::make('date_resolved')
                    ->label('Date Resolved')
                    ->dateTime('l, F d, Y h:i A')
                    ->icon(LucideIcon::CheckCheck)
                    ->iconColor('primary')
                    ->size(TextSize::Large)
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->placeholder('No Date Resolved Captured'),
                ImageEntry::make('capture_solved')
                    ->extraAttributes([
                        'alt' => 'Logo',
                        'loading' => 'lazy',
                    ])
                    ->placeholder('No Photo')
                    ->imageGallery()
                    ->ring(5)
                    ->label('Proof Solved'),
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
}
