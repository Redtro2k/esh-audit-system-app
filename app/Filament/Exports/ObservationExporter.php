<?php

namespace App\Filament\Exports;

use App\Models\Observation;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class ObservationExporter extends Exporter
{
    protected static ?string $model = Observation::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('pic.name')->label('Observer Name'),
            ExportColumn::make('pic.department.name')->label('Observer Department'),
            ExportColumn::make('dealer.name')->label('Dealer'),
            ExportColumn::make('area')->label('Audit Area'),
            ExportColumn::make('concernType.name')->label('Concern Type'),
            ExportColumn::make('concern')->label('Concern'),
            ExportColumn::make('counter_measure')->label('Counter Measure'),
            ExportColumn::make('target_date')
                ->label('Target Date')
                ->formatStateUsing(fn ($state): ?string => self::formatDateTime($state)),
            ExportColumn::make('date_captured')
                ->label('Date Captured')
                ->formatStateUsing(fn ($state): ?string => self::formatDateTime($state)),
            ExportColumn::make('date_pending')
                ->label('Date Pending')
                ->formatStateUsing(fn ($state): ?string => self::formatDateTime($state)),
            self::leadTimeColumn('pending_lead_time', 'Pending Lead Time', 'date_pending'),
            ExportColumn::make('date_ongoing')
                ->label('Date Ongoing')
                ->formatStateUsing(fn ($state): ?string => self::formatDateTime($state)),
            self::leadTimeColumn('ongoing_lead_time', 'Ongoing Lead Time', 'date_ongoing'),
            ExportColumn::make('date_for_further_discussion')
                ->label('Date For Further Discussion')
                ->formatStateUsing(fn ($state): ?string => self::formatDateTime($state)),
            self::leadTimeColumn('discussion_lead_time', 'Discussion Lead Time', 'date_for_further_discussion'),
            ExportColumn::make('counter_measure_date')
                ->label('Counter Measure Date')
                ->formatStateUsing(fn ($state): ?string => self::formatDateTime($state)),
            self::leadTimeColumn('counter_measure_lead_time', 'Counter Measure Lead Time', 'counter_measure_date'),
            ExportColumn::make('date_resolved')
                ->label('Date Resolved')
                ->formatStateUsing(fn ($state): ?string => self::formatDateTime($state)),
            self::leadTimeColumn('resolved_lead_time', 'Resolved Lead Time', 'date_resolved'),
            ExportColumn::make('remarks')->label('Remarks'),
            ExportColumn::make('auditor.name')->label('Auditor'),
            ExportColumn::make('created_at')
                ->label('Created At')
                ->formatStateUsing(fn ($state): ?string => self::formatDateTime($state)),
        ];
    }

    protected static function formatDateTime(mixed $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        return Carbon::parse($state)->format('Y-m-d H:i:s');
    }

    protected static function leadTimeColumn(string $name, string $label, string $attribute): ExportColumn
    {
        return ExportColumn::make($name)
            ->label($label)
            ->state(fn (Observation $record): ?string => $record->formatLeadTime($attribute));
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your observation export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
