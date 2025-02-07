<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocksToken extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expires_in' => 'datetime'
    ];

    public function credential()
    {
        return $this->hasOne(LocksCredential::class, 'id', 'credential_id');
    }
}
