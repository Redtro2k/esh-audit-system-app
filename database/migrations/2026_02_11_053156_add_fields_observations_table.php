<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->string('concern_type')->after('target_date')->comment('Type of concern reported');
        });
    }
    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropColumn('concern_type');
        });
    }
};
