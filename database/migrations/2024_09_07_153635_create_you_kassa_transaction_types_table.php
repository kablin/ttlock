<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\YouKassaTransactionType;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('you_kassa_transaction_types', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->timestamps();
    });



    YouKassaTransactionType::create([
      'name' => 'Ожидание платежа'
    ]);
    YouKassaTransactionType::create([
      'name' => 'Ожидание подтверждения платежа'
    ]);
    YouKassaTransactionType::create([
      'name' => 'Успешный платеж'
    ]);
    YouKassaTransactionType::create([
      'name' => 'Отмена платежа'
    ]);
    YouKassaTransactionType::create([
      'name' => 'Ожидание возврата'
    ]);
    YouKassaTransactionType::create([
      'name' => 'Успешный возврат'
    ]);
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('you_kassa_transaction_types');
  }
};
