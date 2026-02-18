<?php

namespace App\Filament\Exports;

use App\Models\Observation;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ObservationExporter extends Exporter
{
    protected static ?string $model = Observation::class;

    public static function getColumns(): array
    {
        return [
            //
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('pic.name')->label('Observer Name'),
            ExportColumn::make('pic.department.name')->label('Observer Department'),
            ExportColumn::make('area')->label('Audit Area'),
            ExportColumn::make('concernType.name')->label('Concern Type'),
            ExportColumn::make('concern')->label('Concern'),
            ExportColumn::make('counter_measure')->label('Counter Measure'),
            ExportColumn::make('target_date')->label('Target Date'),
            ExportColumn::make('date_resolved')->label('Date Resolved'),
            ExportColumn::make('remarks')->label('Remarks'),
            ExportColumn::make('auditor.name')->label('Auditor'),
            ExportColumn::make('created_at')->label('Created At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your observation export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
