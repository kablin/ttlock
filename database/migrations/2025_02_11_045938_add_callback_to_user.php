<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**  172.31.0.2/api/callback
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('callback')->default('');
        });

        Schema::table('lock_jobs', function (Blueprint $table) {
            $table->json('data')->nullable();
            $table->string('task')->nullable();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('callback');
        });

        Schema::table('lock_jobs', function (Blueprint $table) {
            $table->dropColumn('data');
            $table->dropColumn('task');
        });
    }
};
