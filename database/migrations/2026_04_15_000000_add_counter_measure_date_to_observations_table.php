<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dateTime('counter_measure_date')->nullable();
        });

        DB::table('observations')
            ->whereNotNull('counter_measure')
            ->where('counter_measure', '!=', '')
            ->update([
                'counter_measure_date' => DB::raw('COALESCE(date_resolved, updated_at, created_at)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropColumn('counter_measure_date');
        });
    }
};
