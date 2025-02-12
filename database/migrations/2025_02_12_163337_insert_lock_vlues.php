<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\LockData\LockOption;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        LockOption::create(['option_name'=>'electricQuantity']);
        LockOption::create(['option_name'=>'lockAlias']);
        LockOption::create(['option_name'=>'noKeyPwd']);
        LockOption::create(['option_name'=>'timezoneRawOffset']);
        LockOption::create(['option_name'=>'error']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
