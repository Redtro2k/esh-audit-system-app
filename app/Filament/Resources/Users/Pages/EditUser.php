<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (UserResource::canAssignTeams() && ! UserResource::canManageUsers()) {
            return [
                'team_id' => Arr::get($data, 'team_id'),
            ];
        }

        $data['email_verified_at'] = ! empty($data['is_email_verified'])
            ? ($this->getRecord()->email_verified_at ?? Carbon::now())
            : null;

        $roles = $data['roles'] ?? $this->getRecord()->roles->modelKeys();

        if (! $this->hasContributorRole($roles)) {
            $data['team_id'] = null;
        }

        unset($data['is_email_verified']);

        return $data;
    }

    protected function hasContributorRole(array $roles): bool
    {
        $normalizedRoles = collect($roles)
            ->filter(fn ($role) => filled($role))
            ->map(fn ($role) => is_string($role) ? strtolower(trim($role)) : $role)
            ->values();

        if ($normalizedRoles->contains('contributor')) {
            return true;
        }

        return \Spatie\Permission\Models\Role::query()
            ->where('name', 'contributor')
            ->whereIn('id', $normalizedRoles->map(fn ($role) => (int) $role)->all())
            ->exists();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn (): bool => ! UserResource::canManageUsers() || auth()->id() === $this->getRecord()->getKey()),
        ];
    }
}
