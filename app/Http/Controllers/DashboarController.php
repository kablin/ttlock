<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Rent;
use App\Models\Group;
use App\Models\Lock;
use Illuminate\Support\Facades\Validator;
use denis660\Centrifugo\Centrifugo;


class DashboarController extends Controller
{
    public function index(Request $request)
    {
        $rents = auth()->user()->rents()->with('locks')->orderBy('id')->get();

        $centrifugo =  resolve(Centrifugo::class);
        $token = $centrifugo->generateConnectionToken((string)Auth::id(), 0, [
            'name' => Auth::user()->name,
        ], [
            'api:open_lock-' . (string)Auth::id(),
            'api:get_codes_list-' . (string)Auth::id(),
            'api:delete_code_from_lock-' . (string)Auth::id(),
            'api:add_code_to_lock-' . (string)Auth::id(),
            'api:change_code-' . (string)Auth::id(),

        ]);

        $params = [
            'rents' => $rents,
            'locks_count' =>  auth()->user()->locks->count(),
            'token' => $token,
            'success' => session('success')
        ];

        return Inertia::render('Dashboard', $params);
    }
}
