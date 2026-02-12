<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NewRegistration extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('username')
                ->required()
                ->unique(),
            TextInput::make('name')
                ->required(),
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ]);
    }
}
