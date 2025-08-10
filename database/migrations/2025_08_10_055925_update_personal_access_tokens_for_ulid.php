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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Drop the existing morphs columns
            $table->dropMorphs('tokenable');

            // Add new ULID-compatible columns
            $table->string('tokenable_id', 26)->index();
            $table->string('tokenable_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Drop the ULID columns
            $table->dropColumn(['tokenable_id', 'tokenable_type']);

            // Restore the original morphs
            $table->morphs('tokenable');
        });
    }
};
