<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CodePacket;
use App\Models\LocksCredential;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use denis660\Centrifugo\Centrifugo;



class LockPinCodeController extends Controller
{
    public function pincodesList(Request $request, $lock_id)
    {
        $pincodes = auth()->user()->locks->find($lock_id)->pincodes;

        return response()->json(['pincodes' => $pincodes]);
    }
}
