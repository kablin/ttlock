<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocksCredential extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = ($this->isValidMd5($value)) ? $value : md5($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function token()
    {
        return $this->hasOne(LocksToken::class,'credential_id');
    }

    private function isValidMd5($md5 = '') {
        return strlen($md5) == 32 && ctype_xdigit($md5);
    }
}
