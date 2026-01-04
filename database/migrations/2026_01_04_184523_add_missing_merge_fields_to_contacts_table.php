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
            if (!Schema::hasColumn('contacts', 'is_merged')) {
                $table->boolean('is_merged')->default(false)->after('additional_files');
            }
            if (!Schema::hasColumn('contacts', 'merged_into_contact_id')) {
                $table->unsignedBigInteger('merged_into_contact_id')->nullable()->after('is_merged');
                $table->foreign('merged_into_contact_id')->references('id')->on('contacts')->onDelete('set null');
            }
            if (!Schema::hasColumn('contacts', 'merge_history')) {
                $table->json('merge_history')->nullable()->after('merged_into_contact_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'merge_history')) {
                $table->dropColumn('merge_history');
            }
            if (Schema::hasColumn('contacts', 'merged_into_contact_id')) {
                $table->dropForeign(['merged_into_contact_id']);
                $table->dropColumn('merged_into_contact_id');
            }
            if (Schema::hasColumn('contacts', 'is_merged')) {
                $table->dropColumn('is_merged');
            }
        });
    }
};
