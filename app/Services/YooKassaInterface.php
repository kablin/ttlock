<?php

namespace App\Services;

use App\Models\User;

interface YooKassaInterface
{
    public function setUser(User $user);
}
