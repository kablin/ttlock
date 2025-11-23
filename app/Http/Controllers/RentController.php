<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Rent;


class RentController extends Controller
{
    public function index(Request $request)
    {
        $rents = auth()->user()->parent_rents()->with('children')
        ->get();

      //  dd($rents);
        return Inertia::render('RentsObjects', ['rents' => $rents,]);
    }
}
