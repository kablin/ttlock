<?php

namespace App\Models;

use App\Models\Apartments\Apartment;
use App\Models\Booking\Booking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LockPinCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

	protected $casts = [
		'start' => 'datetime:d.m.Y H:i:s',
		'end' => 'datetime:d.m.Y H:i:s',
		'start_local' => 'datetime:d.m.Y H:i:s',
		'end_local' => 'datetime:d.m.Y H:i:s',
	];



    public function lock()
    {
        return $this->belongsTo(Lock::class);
    }
}
