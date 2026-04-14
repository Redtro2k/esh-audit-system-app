<?php

namespace App\Filament\Resources\Observations\Pages;

use App\Filament\Resources\Observations\ObservationResource;
use App\Models\Observation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateObservation extends CreateRecord
{
    protected static string $resource = ObservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['auditor_id'] = auth()->id();
        $data['status'] = 'pending';

        if (auth()->user()->hasAnyRole(['auditor', 'contributor']) && blank($data['dealer_id'] ?? null)) {
            $data['dealer_id'] = auth()->user()?->dealers()->orderBy('dealers.name')->value('dealers.id');
        }

        if (! auth()->user()->hasAnyRole(['remediator', 'representative'])) {
            $data['target_date'] = null;
        }

        if (auth()->user()->hasAnyRole(['remediator', 'representative'])) {
            $data['date_captured'] = now();
            $data['status'] = 'ongoing';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $observation = Observation::with('pic', 'auditor', 'pic.department')->find($this->record->id);
        Mail::to($observation->pic->email)->send(new \App\Mail\SendObservation($observation));
    }
}
