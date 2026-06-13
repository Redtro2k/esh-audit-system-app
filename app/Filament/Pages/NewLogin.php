<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

class NewLogin extends BaseLogin
{
    private const LAST_LOGIN_PROFILE_COOKIE = 'esh_last_login_profile';

    protected string $view = 'filament.pages.new-login';

    protected Width|string|null $maxContentWidth = Width::Full;

    public ?array $rememberedLoginProfile = null;

    public function mount(): void
    {
        parent::mount();

        $this->rememberedLoginProfile = $this->resolveRememberedLoginProfile();

        if (filled($this->rememberedLoginProfile['username'] ?? null)) {
            $this->form->fill([
                'login' => $this->rememberedLoginProfile['username'],
                'remember' => true,
            ]);
        }
    }

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
            ->required(fn (): bool => ! $this->hasRememberedLoginProfile())
            ->hidden(fn (): bool => $this->hasRememberedLoginProfile())
            ->dehydrated()
            ->autofocus(fn (): bool => ! $this->hasRememberedLoginProfile())
            ->autocomplete('username')
            ->placeholder('Enter your username')
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->placeholder('Enter your password')
            ->extraInputAttributes(['tabindex' => $this->hasRememberedLoginProfile() ? 1 : 2]);
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->hidden(fn (): bool => $this->hasRememberedLoginProfile())
            ->dehydrated()
            ->extraInputAttributes(['tabindex' => $this->hasRememberedLoginProfile() ? 2 : 3]);
    }

    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->label($this->hasRememberedLoginProfile() ? 'Continue' : __('filament-panels::auth/pages/login.form.actions.authenticate.label'));
    }

    public function useAnotherProfile(): void
    {
        Cookie::queue(Cookie::forget(self::LAST_LOGIN_PROFILE_COOKIE));

        $this->rememberedLoginProfile = null;

        $this->form->fill([
            'login' => null,
            'password' => null,
            'remember' => false,
        ]);
    }

    public function hasRememberedLoginProfile(): bool
    {
        return filled($this->rememberedLoginProfile['username'] ?? null);
    }

    protected function resolveRememberedLoginProfile(): ?array
    {
        $cookie = request()->cookie(self::LAST_LOGIN_PROFILE_COOKIE);

        if (blank($cookie)) {
            return null;
        }

        $profile = json_decode((string) $cookie, true);

        if (! is_array($profile) || blank($profile['username'] ?? null)) {
            return null;
        }

        return [
            'name' => trim((string) ($profile['name'] ?? $profile['username'])),
            'username' => trim((string) $profile['username']),
            'avatar_url' => filled($profile['avatar_url'] ?? null) ? (string) $profile['avatar_url'] : null,
            'last_login_at' => filled($profile['last_login_at'] ?? null) ? (string) $profile['last_login_at'] : null,
        ];
    }

    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        $login = trim((string) ($data['login'] ?? ''));

        if (blank($login) && $this->hasRememberedLoginProfile()) {
            $login = trim((string) $this->rememberedLoginProfile['username']);
        }

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
