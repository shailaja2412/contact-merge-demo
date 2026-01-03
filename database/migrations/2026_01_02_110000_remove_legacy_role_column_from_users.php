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
        Schema::table('users', function (Blueprint $table) {
            // remove legacy `role` column if present (and any FK constraint)
            if (Schema::hasColumn('users', 'role')) {
                try {
                    $table->dropForeign(['role']);
                } catch (\Exception $e) {
                    // ignore if constraint doesn't exist
                }

                try {
                    $table->dropColumn('role');
                } catch (\Exception $e) {
                    // ignore if it can't be dropped
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->unsignedBigInteger('role')->nullable();

                try {
                    $table->foreign('role')->references('id')->on('roles');
                } catch (\Exception $e) {
                    // ignore
                }
            }
        });
    }
};
