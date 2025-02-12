<?php

namespace App\Models\LockData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LockData\LockValue;
use Illuminate\Database\Eloquent\SoftDeletes;

class LockValuesLog extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    
    public function value() 
    {
        return $this->belongsTo(LockValue::class,'lock_value_id','id');
    }

}
