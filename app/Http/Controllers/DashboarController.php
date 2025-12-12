<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Rent;
use App\Models\Group;
use App\Models\Lock;
use Illuminate\Support\Facades\Validator;


class DashboarController extends Controller
{
    public function index(Request $request)
    {
        $rents = auth()->user()->rents()->with('locks')->orderBy('id')->get();
        $params = [
            'rents' => $rents,
            'success' => session('success')
        ];

        return Inertia::render('Dashboard', $params);
    }



}
