<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dateTime('date_pending')->nullable()->after('date_captured');
            $table->dateTime('date_ongoing')->nullable()->after('date_pending');
            $table->dateTime('date_for_further_discussion')->nullable()->after('date_ongoing');
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropColumn([
                'date_pending',
                'date_ongoing',
                'date_for_further_discussion',
            ]);
        });
    }
};
