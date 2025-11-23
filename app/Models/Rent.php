<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rent extends Model
{

    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function locks()
    {
        return $this->hasMany(Lock::class);
    }

     public function rents()
    {
        return $this->hasMany(Rent::class,'rent_id','id');
    }
}
