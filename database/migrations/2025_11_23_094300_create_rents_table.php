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
        Schema::create('rents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->text('description')->nullable();
            $table->bigInteger('rent_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('rent_id')->references('id')->on('rents')->onUpdate('cascade')->onDelete('set null');
        });



        Schema::table('locks', function (Blueprint $table) {
            $table->bigInteger('rent_id')->nullable();
            $table->foreign('rent_id')->references('id')->on('rents')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('locks', function (Blueprint $table) {
            $table->dropColumn([
                'rent_id'
            ]);
        });


        Schema::dropIfExists('rents');
    }
};
