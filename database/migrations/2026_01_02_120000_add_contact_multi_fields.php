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
            if (!Schema::hasColumn('contacts', 'phone_numbers')) {
                $table->json('phone_numbers')->nullable()->after('phone_number');
            }

            if (!Schema::hasColumn('contacts', 'emails')) {
                $table->json('emails')->nullable()->after('email');
            }
        });

        // Migrate existing single values into arrays
        try {
            \DB::table('contacts')->get()->each(function ($c) {
                $phones = [];
                if (!empty($c->phone_number)) {
                    $phones[] = (string)$c->phone_number;
                }
                \DB::table('contacts')->where('id', $c->id)->update(['phone_numbers' => json_encode($phones)]);

                $emails = [];
                if (!empty($c->email)) {
                    $emails[] = $c->email;
                }
                \DB::table('contacts')->where('id', $c->id)->update(['emails' => json_encode($emails)]);
            });
        } catch (\Exception $e) {
            // ignore during migration if DB not reachable in some contexts
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'phone_numbers')) {
                $table->dropColumn('phone_numbers');
            }
            if (Schema::hasColumn('contacts', 'emails')) {
                $table->dropColumn('emails');
            }
        });
    }
};