<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CodePacket;
use App\Models\LocksCredential;
use App\Models\Tarif;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use denis660\Centrifugo\Centrifugo;


class TarifsController extends Controller
{
    public function index(Request $request)
    {
        $code_packet = auth()->user()->code_packet()->get();

        $tarifs = Tarif::where('active', true)->get();
        //dd(  $tarifs);
        return  Inertia::render('Tarifs', ['phone' =>   auth()->user()->phone, 'code_packet' => $code_packet, 'tarifes' => $tarifs]);
    }
}
