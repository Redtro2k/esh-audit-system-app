<?php

namespace App\Filament\Resources\Observations\Pages;

use App\Filament\Resources\Observations\ObservationResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Observation;
use Illuminate\Support\Facades\Mail;

class CreateObservation extends CreateRecord
{
    protected static string $resource = ObservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['auditor_id'] = auth()->id();
        $data['status'] = 'pending';

        if(auth()->user()->hasRole('remediator')) {
            $data['date_captured'] = now();
                $data['status'] = 'ongoing';
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        $observation = Observation::with('pic', 'auditor', 'pic.department')->find($this->record->id);
        Mail::to($observation->pic->email)->queue(new \App\Mail\SendObservation($observation));
    }
}
