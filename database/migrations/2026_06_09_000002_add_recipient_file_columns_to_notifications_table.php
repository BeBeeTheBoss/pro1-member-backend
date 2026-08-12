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
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('recipient_file')->nullable()->after('image');
            $table->string('recipient_file_original_name')->nullable()->after('recipient_file');
            $table->string('recipient_file_mime_type')->nullable()->after('recipient_file_original_name');
            $table->unsignedBigInteger('recipient_file_size')->nullable()->after('recipient_file_mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_file',
                'recipient_file_original_name',
                'recipient_file_mime_type',
                'recipient_file_size',
            ]);
        });
    }
};
