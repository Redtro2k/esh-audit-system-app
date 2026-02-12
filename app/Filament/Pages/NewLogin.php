<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Components\Component;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

class NewLogin extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                $this->getLoginFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent()
            ]);
    }
    public function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->required()
            ->autofocus()
            ->autocomplete()
            ->placeholder('Enter your username or email address')
            ->extraInputAttributes(['tabindex' => 1]);
    }
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        $login_type = filter_var($data['login'], FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        return [
            $login_type => $data['login'],
            'password' => $data['password'],
        ];
    }
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }

    protected function throwPermissionDeniedValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('You do not have permission to access this panel.'),
        ]);
    }
}
