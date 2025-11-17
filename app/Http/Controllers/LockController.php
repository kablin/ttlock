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



class LockController extends Controller
{
    public function lockList(Request $request)
    {
        $locks = auth()->user()->locks;

        $centrifugo =  resolve(Centrifugo::class);  
         $token = $centrifugo->generateConnectionToken((string)Auth::id(), 0, [
      //   $token = $centrifugo->generateConnectionToken('10', 0, [
            'name' => Auth::user()->name,
           
        ], ['news']);


        /*   $token = $centrifugo->generatePrivateChannelToken((string)Auth::id(), 'user', time() + 5 * 60, [
            'name' => Auth::user()->name,
        ]);*/


        info($token);

        return Inertia::render('Locks', ['locks' => $locks  ,  'token' => $token]);
    }
}
