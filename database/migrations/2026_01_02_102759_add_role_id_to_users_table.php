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
            // ensure a single, well-named foreign key column
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            }

            // in case an unintended `role` column/fk exists from a previous migration, remove it
            if (Schema::hasColumn('users', 'role')) {
                // drop foreign key if exists (constraint name could vary)
                try {
                    $table->dropForeign(['role']);
                } catch (\Exception $e) {
                    // ignore if constraint doesn't exist
                }

                try {
                    $table->dropColumn('role');
                } catch (\Exception $e) {
                    // ignore if column doesn't exist or already removed
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
            // drop role_id foreign and column if present
            if (Schema::hasColumn('users', 'role_id')) {
                try {
                    $table->dropForeign(['role_id']);
                } catch (\Exception $e) {
                    // ignore if constraint name differs or doesn't exist
                }

                try {
                    $table->dropColumn('role_id');
                } catch (\Exception $e) {
                    // ignore
                }
            }

            // also defensively drop any stray `role` column/foreign
            if (Schema::hasColumn('users', 'role')) {
                try {
                    $table->dropForeign(['role']);
                } catch (\Exception $e) {
                }

                try {
                    $table->dropColumn('role');
                } catch (\Exception $e) {
                }
            }
        });
    }
};
