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

        $locks = auth()->user()->locks()->with('rents')->orderBy('id');
        if ($request->has('lock_search')) {
            $rents = $locks->where('name', 'ilike', '%' . $request->lock_search . '%');
        }
        $locks =  $locks->get();
        // $rents = $rents->paginate(12, ['*'], 'rent_page', $rent_page);

        $params = [
            'locks' => $locks,
            'success' => session('success')
        ];

        return Inertia::render('Dashboard', $params);
    }



}
