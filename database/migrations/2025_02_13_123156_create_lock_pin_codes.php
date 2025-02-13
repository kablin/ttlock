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
        Schema::create('lock_pin_codes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('lock_id');
            $table->boolean('is_load')->default(false);
            $table->string('pin_code')->nullable();
            $table->bigInteger('pin_code_id')->nullable();
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            $table->timestamp('start_local')->nullable();
	        $table->timestamp('end_local')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lock_pin_codes');
    }
};
