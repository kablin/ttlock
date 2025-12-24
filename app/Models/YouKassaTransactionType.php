<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YouKassaTransactionType extends Model
{
  use HasFactory;

  public $guarded = [];

  public const PAYMENT_PENDING = 1;
  public const PAYMENT_WAITING_FOR_CAPTURE = 2;
  public const PAYMENT_SUCCEEDED = 3;
  public const PAYMENT_CANCELED = 4;
  public const REFUND_PENDING = 5;
  public const REFUND_SUCCEEDED = 6;

  public function youKassaTransactions()
  {
    return $this->hasMany(YouKassaTransaction::class);
  }
}
