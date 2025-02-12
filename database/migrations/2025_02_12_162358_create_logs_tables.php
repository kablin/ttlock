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
        Schema::create('lock_options', function (Blueprint $table) {
			$table->id();
			$table->string('option_name');
			$table->timestamps();
			$table->softDeletes();
		});


        Schema::create('lock_values', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('lock_id'); // id from table locks
            $table->bigInteger('option_id');
            $table->string('value')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('option_id')->references('id')->on('lock_options')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('lock_id')->references('id')->on('locks')->onUpdate('cascade')->onDelete('cascade');
        });


        Schema::create('lock_values_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('lock_value_id')->nullable();
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('lock_value_id')->references('id')->on('lock_values')->onUpdate('cascade')->onDelete('set null');
        });


        


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lock_options');
        Schema::dropIfExists('lock_values');
        Schema::dropIfExists('lock_values_logs');
    }
};
