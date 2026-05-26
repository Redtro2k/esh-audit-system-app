<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Components\Component;
use Filament\Support\Enums\Width;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

class NewLogin extends BaseLogin
{
    protected string $view = 'filament.pages.new-login';

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getView(): string
    {
        return 'filament.pages.new-login';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                $this->getLoginFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    public function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('Username')
            ->required()
            ->autofocus()
            ->autocomplete('username')
            ->placeholder('Enter your username')
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        $login = trim((string) $data['login']);

        $login_type = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        return [
            $login_type => $login,
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
