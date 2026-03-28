<?php

namespace App\Filament\Resources\Observations\Pages;

use App\Filament\Resources\Observations\ObservationResource;
use App\Filament\Resources\Observations\Schemas\ObservationInfolist;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForResolved;


class EditObservation extends EditRecord
{
    protected static string $resource = ObservationResource::class;

    public function defaultInfolist(Schema $schema): Schema
    {
        if (! $schema->hasCustomColumns()) {
            $schema->columns($this->hasInlineLabels() ? 1 : 2);
        }

        return $schema
            ->inlineLabel($this->hasInlineLabels())
            ->record($this->getRecord());
    }

    public function infolist(Schema $schema): Schema
    {
        return ObservationInfolist::configureForEdit($schema);
    }

    public function content(Schema $schema): Schema
    {
        if ($this->shouldShowObservationInfolist()) {
            return $schema->components([
                EmbeddedSchema::make('infolist'),
                $this->getFormContentComponent(),
                $this->getRelationManagersContentComponent(),
            ]);
        }

        return parent::content($schema);
    }

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
                     Mail::to($this->getRecord()->pic->email)->send(new \App\Mail\ForFutherDiscussion($this->getRecord()));
                     break;
                case 'resolved':
                    Mail::to($this->getRecord()->pic->email)->send(new \App\Mail\ForResolved($this->getRecord()));
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

    protected function shouldShowObservationInfolist(): bool
    {
        return ! auth()->user()->hasRole('auditor');
    }
}
