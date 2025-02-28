<?php

namespace App\Traits;

use App\Models\LockData\LockOption;

trait OptionValueTrait
{
    public  function saveOptionValueByName( $option_name,$value) 
    {

        if ($lock_option = LockOption::where('option_name',$option_name)->first())
        {
            $v = $this->values()->where('option_id', $lock_option['id'])->first();
            if (!$v ||$v['value'] != $value)

            $this->values()->updateOrCreate([
                'option_id' => $lock_option['id'],
            ], [
                'value' => $value,
            ]);
            return true;
        }
        return false;

    }
}