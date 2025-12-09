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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('user_id');
        });

        Schema::create('groupables', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('groupable_id');

            $table->string('groupable_type');

            $table->foreign('group_id')
                ->references('id')
                ->on('groups')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
        Schema::dropIfExists('groupables');
    }
};
