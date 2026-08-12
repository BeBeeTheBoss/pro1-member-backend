<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('spin_wheel_chances', function (Blueprint $table) {
            $table->string('type')->default('other')->after('max_times');
        });

        Schema::table('spin_wheel_chances_daily', function (Blueprint $table) {
            $table->string('type')->default('other')->after('max_times');
        });

        if (Schema::hasColumn('spin_wheel_chances', 'is_super_prize')) {
            DB::table('spin_wheel_chances')
                ->where('is_super_prize', true)
                ->update(['type' => 'super_prize']);

            DB::table('spin_wheel_chances')
                ->where('is_super_prize', false)
                ->update(['type' => 'fix_prize']);

            Schema::table('spin_wheel_chances', function (Blueprint $table) {
                $table->dropColumn('is_super_prize');
            });
        }

        if (Schema::hasColumn('spin_wheel_chances_daily', 'is_super_prize')) {
            DB::table('spin_wheel_chances_daily')
                ->where('is_super_prize', true)
                ->update(['type' => 'super_prize']);

            DB::table('spin_wheel_chances_daily')
                ->where('is_super_prize', false)
                ->update(['type' => 'fix_prize']);

            Schema::table('spin_wheel_chances_daily', function (Blueprint $table) {
                $table->dropColumn('is_super_prize');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spin_wheel_chances', function (Blueprint $table) {
            $table->boolean('is_super_prize')->default(false)->after('max_times');
        });

        Schema::table('spin_wheel_chances_daily', function (Blueprint $table) {
            $table->boolean('is_super_prize')->default(false)->after('max_times');
        });

        DB::table('spin_wheel_chances')
            ->where('type', 'super_prize')
            ->update(['is_super_prize' => true]);

        DB::table('spin_wheel_chances_daily')
            ->where('type', 'super_prize')
            ->update(['is_super_prize' => true]);

        Schema::table('spin_wheel_chances', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('spin_wheel_chances_daily', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
