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
        Schema::table('games_event', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('start_date');
            $table->time('end_time')->nullable()->after('end_date');
            $table->boolean('all_branches')->default(true)->after('is_active');
        });

        Schema::create('games_event_branch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('games_event_id')->constrained('games_event')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['games_event_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games_event_branch');

        Schema::table('games_event', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'all_branches']);
        });
    }
};
