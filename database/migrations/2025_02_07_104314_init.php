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
        Schema::create('locks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('lock_id');
            $table->string('lock_name');
            $table->string('lock_alias')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('electric_quantity')->default(0);
            $table->string('no_key_pwd')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('locks_credentials', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('login');
            $table->string('password');

            $table->timestamps();
        });



        Schema::create('locks_tokens', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('credential_id');
            $table->string('access_token');
            $table->string('uid')->nullable();
            $table->dateTime('expires_in');
            $table->string('refresh_token');
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locks');
        Schema::dropIfExists('locks_credentials');
        Schema::dropIfExists('locks_tokens');
    }
};
