<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


class RentController extends Controller
{
    public function index(Request $request)
    {
        $rents = auth()->user()->rents;
        return Inertia::render('RentsObjects', ['rents' => $rents,]);
    }
}
