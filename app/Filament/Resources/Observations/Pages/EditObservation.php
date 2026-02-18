<?php

namespace App\Filament\Resources\Observations\Pages;

use App\Filament\Resources\Observations\ObservationResource;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForResolved;


class EditObservation extends EditRecord
{
    protected static string $resource = ObservationResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if(auth()->user()->hasRole('remediator')) {
            $data['date_captured'] = Carbon::now('Asia/Manila');
            $data['status'] = 'ongoing';
        }
        if(auth()->user()->hasRole('auditor') && strtolower($data['status']) === 'resolved') {
            $data['date_resolved'] = Carbon::now('Asia/Manila');
        }
        return $data;
    }

    protected function afterSave(): void
    {
        if($this->getRecord()->wasChanged('status')) {
            switch (strtolower($this->getRecord()->status)) {
                case 'for further discussion':
                     Mail::to($this->getRecord()->pic->email)->queue(new \App\Mail\ForFutherDiscussion($this->getRecord()));
                     break;
                case 'resolved':
                    Mail::to($this->getRecord()->pic->email)->queue(new \App\Mail\ForResolved($this->getRecord()));
                    break;
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()->hidden(auth()->user()->hasRole('remediator')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
