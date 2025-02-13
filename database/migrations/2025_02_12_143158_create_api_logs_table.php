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
        Schema::create('lock_api_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('lock_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('api_method');
            $table->json('params')->nullable();
            $table->timestamps();

            $table->foreign('lock_id')->references('id')->on('locks')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lock_api_logs');
    }
};
