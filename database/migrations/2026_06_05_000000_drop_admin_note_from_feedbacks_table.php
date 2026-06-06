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
        if (Schema::hasColumn('feedbacks', 'admin_note')) {
            Schema::table('feedbacks', function (Blueprint $table) {
                $table->dropColumn('admin_note');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('feedbacks', 'admin_note')) {
            Schema::table('feedbacks', function (Blueprint $table) {
                $table->text('admin_note')->nullable();
            });
        }
    }
};
