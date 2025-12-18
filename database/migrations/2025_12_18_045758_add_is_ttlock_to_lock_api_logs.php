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
        Schema::table('lock_api_logs', function (Blueprint $table) {
            $table->boolean('is_ttlock_result')->nullable()->default(true);
            $table->string('ip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lock_api_logs', function (Blueprint $table) {
            $table->dropColumn([
                'is_ttlock_result','ip',

            ]);
        });
    }
};
