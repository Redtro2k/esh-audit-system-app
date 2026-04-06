<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Kirschbaum\Commentions\Contracts\Commenter;


class User extends Authenticatable implements HasAvatar, MustVerifyEmail, Commenter
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;
    protected $fillable = [
        'name',
        'email',
        'avatar_url',
        'password',
        'department',
        'department_id',
        'team_id',
        'username'
    ];
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->resolveAvatarUrl();
    }

    public function getCommenterAvatar(): ?string
    {
        return $this->resolveAvatarUrl();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function dealers(): BelongsToMany
    {
        return $this->belongsToMany(Dealer::class)->withTimestamps();
    }

    protected function resolveAvatarUrl(): ?string
    {
        if (! $this->avatar_url) {
            return $this->defaultAvatarUrl();
        }

        if (Str::startsWith($this->avatar_url, ['http://', 'https://'])) {
            return $this->avatar_url;
        }

        return Storage::disk('public')->url($this->avatar_url);
    }

    protected function defaultAvatarUrl(): string
    {
        $name = trim((string) $this->name) !== '' ? $this->name : $this->username;

        return 'https://ui-avatars.com/api/?name=' . urlencode((string) $name) . '&background=E5E7EB&color=111827';
    }
}
