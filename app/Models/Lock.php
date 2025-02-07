<?php

namespace App\Models;



//use App\Models\LockEvent;
use App\Models\User;

//use App\Models\Lock\LockData\LockValue;
//use App\Models\Lock\LockData\LockApiLog;
use App\Services\TTLockService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
//use App\Traits\OptionValueTrait;

class Lock extends Model
{
    use HasFactory;//, OptionValueTrait;
  
    protected $guarded = [];
    
  
    public function token()
    {
        return $this->hasOne(LocksToken::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

 /*   public function credential()
    {
        return $this->belongsTo(LocksCredential::class,'user_id','user_id');
    }
*/

  /*  public function events()
    {
        return $this->hasMany(LockEvent::class, 'lock_id', 'lock_id');
    }

    public function values() 
    {
        return $this->hasMany(LockValue::class,'lock_id','id');
    }

    public function api_logs() 
    {
        return $this->hasMany(LockApiLog::class,'lock_id','id');
    }*/


}
