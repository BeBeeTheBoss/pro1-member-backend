<?php

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
        Schema::create('spin_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('spin_wheel_chance_daily_id')
                ->nullable()
                ->constrained('spin_wheel_chances_daily')
                ->nullOnDelete();
            $table->unsignedInteger('at_max_times')->nullable();
            $table->unsignedInteger('reward_points');
            $table->timestamp('spun_at');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_records');
    }
};
