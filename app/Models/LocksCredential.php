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
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    

    private function isValidMd5($md5 = '') {
        return strlen($md5) == 32 && ctype_xdigit($md5);
    }
}
