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
        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('is_merged')->default(false)->after('additional_files');
            $table->foreignId('merged_into_contact_id')->nullable()->after('is_merged')
                ->constrained('contacts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['merged_into_contact_id']);
            $table->dropColumn(['is_merged', 'merged_into_contact_id']);
        });
    }
};
