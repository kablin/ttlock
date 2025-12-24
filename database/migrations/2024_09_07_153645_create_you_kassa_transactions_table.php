<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('you_kassa_transactions', function (Blueprint $table) {
      $table->id();
      $table->boolean('test')->default(false);
      $table->foreignId('user_id')->nullable()->constrained();
      $table->foreignId('you_kassa_transaction_types_id')->nullable()->constrained();
      $table->integer('amount')->nullable();
      $table->string('payment_id')->nullable();
      $table->string('payment_type')->nullable();
      $table->longText('metadata')->nullable();
      $table->string('card_type')->nullable();
      $table->string('issuer_country')->nullable();
      $table->string('issuer_name')->nullable();
      $table->double('income_amount',8,2)->nullable()->comment('Сумма платежа, которую получит магазин, — значение amount за вычетом комиссии ЮKassa');
      $table->string('income_amount_currency')->nullable()->comment('Валюта');
      $table->double('refunded_amount',8,2)->nullable()->comment('Сумма, которая вернулась пользователю. Присутствует, если у этого платежа есть успешные возвраты.');
      $table->string('refunded_amount_currency')->nullable()->comment('Валюта');
      $table->timestampTz('expires_at')->nullable()->comment('Время, до которого вы можете бесплатно отменить или подтвердить платеж. В указанное время платеж в статусе waiting_for_capture будет автоматически отменен');
      $table->timestampsTz();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('you_kassa_transactions');
  }
};
