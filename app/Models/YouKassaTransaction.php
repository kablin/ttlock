<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YouKassaTransaction extends Model
{
  use HasFactory;

  public $guarded = [];

  public function youKassaTransactionType()
  {
    return $this->belongsTo(YouKassaTransactionType::class);
  }

}
