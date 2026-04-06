<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'dealer_id') && Schema::hasTable('dealer_user')) {
            $now = now();

            $rows = DB::table('users')
                ->select('dealer_id', 'id as user_id')
                ->whereNotNull('dealer_id')
                ->get()
                ->map(fn ($row): array => [
                    'dealer_id' => $row->dealer_id,
                    'user_id' => $row->user_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            if ($rows !== []) {
                DB::table('dealer_user')->upsert(
                    $rows,
                    ['dealer_id', 'user_id'],
                    ['updated_at']
                );
            }
        }

        if (Schema::hasColumn('users', 'dealer_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('dealer_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'dealer_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('dealer_id')
                    ->nullable()
                    ->after('department_id')
                    ->constrained('dealers');
            });
        }

        if (Schema::hasTable('dealer_user')) {
            $assignments = DB::table('dealer_user')
                ->select('user_id', DB::raw('MIN(dealer_id) as dealer_id'))
                ->groupBy('user_id')
                ->get();

            foreach ($assignments as $assignment) {
                DB::table('users')
                    ->where('id', $assignment->user_id)
                    ->update(['dealer_id' => $assignment->dealer_id]);
            }
        }
    }
};
