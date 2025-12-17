<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LockEvent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use denis660\Centrifugo\Centrifugo;



class LockEventController extends Controller
{

    public function index(Request $request)
    {
        return Inertia::render('Logs');
    }


    public function lock_page(Request $request, $lock_id)
    {

        $validator = Validator::make($request->all(), [
            'page' => 'required',
        ]);

        $validated = $validator->safe()->only(['page']);
        $log_events = auth()->user()->locks->find($lock_id)->events()->orderBy('id', 'desc')->paginate(10, ['*'], 'page', $validated['page']);
        return response()->json(['log_events' => $log_events]);
    }


    public function page(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'required',
        ]);

        $validated = $validator->safe()->only(['page']);

        $log_events =  LockEvent::whereHas('lock', function ($query) {
            $query->where('user_id', auth()->id());
        })->paginate(10, ['*'], 'page', $validated['page']);

        return response()->json(['log_events' => $log_events]);
    }
}
