<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['email_verified_at'] = ! empty($data['is_email_verified'])
            ? Carbon::now()
            : null;

        if (! $this->hasContributorRole($data['roles'] ?? [])) {
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
}
