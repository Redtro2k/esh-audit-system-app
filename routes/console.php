<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('users:verify-email {identifier? : User email or username} {--all : Verify all unverified users}', function (?string $identifier = null) {
    if (blank($identifier) && ! $this->option('all')) {
        $this->error('Provide an email/username or use --all.');
        $this->line('Examples:');
        $this->line('  php artisan users:verify-email marish.staana@toyotanorthedsaservicecenter.com.ph');
        $this->line('  php artisan users:verify-email marish');
        $this->line('  php artisan users:verify-email --all');

        return self::FAILURE;
    }

    $query = User::query()->whereNull('email_verified_at');

    if (filled($identifier)) {
        $query->where(fn ($userQuery) => $userQuery
            ->where('email', $identifier)
            ->orWhere('username', $identifier));
    }

    $users = $query->get();

    if ($users->isEmpty()) {
        $this->info('No unverified users found.');

        return self::SUCCESS;
    }

    $verifiedAt = now();

    $users->each->forceFill([
        'email_verified_at' => $verifiedAt,
    ])->each->save();

    $this->info("Verified {$users->count()} user email(s).");

    $users->each(fn (User $user) => $this->line("- {$user->username} <{$user->email}>"));

    return self::SUCCESS;
})->purpose('Mark one user, or all unverified users, as email verified');
