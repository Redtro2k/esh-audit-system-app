<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'username')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        DB::table('users')
            ->select(['id', 'name', 'email', 'username'])
            ->orderBy('id')
            ->get()
            ->each(function (object $user): void {
                if (filled($user->username)) {
                    return;
                }

                $baseUsername = Str::of((string) ($user->email ?: $user->name ?: 'user'))
                    ->before('@')
                    ->lower()
                    ->replaceMatches('/[^a-z0-9]+/', '')
                    ->value();

                $baseUsername = $baseUsername !== '' ? $baseUsername : 'user';
                $username = $baseUsername;
                $suffix = 1;

                while (
                    DB::table('users')
                        ->where('username', $username)
                        ->where('id', '!=', $user->id)
                        ->exists()
                ) {
                    $username = $baseUsername . $suffix;
                    $suffix++;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $username]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        //
    }
};
