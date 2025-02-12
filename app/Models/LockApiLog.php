<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class LockApiLog extends Model
{
    use HasFactory;//, OptionValueTrait;
  
    protected $guarded = [];
    
    public function lock() 
    {
        return $this->belongsTo(Lock::class,'lock_id','id');
    }


}
