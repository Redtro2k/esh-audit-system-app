<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class Profile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_url')
                    ->label('Profile Picture')
                    ->disk('public')
                    ->directory('avatars')
                    ->image()
                    ->avatar()
                    ->imageEditor()
                    ->nullable(),
                Placeholder::make('department_display')
                    ->label('Department')
                    ->content(function (): string {
                        /** @var User|null $user */
                        $user = auth()->user();

                        return $user?->department?->name ?? 'No department assigned';
                    }),
                Placeholder::make('dealer_display')
                    ->label('Dealer')
                    ->content(function (): string {
                        /** @var User|null $user */
                        $user = auth()->user();

                        $dealerNames = $user?->dealers()
                            ->orderBy('name')
                            ->pluck('name')
                            ->all() ?? [];

                        return count($dealerNames) > 0
                            ? implode(', ', $dealerNames)
                            : 'No dealer assigned';
                    }),
                TextInput::make('username')
                    ->required()
                    ->unique(ignoreRecord: true),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
