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
        Schema::create('lock_events', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('record_id')->nullable();
            $table->integer('lock_id')->nullable();
            $table->integer('record_type_from_lock')->nullable();
            $table->integer('record_type')->nullable();
            $table->integer('success')->nullable();
            $table->string('username')->nullable();
            $table->string('keyboard_pwd')->nullable();
            $table->bigInteger('lock_date')->nullable();
            $table->bigInteger('server_date')->nullable();
            $table->boolean('is_webhook')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lock_events');
    }
};
