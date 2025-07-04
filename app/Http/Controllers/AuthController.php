<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CodePacket;
use App\Models\LocksCredential;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;



class AuthController extends Controller
{
    public function CreateUser(Request $request)
    {
        try {

            $validated = $request->validate([

                'email' => 'required|string|email|max:255',
                'password' => 'required|string',
            ]);



            if (!testCredential($validated['email'], $validated['password'])) {

                return response()->json([
                    'status' => false,
                    'error' => 1,
                    'message' => 'Error login to TTLock',
                ], 200);
            }

            $user = User::firstOrCreate([
                'email' => $validated['email']
            ], [
                'name' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);



            $credentials['email'] = $validated['email'];
            $credentials['password'] = $validated['password'];


            $credential = LocksCredential::updateOrCreate(['user_id' => $user->id], ['login' => $validated['email'], 'password' => $validated['password']]);
            if (updateRefreshToken($credential)) {

                if ($user->wasRecentlyCreated) {
                    $code_packet = CodePacket::firstOrCreate(['user_id' => $user->id]);
                    $code_packet->refresh();
                    $code_packet->count = 10;

                    $code_packet->end = $code_packet->created_at->addYear();
                    $code_packet->save();
                }
                return response()->json([
                    'status' => true,
                    'error' => 0,
                    'message' => 'User created successfully',
                ], 200);
            } else

                return response()->json([
                    'status' => false,
                    'error' => 2,
                    'message' => 'Error to set TTLock credential',
                ], 200);



            /* if (!Auth::attempt($credentials))
            {
                return response()->json(['status' => false,'message' => 'Incorrect email or password',],200);

            }*/
        } catch (\Exception $e) {
            return  response()->json(['status' => false, 'error' => 3, 'message' => 'something wrong',], 200);
        }
    }
}
