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
        Schema::table('spin_wheel_chances', function (Blueprint $table) {
            $table->boolean('is_super_prize')->default(false)->after('max_times');
        });

        Schema::table('spin_wheel_chances_daily', function (Blueprint $table) {
            $table->boolean('is_super_prize')->default(false)->after('max_times');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spin_wheel_chances', function (Blueprint $table) {
            $table->dropColumn('is_super_prize');
        });

        Schema::table('spin_wheel_chances_daily', function (Blueprint $table) {
            $table->dropColumn('is_super_prize');
        });
    }
};
