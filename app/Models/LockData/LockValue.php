<?php

namespace App\Models\LockData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LockData\LockOption;
use App\Models\LockData\LockValuesLog;
use App\Models\Lock;
use Illuminate\Database\Eloquent\SoftDeletes;


class LockValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];


    protected static function boot()
    {
        parent::boot();

        static::saved(function (LockValue $lockValue) {
            if ($lockValue->isDirty('value'))
            {
                $lockValue->logs()->create([
                    'old_value' => array_key_exists('value',$lockValue->getOriginal()) ? $lockValue->getOriginal()['value'] :null,
                    'new_value' => $lockValue->value,
                ]);
            }
            else
            $lockValue->logs()->create([
                'old_value' => null,
                'new_value' => $lockValue->value,
            ]);  
        });
    }


    public function option()
    {
        return $this->belongsTo(LockOption::class, 'option_id', 'id');
    }

    public function lock()
    {
        return $this->belongsTo(Lock::class, 'lock_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany(LockValuesLog::class, 'lock_value_id', 'id');
    }
}
