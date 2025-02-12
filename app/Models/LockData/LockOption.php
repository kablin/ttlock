<?php

namespace App\Models\LockData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LockOption extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    
}
