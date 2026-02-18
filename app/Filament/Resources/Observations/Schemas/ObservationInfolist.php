<?php

namespace App\Filament\Resources\Observations\Schemas;

use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class ObservationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                Section::make('Audit')
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
                                    ->color('primary'),
                               TextEntry::make('concern')
                                    ->label('Concern / Remarks')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                            ]),
                        TextEntry::make('auditor.name')->label('Auditor Name'),
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
                    ]),
                Section::make('Response')
                    ->icon(LucideIcon::ClipboardSignature)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->description('Provides management’s formal response to the audit finding, outlining corrective actions, timelines, and accountability.')
                    ->columns()
                    ->schema([
                        Grid::make(3)
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
                                    ->label('Audit Area')
                            ])
                        ->columnSpanFull(),
                        TextEntry::make('remarks')
                            ->placeholder('No Remarks'),
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
                    ]),
            ]);
    }
}
