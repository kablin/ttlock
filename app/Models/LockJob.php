<?php

namespace App\Models;


;
use App\Models\User;


use App\Services\TTLockService;
//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

use Illuminate\Support\Str;

class LockJob extends Model
{
   
  
    protected $guarded = [];
    
  
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lockjob) {
            $lockjob->job_id = (string) Str::uuid();
        });
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
