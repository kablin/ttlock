<?php

namespace App\Models;



//use App\Models\LockEvent;
use App\Models\User;

use App\Models\LockData\LockValue;
use App\Models\LockApiLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\OptionValueTrait;
use App\Models\LockEvent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lock extends Model
{
    use HasFactory, OptionValueTrait, SoftDeletes;
  
    protected $guarded = [];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }

 /*   public function credential()
    {
        return $this->belongsTo(LocksCredential::class,'user_id','user_id');
    }
*/

    public function events()
    {
        return $this->hasMany(LockEvent::class, 'lock_id', 'lock_id');
    }

    public function pincodes()
    {
        return $this->hasMany(LockPinCode::class);
    }


    public function allpincodes()
    {
        return $this->hasMany(LockPinCode::class)->withTrashed();
    }


    public function values() 
    {
        return $this->hasMany(LockValue::class,'lock_id','id');
    }

    public function api_logs() 
    {
        return $this->hasMany(LockApiLog::class,'lock_id','id');
    }




}
