<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Group extends Model
{

    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function rents(): MorphToMany
    {
        return $this->morphedByMany(Rent::class, 'groupable');
    }

    /**
     * Получить все видео, которым присвоен этот тег.
     */
    public function locks(): MorphToMany
    {
        return $this->morphedByMany(Lock::class, 'groupable');
    }
}
