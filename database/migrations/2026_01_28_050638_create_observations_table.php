<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->string('area');
            $table->foreignIdFor(User::class, 'pic_id');
            $table->enum('status', ['pending', 'ongoing', 'for further discussion', 'resolved'])->default('pending');
            $table->datetime('target_date');
            $table->string('concern');
            $table->string('counter_measure')->nullable();
            $table->foreignIdFor(User::class, 'auditor_id');
            $table->dateTime('date_captured')->nullable();
            $table->dateTime('date_resolved')->nullable();
            $table->text('remarks')->nullable();
            $table->json('capture_concern')->nullable();
            $table->json('capture_solved')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};
